<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class WarehouseController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of warehouses
     */
    public function index()
    {
        $this->authorize('viewAny', Warehouse::class);

        $warehouses = Warehouse::with('company')
                               ->latest()
                               ->get();

        return view('warehouses.index', compact('warehouses'));
    }

    /**
     * Show the form for creating a new warehouse
     */
    public function create()
    {
        $this->authorize('create', Warehouse::class);

        $companies = Company::where('is_active', true)->orderBy('name')->get();

        return view('warehouses.create', compact('companies'));
    }

    /**
     * Store a newly created warehouse
     */
    public function store(Request $request)
    {
        $this->authorize('create', Warehouse::class);

        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:warehouses,code',
            'address' => 'required|string|max:500',
            'city' => 'nullable|string|max:100',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'capacity' => 'nullable|numeric|min:0',
            'current_stock' => 'nullable|numeric|min:0',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        // Ensure is_active has a value
        $validated['is_active'] = $request->has('is_active') ? true : false;

        // Ensure current_stock doesn't exceed capacity
        if (isset($validated['capacity']) && isset($validated['current_stock'])) {
            if ($validated['current_stock'] > $validated['capacity']) {
                return back()->withInput()->withErrors([
                    'current_stock' => 'Current stock cannot exceed warehouse capacity!'
                ]);
            }
        }

        $warehouse = Warehouse::create($validated);

        return redirect()->route('warehouses.index')
                        ->with('success', 'Warehouse created successfully!');
    }

    /**
     * Display the specified warehouse
     */
    public function show(Warehouse $warehouse)
    {
        $this->authorize('view', $warehouse);

        $warehouse->load('company');

        return view('warehouses.show', compact('warehouse'));
    }

    /**
     * Show the form for editing the specified warehouse
     */
    public function edit(Warehouse $warehouse)
    {
        $this->authorize('update', $warehouse);

        $companies = Company::where('is_active', true)->orderBy('name')->get();

        return view('warehouses.edit', compact('warehouse', 'companies'));
    }

    /**
     * Update the specified warehouse
     */
    public function update(Request $request, Warehouse $warehouse)
    {
        $this->authorize('update', $warehouse);

        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:warehouses,code,' . $warehouse->id,
            'address' => 'required|string|max:500',
            'city' => 'nullable|string|max:100',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'capacity' => 'nullable|numeric|min:0',
            'current_stock' => 'nullable|numeric|min:0',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        // Ensure is_active has a value
        $validated['is_active'] = $request->has('is_active') ? true : false;

        // Ensure current_stock doesn't exceed capacity
        if (isset($validated['capacity']) && isset($validated['current_stock'])) {
            if ($validated['current_stock'] > $validated['capacity']) {
                return back()->withInput()->withErrors([
                    'current_stock' => 'Current stock cannot exceed warehouse capacity!'
                ]);
            }
        }

        $warehouse->update($validated);

        return redirect()->route('warehouses.index')
                        ->with('success', 'Warehouse updated successfully!');
    }

    /**
     * Remove the specified warehouse
     */
    public function destroy(Warehouse $warehouse)
    {
        $this->authorize('delete', $warehouse);

        // Check if warehouse has associated routes (when routes are implemented)
        // if ($warehouse->routes()->count() > 0) {
        //     return redirect()->route('warehouses.index')
        //                     ->with('error', 'Cannot delete warehouse with associated routes!');
        // }

        $warehouse->delete();

        return redirect()->route('warehouses.index')
                        ->with('success', 'Warehouse deleted successfully!');
    }
}
