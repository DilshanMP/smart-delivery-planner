<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class VehicleController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of vehicles
     */
    public function index()
    {
        $this->authorize('viewAny', Vehicle::class);

        $vehicles = Vehicle::with('company')
                           ->latest()
                           ->get();

        return view('vehicles.index', compact('vehicles'));
    }

    /**
     * Show the form for creating a new vehicle
     */
    public function create()
    {
        $this->authorize('create', Vehicle::class);

        $companies = Company::where('is_active', true)->orderBy('name')->get();

        return view('vehicles.create', compact('companies'));
    }

    /**
     * Store a newly created vehicle
     */
    public function store(Request $request)
    {
        $this->authorize('create', Vehicle::class);

        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'registration_number' => 'required|string|max:50|unique:vehicles,registration_number',
            'vehicle_type' => 'required|in:lorry,truck,van,mini_truck,pickup,other',
            'make' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'capacity_weight' => 'nullable|numeric|min:0',
            'capacity_volume' => 'nullable|numeric|min:0',
            'fuel_type' => 'required|in:diesel,petrol,electric,hybrid',
            'fuel_efficiency' => 'nullable|numeric|min:0',
            'condition' => 'required|in:excellent,good,fair,poor',
            'last_service_date' => 'nullable|date',
            'next_service_date' => 'nullable|date|after:last_service_date',
            'insurance_expiry' => 'nullable|date',
            'license_expiry' => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        // Ensure is_active has a value
        $validated['is_active'] = $request->has('is_active') ? true : false;

        $vehicle = Vehicle::create($validated);

        return redirect()->route('vehicles.index')
                        ->with('success', 'Vehicle created successfully!');
    }

    /**
     * Display the specified vehicle
     */
    public function show(Vehicle $vehicle)
    {
        $this->authorize('view', $vehicle);

        $vehicle->load('company');

        return view('vehicles.show', compact('vehicle'));
    }

    /**
     * Show the form for editing the specified vehicle
     */
    public function edit(Vehicle $vehicle)
    {
        $this->authorize('update', $vehicle);

        $companies = Company::where('is_active', true)->orderBy('name')->get();

        return view('vehicles.edit', compact('vehicle', 'companies'));
    }

    /**
     * Update the specified vehicle
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        $this->authorize('update', $vehicle);

        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'registration_number' => 'required|string|max:50|unique:vehicles,registration_number,' . $vehicle->id,
            'vehicle_type' => 'required|in:lorry,truck,van,mini_truck,pickup,other',
            'make' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'capacity_weight' => 'nullable|numeric|min:0',
            'capacity_volume' => 'nullable|numeric|min:0',
            'fuel_type' => 'required|in:diesel,petrol,electric,hybrid',
            'fuel_efficiency' => 'nullable|numeric|min:0',
            'condition' => 'required|in:excellent,good,fair,poor',
            'last_service_date' => 'nullable|date',
            'next_service_date' => 'nullable|date|after:last_service_date',
            'insurance_expiry' => 'nullable|date',
            'license_expiry' => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        // Ensure is_active has a value
        $validated['is_active'] = $request->has('is_active') ? true : false;

        $vehicle->update($validated);

        return redirect()->route('vehicles.index')
                        ->with('success', 'Vehicle updated successfully!');
    }

    /**
     * Remove the specified vehicle
     */
    public function destroy(Vehicle $vehicle)
    {
        $this->authorize('delete', $vehicle);

        // Check if vehicle has associated routes (when routes are implemented)
        // if ($vehicle->routes()->count() > 0) {
        //     return redirect()->route('vehicles.index')
        //                     ->with('error', 'Cannot delete vehicle with associated routes!');
        // }

        $vehicle->delete();

        return redirect()->route('vehicles.index')
                        ->with('success', 'Vehicle deleted successfully!');
    }
}
