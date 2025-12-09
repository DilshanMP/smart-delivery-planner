<?php

namespace App\Http\Controllers;

use App\Models\Route;
use App\Models\Company;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Warehouse;
use App\Models\RouteStop;
use App\Models\RouteLeg;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;

class RouteController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', Route::class);

        $routes = Route::with(['company', 'driver', 'vehicle', 'stops'])
                       ->latest()
                       ->get();

        return view('routes.index', compact('routes'));
    }

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

    public function store(Request $request)
    {
        $this->authorize('create', Route::class);

        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'driver_id' => 'required|exists:drivers,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'route_code' => 'required|string|max:50|unique:routes,route_code',
            'delivery_date' => 'required|date',
            'delivery_type' => 'required|in:own,outside',
            'status' => 'required|in:planned,in_progress,completed,cancelled',
            'notes' => 'nullable|string|max:1000',
            'stops' => 'required|array|min:2',
            'stops.*.customer_name' => 'required|string|max:255',
            'stops.*.delivery_address' => 'required|string|max:500',
            'stops.*.latitude' => 'required|numeric|between:-90,90',
            'stops.*.longitude' => 'required|numeric|between:-180,180',
            'stops.*.sale_value' => 'nullable|numeric|min:0',
            'stops.*.special_instructions' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();

        try {
            // Create route
            $route = Route::create([
                'company_id' => $validated['company_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'driver_id' => $validated['driver_id'],
                'vehicle_id' => $validated['vehicle_id'],
                'route_code' => $validated['route_code'],
                'delivery_date' => $validated['delivery_date'],
                'delivery_type' => $validated['delivery_type'],
                'status' => $validated['status'],
                'notes' => $validated['notes'],
            ]);

            // Create route stops
            foreach ($validated['stops'] as $index => $stopData) {
                $route->stops()->create([
                    'sequence' => $index + 1,
                    'customer_name' => $stopData['customer_name'],
                    'delivery_address' => $stopData['delivery_address'],
                    'latitude' => $stopData['latitude'],
                    'longitude' => $stopData['longitude'],
                    'sale_value' => $stopData['sale_value'] ?? 0,
                    'special_instructions' => $stopData['special_instructions'] ?? null,
                ]);
            }

            // Calculate and create route legs
            $this->calculateRouteLegs($route);

            // Calculate totals
            $this->calculateRouteTotals($route);

            DB::commit();

            return redirect()->route('routes.index')
                            ->with('success', 'Route created successfully with ' . count($validated['stops']) . ' stops!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                            ->withInput()
                            ->with('error', 'Error creating route: ' . $e->getMessage());
        }
    }

    public function show(Route $route)
    {
        $this->authorize('view', $route);

        $route->load(['company', 'driver', 'vehicle', 'warehouse', 'stops', 'legs']);

        return view('routes.show', compact('route'));
    }

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

        $route->load('stops');

        return view('routes.edit', compact('route', 'companies', 'drivers', 'vehicles', 'warehouses'));
    }

    public function update(Request $request, Route $route)
    {
        $this->authorize('update', $route);

        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'driver_id' => 'required|exists:drivers,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'route_code' => 'required|string|max:50|unique:routes,route_code,' . $route->id,
            'delivery_date' => 'required|date',
            'delivery_type' => 'required|in:own,outside',
            'status' => 'required|in:planned,in_progress,completed,cancelled',
            'notes' => 'nullable|string|max:1000',
            'stops' => 'required|array|min:2',
            'stops.*.customer_name' => 'required|string|max:255',
            'stops.*.delivery_address' => 'required|string|max:500',
            'stops.*.latitude' => 'required|numeric|between:-90,90',
            'stops.*.longitude' => 'required|numeric|between:-180,180',
            'stops.*.sale_value' => 'nullable|numeric|min:0',
            'stops.*.special_instructions' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();

        try {
            $route->update([
                'company_id' => $validated['company_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'driver_id' => $validated['driver_id'],
                'vehicle_id' => $validated['vehicle_id'],
                'route_code' => $validated['route_code'],
                'delivery_date' => $validated['delivery_date'],
                'delivery_type' => $validated['delivery_type'],
                'status' => $validated['status'],
                'notes' => $validated['notes'],
            ]);

            // Delete existing stops and legs
            $route->stops()->delete();
            $route->legs()->delete();

            // Recreate stops
            foreach ($validated['stops'] as $index => $stopData) {
                $route->stops()->create([
                    'sequence' => $index + 1,
                    'customer_name' => $stopData['customer_name'],
                    'delivery_address' => $stopData['delivery_address'],
                    'latitude' => $stopData['latitude'],
                    'longitude' => $stopData['longitude'],
                    'sale_value' => $stopData['sale_value'] ?? 0,
                    'special_instructions' => $stopData['special_instructions'] ?? null,
                ]);
            }

            // Recalculate
            $this->calculateRouteLegs($route);
            $this->calculateRouteTotals($route);

            DB::commit();

            return redirect()->route('routes.index')
                            ->with('success', 'Route updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                            ->withInput()
                            ->with('error', 'Error updating route: ' . $e->getMessage());
        }
    }

    public function destroy(Route $route)
    {
        $this->authorize('delete', $route);

        DB::beginTransaction();

        try {
            $route->stops()->delete();
            $route->legs()->delete();
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

    private function calculateRouteLegs(Route $route)
    {
        $stops = $route->stops()->orderBy('sequence')->get();

        for ($i = 0; $i < count($stops) - 1; $i++) {
            $fromStop = $stops[$i];
            $toStop = $stops[$i + 1];

            $distance = $this->calculateDistance(
                $fromStop->latitude,
                $fromStop->longitude,
                $toStop->latitude,
                $toStop->longitude
            );

            $durationMinutes = round(($distance / 40) * 60);

            $route->legs()->create([
                'leg_number' => $i + 1,
                'from_location' => $fromStop->customer_name,
                'to_location' => $toStop->customer_name,
                'from_latitude' => $fromStop->latitude,
                'from_longitude' => $fromStop->longitude,
                'to_latitude' => $toStop->latitude,
                'to_longitude' => $toStop->longitude,
                'distance_km' => round($distance, 2),
                'duration_minutes' => $durationMinutes,
                'calculation_method' => 'haversine',
                'calculated_at' => now(),
            ]);
        }
    }

    private function calculateRouteTotals(Route $route)
    {
        $totalDistance = $route->legs()->sum('distance_km');
        $totalDuration = $route->legs()->sum('duration_minutes');

        $fuelEfficiency = $route->vehicle->fuel_efficiency ?? 10;
        $fuelCostPerLiter = 350;
        $fuelNeeded = $totalDistance / $fuelEfficiency;
        $estimatedFuelCost = $fuelNeeded * $fuelCostPerLiter;
        $otherCosts = $estimatedFuelCost * 0.20;
        $totalEstimatedCost = $estimatedFuelCost + $otherCosts;

        $route->update([
            'total_distance_km' => round($totalDistance, 2),
            'estimated_distance_km' => round($totalDistance, 2),
            'total_duration_minutes' => $totalDuration,
            'estimated_cost' => round($totalEstimatedCost, 2),
        ]);
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }
}
