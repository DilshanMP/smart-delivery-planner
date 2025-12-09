<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index()
    {
        // Eager load relationships used in the table
        $users = User::with(['company', 'roles'])->paginate(25);

        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $companies = Company::orderBy('name')->get();
        $roles = Role::orderBy('name')->get();

        return view('users.create', compact('companies', 'roles'));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'company_id' => 'nullable|exists:companies,id',
            'role' => 'nullable|string|exists:roles,name',
            'password' => 'required|string|min:6|confirmed',
            'phone_number' => 'nullable|string|max:50',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'company_id' => $validated['company_id'] ?? null,
            'phone_number' => $validated['phone_number'] ?? null,
            'password' => Hash::make($validated['password']),
            'is_active' => true,
        ]);

        // assign role if provided
        if (!empty($validated['role'])) {
            $user->syncRoles([$validated['role']]);
        }

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        // fetch companies & roles for selects
        $companies = Company::orderBy('name')->get();
        $roles = Role::orderBy('name')->get();

        return view('users.edit', compact('user', 'companies', 'roles'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'company_id' => 'nullable|exists:companies,id',
            'role' => 'nullable|string|exists:roles,name',
            'phone_number' => 'nullable|string|max:50',
            'password' => 'nullable|string|min:6|confirmed',
            'is_active' => 'nullable|in:1',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->company_id = $validated['company_id'] ?? null;
        $user->phone_number = $validated['phone_number'] ?? null;
        // checkbox: if present set true, otherwise false
        $user->is_active = isset($validated['is_active']) ? (bool)$validated['is_active'] : false;

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        // sync role (if supplied). If you allow single role:
        if (isset($validated['role']) && $validated['role'] !== '') {
            $user->syncRoles([$validated['role']]);
        } else {
            // remove roles if none selected
            $user->syncRoles([]);
        }

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        // Protect deletion of current user
        if (auth()->check() && auth()->id() === $user->id) {
            return redirect()->route('users.index')->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
