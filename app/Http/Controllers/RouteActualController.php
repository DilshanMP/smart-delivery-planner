<?php

namespace App\Http\Controllers;

use App\Models\Route;
use App\Models\RouteCostItem;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RouteActualController extends Controller
{
    use AuthorizesRequests;

    /**
     * Show routes pending completion
     */
    public function index()
    {
        $routes = Route::with(['company', 'driver', 'vehicle'])
                       ->whereIn('status', ['planned', 'in_progress'])
                       ->orderBy('route_date', 'desc')
                       ->get();

        return view('routes.actual.index', compact('routes'));
    }

    /**
     * Show form to complete a route (enter logbook data)
     */
    public function complete(Route $route)
    {
        $this->authorize('update', $route);

        $route->load(['stops', 'legs', 'vehicle']);

        return view('routes.actual.complete', compact('route'));
    }

    /**
     * Store actual completion data (logbook entry)
     */
    public function storeCompletion(Request $request, Route $route)
    {
        $this->authorize('update', $route);

        $validated = $request->validate([
            // Logbook data
            'actual_start_km' => 'required|numeric|min:0',
            'actual_end_km' => 'required|numeric|min:0|gt:actual_start_km',

            // Actual costs (optional - defaults to estimated)
            'actual_fuel_cost' => 'nullable|numeric|min:0',
            'actual_meal_cost' => 'nullable|numeric|min:0',
            'actual_accommodation_cost' => 'nullable|numeric|min:0',
            'actual_other_costs' => 'nullable|numeric|min:0',

            // Returns
            'return_sales_value' => 'nullable|numeric|min:0',

            // Completion notes
            'completion_notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();

        try {
            // Record actual completion
            $route->recordActualCompletion($validated);

            // Mark as completed
            $route->markAsCompleted($validated['return_sales_value'] ?? 0);

            // Update notes
            if ($validated['completion_notes']) {
                $route->update([
                    'notes' => ($route->notes ? $route->notes . "\n\n" : '') .
                               "Completion Notes:\n" . $validated['completion_notes']
                ]);
            }

            DB::commit();

            return redirect()->route('routes.show', $route)
                            ->with('success', 'Route completed successfully! Actual data recorded.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                            ->withInput()
                            ->with('error', 'Error completing route: ' . $e->getMessage());
        }
    }

    /**
     * Show form to enter detailed costs
     */
    public function costs(Route $route)
    {
        $this->authorize('update', $route);

        $route->load('costItems');

        $costTypes = [
            'fuel' => 'Fuel',
            'meal' => 'Meal / Refreshment',
            'accommodation' => 'Accommodation',
            'toll' => 'Toll Charges',
            'parking' => 'Parking',
            'maintenance' => 'Maintenance',
            'other' => 'Other Expenses',
        ];

        return view('routes.actual.costs', compact('route', 'costTypes'));
    }

    /**
     * Store detailed cost items
     */
    public function storeCosts(Request $request, Route $route)
    {
        $this->authorize('update', $route);

        $validated = $request->validate([
            'cost_items' => 'required|array|min:1',
            'cost_items.*.cost_type' => 'required|in:fuel,meal,accommodation,toll,parking,maintenance,other',
            'cost_items.*.description' => 'required|string|max:255',
            'cost_items.*.estimated_amount' => 'nullable|numeric|min:0',
            'cost_items.*.actual_amount' => 'required|numeric|min:0',
            'cost_items.*.receipt_number' => 'nullable|string|max:100',
            'cost_items.*.expense_date' => 'required|date',
        ]);

        DB::beginTransaction();

        try {
            foreach ($validated['cost_items'] as $itemData) {
                RouteCostItem::create([
                    'route_id' => $route->id,
                    'cost_type' => $itemData['cost_type'],
                    'description' => $itemData['description'],
                    'estimated_amount' => $itemData['estimated_amount'],
                    'actual_amount' => $itemData['actual_amount'],
                    'receipt_number' => $itemData['receipt_number'],
                    'expense_date' => $itemData['expense_date'],
                ]);
            }

            // Recalculate total actual cost from all cost items
            $totalActualCost = $route->costItems()->sum('actual_amount');
            $route->update(['actual_total_cost' => $totalActualCost]);

            // Recalculate variances
            $route->calculateVariances();

            DB::commit();

            return redirect()->route('routes.show', $route)
                            ->with('success', 'Cost items added successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                            ->withInput()
                            ->with('error', 'Error saving costs: ' . $e->getMessage());
        }
    }

    /**
     * Upload receipt files
     */
    public function uploadReceipts(Request $request, Route $route)
    {
        $this->authorize('update', $route);

        $validated = $request->validate([
            'cost_item_id' => 'required|exists:route_cost_items,id',
            'receipt_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
        ]);

        try {
            $costItem = RouteCostItem::findOrFail($validated['cost_item_id']);

            if ($costItem->route_id != $route->id) {
                abort(403, 'Unauthorized');
            }

            // Store receipt file
            $path = $request->file('receipt_file')->store('receipts/' . $route->id, 'public');

            $costItem->update(['receipt_file_path' => $path]);

            return redirect()->back()
                            ->with('success', 'Receipt uploaded successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                            ->with('error', 'Error uploading receipt: ' . $e->getMessage());
        }
    }

    /**
     * Start a route (mark as in progress)
     */
    public function start(Route $route)
    {
        $this->authorize('update', $route);

        if ($route->status !== 'planned') {
            return redirect()->back()
                            ->with('error', 'Route has already been started or completed.');
        }

        $route->markAsStarted();

        return redirect()->route('routes.show', $route)
                        ->with('success', 'Route started! Status updated to In Progress.');
    }
}
