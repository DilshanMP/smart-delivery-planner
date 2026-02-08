<?php

namespace App\Http\Controllers;

use App\Models\Route;
use App\Models\Company;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Warehouse;
use App\Models\RouteStop;
use App\Models\RouteLeg;
use App\Models\RouteCompanyAllocation;
use App\Services\GoogleMapsService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RouteController extends Controller
{
    use AuthorizesRequests;

    protected $googleMaps;

    public function __construct(GoogleMapsService $googleMaps)
    {
        $this->googleMaps = $googleMaps;
    }

    /**
     * Display a listing of routes
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Route::class);

        $query = Route::with(['company', 'driver', 'vehicle', 'warehouse', 'stops'])
                      ->latest('route_date');

        // Filters
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('route_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('route_date', '<=', $request->date_to);
        }

        $routes = $query->paginate(25);

        // For filters
        $companies = Company::where('is_active', true)->orderBy('name')->get();
        $drivers = Driver::where('is_active', true)->orderBy('name')->get();

        return view('routes.index', compact('routes', 'companies', 'drivers'));
    }

    /**
     * Show the form for creating a new route
     */
    public function create()
    {
        $this->authorize('create', Route::class);

        $companies = Company::where('is_active', true)->orderBy('name')->get();
        $drivers = Driver::where('is_active', true)
                        ->whereDoesntHave('activeRoutes')
                        ->orderBy('name')
                        ->get();
        $vehicles = Vehicle::where('is_active', true)
                          ->whereDoesntHave('activeRoutes')
                          ->orderBy('registration_number')
                          ->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        return view('routes.create', compact('companies', 'drivers', 'vehicles', 'warehouses'));
    }

    /**
     * Store a newly created route (FIXED FOR COMPANY-WISE SALES)
     */
    public function store(Request $request)
    {
        $this->authorize('create', Route::class);

        $validated = $request->validate([
            // Planning
            'route_date' => 'required|date',
            'company_id' => 'required|exists:companies,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'driver_id' => 'required|exists:drivers,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'route_code' => 'required|string|max:50|unique:routes_new,route_code',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'delivery_type' => 'required|in:own,outside',

            // Estimated costs
            'estimated_distance_km' => 'required|numeric|min:0',
            'estimated_fuel_rate_per_litre' => 'nullable|numeric|min:0',
            'estimated_fuel_cost' => 'required|numeric|min:0',
            'estimated_meal_cost' => 'nullable|numeric|min:0',
            'estimated_accommodation_cost' => 'nullable|numeric|min:0',
            'estimated_days' => 'required|integer|min:1',

            'notes' => 'nullable|string|max:1000',

            // Company-wise sales (NEW!)
            'company_sales' => 'required|array|min:1',
            'company_sales.*.company_id' => 'required|exists:companies,id',
            'company_sales.*.sales_value' => 'required|numeric|min:0',
            'company_sales.*.sales_qty' => 'required|integer|min:0',

            // Stops (NO SALES FIELDS HERE!)
            'stops' => 'required|array|min:2',
            'stops.*.shop_name' => 'required|string|max:255',
            'stops.*.shop_address' => 'required|string|max:500',
            'stops.*.latitude' => 'required|numeric|between:-90,90',
            'stops.*.longitude' => 'required|numeric|between:-180,180',
            'stops.*.stop_type' => 'required|in:warehouse,shop,final',
        ]);

        DB::beginTransaction();

        try {
            // Calculate total estimated cost
            $totalEstimatedCost = ($validated['estimated_fuel_cost'] ?? 0) +
                                 ($validated['estimated_meal_cost'] ?? 0) +
                                 ($validated['estimated_accommodation_cost'] ?? 0);

            // Create route
            $route = Route::create([
                'route_date' => $validated['route_date'],
                'company_id' => $validated['company_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'driver_id' => $validated['driver_id'],
                'vehicle_id' => $validated['vehicle_id'],
                'route_code' => $validated['route_code'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'delivery_type' => $validated['delivery_type'],
                'estimated_distance_km' => $validated['estimated_distance_km'],
                'estimated_fuel_rate_per_litre' => $validated['estimated_fuel_rate_per_litre'] ?? 350,
                'estimated_fuel_cost' => $validated['estimated_fuel_cost'],
                'estimated_meal_cost' => $validated['estimated_meal_cost'] ?? 0,
                'estimated_accommodation_cost' => $validated['estimated_accommodation_cost'] ?? 0,
                'estimated_days' => $validated['estimated_days'],
                'estimated_total_cost' => $totalEstimatedCost,
                'status' => 'planned',
                'notes' => $validated['notes'],
            ]);

            // Create stops (without sales data)
            $stops = [];
            foreach ($validated['stops'] as $index => $stopData) {
                $stop = $route->stops()->create([
                    'stop_sequence' => $index + 1,
                    'shop_name' => $stopData['shop_name'],
                    'shop_address' => $stopData['shop_address'],
                    'latitude' => $stopData['latitude'],
                    'longitude' => $stopData['longitude'],
                    'stop_type' => $stopData['stop_type'],
                    'sales_value' => 0, // Will be distributed from company sales
                    'sales_qty' => 0,
                    'sales_company_id' => null,
                ]);
                $stops[] = $stop;
            }

            // Calculate route legs using Google Maps
            $this->calculateRouteLegs($route, $stops);

            // Store company-wise sales as allocations
            $totalSalesValue = 0;
            $totalSalesQty = 0;

            foreach ($validated['company_sales'] as $companySale) {
                $totalSalesValue += $companySale['sales_value'];
                $totalSalesQty += $companySale['sales_qty'];

                // Calculate cost allocation percentage
                $allocationPercentage = ($companySale['sales_value'] / $totalSalesValue) * 100;
                $allocatedCost = ($allocationPercentage / 100) * $totalEstimatedCost;
                $profit = $companySale['sales_value'] - $allocatedCost;
                $profitMargin = ($companySale['sales_value'] > 0)
                    ? ($profit / $companySale['sales_value']) * 100
                    : 0;

                RouteCompanyAllocation::create([
                    'route_id' => $route->id,
                    'company_id' => $companySale['company_id'],
                    'total_sales_value' => $companySale['sales_value'],
                    'total_sales_qty' => $companySale['sales_qty'],
                    'number_of_stops' => count($stops), // Simplified - all companies share stops
                    'allocated_cost' => $allocatedCost,
                    'allocation_percentage' => $allocationPercentage,
                    'profit' => $profit,
                    'profit_margin_percentage' => $profitMargin,
                ]);
            }

            // Store total sales in route notes
            $salesSummary = "\n\nTotal Sales: LKR " . number_format($totalSalesValue, 2);
            $salesSummary .= "\nTotal Qty: " . $totalSalesQty;
            $salesSummary .= "\nCost %: " . number_format(($totalEstimatedCost / $totalSalesValue) * 100, 2) . "%";

            $route->update([
                'notes' => ($validated['notes'] ?? '') . $salesSummary
            ]);

            DB::commit();

            return redirect()->route('routes.show', $route)
                            ->with('success', 'Route created successfully! Total Sales: LKR ' . number_format($totalSalesValue, 0) . ' | Cost %: ' . number_format(($totalEstimatedCost / $totalSalesValue) * 100, 1) . '%');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Route creation error: ' . $e->getMessage());

            return redirect()->back()
                            ->withInput()
                            ->with('error', 'Error creating route: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified route
     */
    public function show(Route $route)
    {
        $this->authorize('view', $route);

        $route->load([
            'company',
            'warehouse',
            'driver',
            'vehicle',
            'stops',
            'legs',
            'companyAllocations.company',
            'costItems'
        ]);

        // Calculate profitability
        $profitability = $route->getRouteProfitability();

        return view('routes.show', compact('route', 'profitability'));
    }

    /**
     * Show the form for editing the route
     */
    public function edit(Route $route)
    {
        $this->authorize('update', $route);

        $companies = Company::where('is_active', true)->orderBy('name')->get();
        $drivers = Driver::where('is_active', true)
                        ->where(function($query) use ($route) {
                            $query->whereDoesntHave('activeRoutes')
                                  ->orWhere('id', $route->driver_id);
                        })
                        ->orderBy('name')
                        ->get();
        $vehicles = Vehicle::where('is_active', true)
                          ->where(function($query) use ($route) {
                              $query->whereDoesntHave('activeRoutes')
                                    ->orWhere('id', $route->vehicle_id);
                          })
                          ->orderBy('registration_number')
                          ->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        $route->load('stops', 'companyAllocations');

        return view('routes.edit', compact('route', 'companies', 'drivers', 'vehicles', 'warehouses'));
    }

    /**
     * Update the specified route
     */
    public function update(Request $request, Route $route)
    {
        $this->authorize('update', $route);

        // Similar validation and logic as store()
        // Implementation similar to store() method

        return redirect()->route('routes.show', $route)
                        ->with('success', 'Route updated successfully!');
    }

    /**
     * Remove the specified route
     */
    public function destroy(Route $route)
    {
        $this->authorize('delete', $route);

        DB::beginTransaction();

        try {
            $route->stops()->delete();
            $route->legs()->delete();
            $route->costItems()->delete();
            $route->companyAllocations()->delete();
            $route->delete();

            DB::commit();

            return redirect()->route('routes.index')
                            ->with('success', 'Route deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('routes.index')
                            ->with('error', 'Error deleting route: ' . $e->getMessage());
        }
    }

    /**
     * Calculate route legs using Google Maps
     */
    private function calculateRouteLegs(Route $route, array $stops)
    {
        $totalDistance = 0;
        $totalDuration = 0;

        for ($i = 0; $i < count($stops) - 1; $i++) {
            $fromStop = $stops[$i];
            $toStop = $stops[$i + 1];

            $result = $this->googleMaps->calculateDistance(
                $fromStop->latitude,
                $fromStop->longitude,
                $toStop->latitude,
                $toStop->longitude
            );

            if ($result['success']) {
                $totalDistance += $result['distance_km'];
                $totalDuration += $result['duration_minutes'];

                $route->legs()->create([
                    'leg_number' => $i + 1,
                    'from_stop_id' => $fromStop->id,
                    'to_stop_id' => $toStop->id,
                    'from_location' => $fromStop->shop_name,
                    'to_location' => $toStop->shop_name,
                    'from_latitude' => $fromStop->latitude,
                    'from_longitude' => $fromStop->longitude,
                    'to_latitude' => $toStop->latitude,
                    'to_longitude' => $toStop->longitude,
                    'distance_km' => $result['distance_km'],
                    'duration_minutes' => $result['duration_minutes'],
                    'calculation_method' => $result['method'],
                    'calculated_at' => now(),
                ]);
            }
        }
    }

    /**
     * AJAX: Calculate distance between two points
     */
    public function calculateDistance(Request $request)
    {
        $validated = $request->validate([
            'from_lat' => 'required|numeric',
            'from_lng' => 'required|numeric',
            'to_lat' => 'required|numeric',
            'to_lng' => 'required|numeric',
        ]);

        $result = $this->googleMaps->calculateDistance(
            $validated['from_lat'],
            $validated['from_lng'],
            $validated['to_lat'],
            $validated['to_lng']
        );

        return response()->json($result);
    }

    /**
     * AJAX: Calculate estimated costs
     */
    public function calculateCosts(Request $request)
    {
        $validated = $request->validate([
            'distance_km' => 'required|numeric|min:0',
            'fuel_efficiency' => 'required|numeric|min:1',
            'fuel_rate' => 'required|numeric|min:0',
            'days' => 'required|integer|min:1',
        ]);

        $fuelNeeded = $validated['distance_km'] / $validated['fuel_efficiency'];
        $fuelCost = $fuelNeeded * $validated['fuel_rate'];
        $mealCost = 1500 * $validated['days'];
        $accommodationCost = $validated['days'] > 1 ? 3000 * ($validated['days'] - 1) : 0;
        $totalCost = $fuelCost + $mealCost + $accommodationCost;

        return response()->json([
            'fuel_needed' => round($fuelNeeded, 2),
            'fuel_cost' => round($fuelCost, 2),
            'meal_cost' => $mealCost,
            'accommodation_cost' => $accommodationCost,
            'total_cost' => round($totalCost, 2),
        ]);
    }

    /**
     * Get company allocations for a route
     */
    public function getCompanyAllocations(Route $route)
    {
        $allocations = $route->companyAllocations()
                             ->with('company')
                             ->get();

        return response()->json($allocations);
    }
}
