<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CompanyController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of companies
     */
    public function index()
    {
        $this->authorize('viewAny', Company::class);

        $companies = Company::withCount('users')
                            ->withCount('warehouses')
                            ->latest()
                            ->get();

        return view('companies.index', compact('companies'));
    }

    /**
     * Show the form for creating a new company
     */
    public function create()
    {
        $this->authorize('create', Company::class);

        return view('companies.create');
    }

    /**
     * Store a newly created company
     */
    public function store(Request $request)
    {
        $this->authorize('create', Company::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:companies,name',
            'code' => 'required|string|max:50|unique:companies,code',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'contact_person' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        // Ensure is_active has a value
        $validated['is_active'] = $request->has('is_active') ? true : false;

        $company = Company::create($validated);

        return redirect()->route('companies.index')
                        ->with('success', 'Company created successfully!');
    }

    /**
     * Display the specified company
     */
    public function show(Company $company)
    {
        $this->authorize('view', $company);

        $company->load(['users', 'warehouses']);

        return view('companies.show', compact('company'));
    }

    /**
     * Show the form for editing the specified company
     */
    public function edit(Company $company)
    {
        $this->authorize('update', $company);

        return view('companies.edit', compact('company'));
    }

    /**
     * Update the specified company
     */
    public function update(Request $request, Company $company)
    {
        $this->authorize('update', $company);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:companies,name,' . $company->id,
            'code' => 'required|string|max:50|unique:companies,code,' . $company->id,
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'contact_person' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        // Ensure is_active has a value
        $validated['is_active'] = $request->has('is_active') ? true : false;

        $company->update($validated);

        return redirect()->route('companies.index')
                        ->with('success', 'Company updated successfully!');
    }

    /**
     * Remove the specified company
     */
    public function destroy(Company $company)
    {
        $this->authorize('delete', $company);

        // Check if company has associated records
        if ($company->users()->count() > 0) {
            return redirect()->route('companies.index')
                            ->with('error', 'Cannot delete company with associated users!');
        }

        if ($company->warehouses()->count() > 0) {
            return redirect()->route('companies.index')
                            ->with('error', 'Cannot delete company with associated warehouses!');
        }

        $company->delete();

        return redirect()->route('companies.index')
                        ->with('success', 'Company deleted successfully!');
    }
}
