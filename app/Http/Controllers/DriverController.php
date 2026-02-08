<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DriverController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of drivers
     */
    public function index()
    {
        $this->authorize('viewAny', Driver::class);

        $drivers = Driver::with('company')
                         ->latest()
                         ->get();

        return view('drivers.index', compact('drivers'));
    }

    /**
     * Show the form for creating a new driver
     */
    public function create()
    {
        $this->authorize('create', Driver::class);

        $companies = Company::where('is_active', true)->orderBy('name')->get();

        return view('drivers.create', compact('companies'));
    }

    /**
     * Store a newly created driver
     */
    public function store(Request $request)
    {
        $this->authorize('create', Driver::class);

        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name' => 'required|string|max:255',
            'license_number' => 'required|string|max:50|unique:drivers,license_number',
            'license_type' => 'required|in:car,van,lorry,heavy_vehicle,all',
            'license_expiry' => 'nullable|date|after:today',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'date_of_birth' => 'nullable|date|before:today',
            'experience_years' => 'nullable|integer|min:0|max:50',
            'is_active' => 'boolean',
        ]);

        // Ensure is_active has a value
        $validated['is_active'] = $request->has('is_active') ? true : false;

        $driver = Driver::create($validated);

        return redirect()->route('drivers.index')
                        ->with('success', 'Driver created successfully!');
    }

    /**
     * Display the specified driver
     */
    public function show(Driver $driver)
    {
        $this->authorize('view', $driver);

        $driver->load('company');

        return view('drivers.show', compact('driver'));
    }

    /**
     * Show the form for editing the specified driver
     */
    public function edit(Driver $driver)
    {
        $this->authorize('update', $driver);

        $companies = Company::where('is_active', true)->orderBy('name')->get();

        return view('drivers.edit', compact('driver', 'companies'));
    }

    /**
     * Update the specified driver
     */
    public function update(Request $request, Driver $driver)
    {
        $this->authorize('update', $driver);

        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name' => 'required|string|max:255',
            'license_number' => 'required|string|max:50|unique:drivers,license_number,' . $driver->id,
            'license_type' => 'required|in:car,van,lorry,heavy_vehicle,all',
            'license_expiry' => 'nullable|date|after:today',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'date_of_birth' => 'nullable|date|before:today',
            'experience_years' => 'nullable|integer|min:0|max:50',
            'is_active' => 'boolean',
        ]);

        // Ensure is_active has a value
        $validated['is_active'] = $request->has('is_active') ? true : false;

        $driver->update($validated);

        return redirect()->route('drivers.index')
                        ->with('success', 'Driver updated successfully!');
    }

    /**
     * Remove the specified driver
     */
    public function destroy(Driver $driver)
    {
        $this->authorize('delete', $driver);

        // Check if driver has associated routes (when routes are implemented)
        // if ($driver->routes()->count() > 0) {
        //     return redirect()->route('drivers.index')
        //                     ->with('error', 'Cannot delete driver with associated routes!');
        // }

        $driver->delete();

        return redirect()->route('drivers.index')
                        ->with('success', 'Driver deleted successfully!');
    }
}
