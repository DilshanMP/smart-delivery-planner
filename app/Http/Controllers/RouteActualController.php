<?php

namespace App\Http\Controllers;

use App\Models\Route;
use Illuminate\Http\Request;

class RouteActualController extends Controller
{
    /**
     * Display list of routes that need completion
     */
    public function index()
    {
        $routes = Route::with(['driver', 'vehicle', 'company', 'stops'])
            ->whereIn('status', ['planned', 'in_progress'])
            ->orderBy('route_date', 'desc')
            ->paginate(20);

        return view('routes.actual.index', compact('routes'));
    }

    /**
     * Start a route (change status to in_progress)
     */
    public function start(Route $route)
    {
        if ($route->status !== 'planned') {
            return back()->with('error', 'Route already started or completed!');
        }

        $route->update([
            'status' => 'in_progress',
            'actual_start_time' => now()
        ]);

        return redirect()->route('routes.actual.complete', $route)
            ->with('success', 'Route started! Now complete the details.');
    }

    /**
     * Show complete route form
     */
    public function showComplete(Route $route)
    {
        // Check if route can be completed
        if (!in_array($route->status, ['planned', 'in_progress'])) {
            return redirect()->route('routes.index')
                ->with('error', 'This route is already completed!');
        }

        // Load relationships
        $route->load([
            'stops',
            'companyAllocations.company',
            'legs',
            'driver',
            'vehicle',
            'warehouse'
        ]);

        return view('routes.actual.complete', compact('route'));
    }

    /**
     * Store route completion data
     */
    public function storeCompletion(Request $request, Route $route)
    {
        // Validate input
        $validated = $request->validate([
            'logbook_start_km' => 'required|numeric|min:0',
            'logbook_end_km' => 'required|numeric|gt:logbook_start_km',
            'actual_fuel_rate_per_litre' => 'nullable|numeric|min:0',
            'actual_fuel_cost' => 'nullable|numeric|min:0',
            'actual_meal_cost' => 'nullable|numeric|min:0',
            'actual_accommodation_cost' => 'nullable|numeric|min:0',
            'actual_other_cost' => 'nullable|numeric|min:0',
            'return_sales_qty' => 'nullable|integer|min:0',
            'return_sales_value' => 'nullable|numeric|min:0',
            'completion_notes' => 'nullable|string|max:1000'
        ]);

        // Calculate actual distance from logbook
        $actualDistance = $validated['logbook_end_km'] - $validated['logbook_start_km'];

        // Calculate total actual cost
        $totalActualCost = ($validated['actual_fuel_cost'] ?? 0) +
                          ($validated['actual_meal_cost'] ?? 0) +
                          ($validated['actual_accommodation_cost'] ?? 0) +
                          ($validated['actual_other_cost'] ?? 0);

        // Calculate variance
        $distanceVariance = $actualDistance - $route->estimated_distance_km;
        $costVariance = $totalActualCost - $route->estimated_total_cost;
        $costVariancePercentage = $route->estimated_total_cost > 0
            ? (($costVariance / $route->estimated_total_cost) * 100)
            : 0;

        // Update route with actual data
        $route->update([
            'status' => 'completed',
            'logbook_start_km' => $validated['logbook_start_km'],
            'logbook_end_km' => $validated['logbook_end_km'],
            'actual_distance_km' => $actualDistance,
            'actual_fuel_rate_per_litre' => $validated['actual_fuel_rate_per_litre'] ?? null,
            'actual_fuel_cost' => $validated['actual_fuel_cost'] ?? 0,
            'actual_meal_cost' => $validated['actual_meal_cost'] ?? 0,
            'actual_accommodation_cost' => $validated['actual_accommodation_cost'] ?? 0,
            'actual_other_cost' => $validated['actual_other_cost'] ?? 0,
            'actual_total_cost' => $totalActualCost,
            'return_sales_qty' => $validated['return_sales_qty'] ?? 0,
            'return_sales_value' => $validated['return_sales_value'] ?? 0,
            'distance_variance_km' => $distanceVariance,
            'cost_variance' => $costVariance,
            'cost_variance_percentage' => $costVariancePercentage,
            'completion_notes' => $validated['completion_notes'] ?? null,
            'completed_at' => now()
        ]);

        // Calculate cost percentage for display
        $totalSales = $route->companyAllocations->sum('total_sales_value');
        $costPercentage = $totalSales > 0 ? ($totalActualCost / $totalSales) * 100 : 0;

        // Success message with key metrics
        $message = sprintf(
            'Route completed! Distance: %s km | Cost: Rs. %s | Cost %%: %s%%',
            number_format($actualDistance, 1),
            number_format($totalActualCost, 0),
            number_format($costPercentage, 2)
        );

        return redirect()->route('routes.show', $route)->with('success', $message);
    }

    /**
     * Show detailed costs entry form (Optional - for itemized costs)
     */
    public function showCosts(Route $route)
    {
        if ($route->status !== 'in_progress') {
            return redirect()->route('routes.show', $route)
                ->with('error', 'Route must be in progress to add costs.');
        }

        return view('routes.actual.costs', compact('route'));
    }

    /**
     * Store detailed cost items (Optional - future enhancement)
     */
    public function storeCosts(Request $request, Route $route)
    {
        $validated = $request->validate([
            'cost_items' => 'required|array|min:1',
            'cost_items.*.cost_type' => 'required|in:fuel,meal,accommodation,toll,maintenance,other',
            'cost_items.*.description' => 'nullable|string|max:255',
            'cost_items.*.actual_amount' => 'required|numeric|min:0',
            'cost_items.*.receipt_number' => 'nullable|string|max:100',
            'cost_items.*.expense_date' => 'nullable|date'
        ]);

        // Store each cost item
        // This would require a new RouteCostItem model and table
        // For now, we can aggregate into the route actual costs

        $fuelCost = 0;
        $mealCost = 0;
        $accommodationCost = 0;
        $otherCost = 0;

        foreach ($validated['cost_items'] as $item) {
            switch ($item['cost_type']) {
                case 'fuel':
                    $fuelCost += $item['actual_amount'];
                    break;
                case 'meal':
                    $mealCost += $item['actual_amount'];
                    break;
                case 'accommodation':
                    $accommodationCost += $item['actual_amount'];
                    break;
                default:
                    $otherCost += $item['actual_amount'];
            }
        }

        // Update route with aggregated costs
        $route->update([
            'actual_fuel_cost' => $fuelCost,
            'actual_meal_cost' => $mealCost,
            'actual_accommodation_cost' => $accommodationCost,
            'actual_other_cost' => $otherCost,
            'actual_total_cost' => $fuelCost + $mealCost + $accommodationCost + $otherCost
        ]);

        return redirect()->route('routes.show', $route)
            ->with('success', 'Detailed costs added successfully!');
    }
}
