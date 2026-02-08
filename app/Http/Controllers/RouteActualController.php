<?php

namespace App\Http\Controllers;

use App\Models\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

        try {
            $route->update([
                'status' => 'in_progress',
                'actual_start_time' => now()
            ]);

            return redirect()->route('routes.actual.complete', $route)
                ->with('success', 'Route started! Now complete the details.');
        } catch (\Exception $e) {
            Log::error('Error starting route: ' . $e->getMessage());
            return back()->with('error', 'Error starting route: ' . $e->getMessage());
        }
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
     * Store route completion data - FIXED VERSION
     */
    public function storeCompletion(Request $request, Route $route)
    {
        // Log the incoming request for debugging
        Log::info('Route completion attempt', [
            'route_id' => $route->id,
            'request_data' => $request->all()
        ]);


        // Check route status
        if (!in_array($route->status, ['planned', 'in_progress'])) {
            return back()->with('error', 'Route cannot be completed. Current status: ' . $route->status);
        }

        // Validate input
        $validated = $request->validate([
            'actual_start_km' => 'required|numeric|min:0',
            'actual_end_km' => 'required|numeric|gt:actual_start_km',
            'actual_fuel_rate_per_litre' => 'nullable|numeric|min:0',
            'actual_fuel_cost' => 'nullable|numeric|min:0',
            'actual_meal_cost' => 'nullable|numeric|min:0',
            'actual_accommodation_cost' => 'nullable|numeric|min:0',
            'actual_other_cost' => 'nullable|numeric|min:0',
            'return_sales_qty' => 'nullable|integer|min:0',
            'return_sales_value' => 'nullable|numeric|min:0',
            'completion_notes' => 'nullable|string|max:1000'
        ]);

        try {
            // Start database transaction
            DB::beginTransaction();

            // Calculate actual distance from logbook
            $actualDistance = $validated['actual_end_km'] - $validated['actual_start_km'];

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

            // Prepare update data
            $updateData = [
                'status' => 'completed',
                'actual_start_km' => $validated['actual_start_km'],
                'actual_end_km' => $validated['actual_end_km'],
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
            ];

            // Log the update data
            Log::info('Updating route with data', [
                'route_id' => $route->id,
                'update_data' => $updateData
            ]);

            // Update route
            $updated = $route->update($updateData);

            if (!$updated) {
                throw new \Exception('Failed to update route in database');
            }

            // Verify the update
            $route->refresh();

            if ($route->status !== 'completed') {
                throw new \Exception('Route status not updated to completed');
            }

            // Commit transaction
            DB::commit();

            // Calculate cost percentage for display
            $totalSales = $route->companyAllocations->sum('total_sales_value');
            $costPercentage = $totalSales > 0 ? ($totalActualCost / $totalSales) * 100 : 0;

            // Success message with key metrics
            $message = sprintf(
                'Route completed successfully! Distance: %s km | Cost: Rs. %s | Cost %%: %s%%',
                number_format($actualDistance, 1),
                number_format($totalActualCost, 0),
                number_format($costPercentage, 2)
            );

            Log::info('Route completed successfully', [
                'route_id' => $route->id,
                'status' => $route->status
            ]);

            return redirect()->route('routes.show', $route)->with('success', $message);

        } catch (\Exception $e) {
            // Rollback transaction
            DB::rollBack();

            // Log the error
            Log::error('Error completing route', [
                'route_id' => $route->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Error completing route: ' . $e->getMessage());
        }
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
        // Validate cost items
        $validated = $request->validate([
            'cost_items' => 'required|array|min:1',
            'cost_items.*.type' => 'required|string',
            'cost_items.*.description' => 'required|string|max:255',
            'cost_items.*.amount' => 'required|numeric|min:0',
            'cost_items.*.receipt_number' => 'nullable|string|max:100'
        ]);

        try {
            DB::beginTransaction();

            // Store cost items (you'll need to create RouteCostItem model)
            // For now, just store as JSON in completion_notes
            $route->update([
                'completion_notes' => json_encode($validated['cost_items'])
            ]);

            DB::commit();

            return redirect()->route('routes.actual.complete', $route)
                ->with('success', 'Cost items saved successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing cost items: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error saving costs: ' . $e->getMessage());
        }
    }
}
