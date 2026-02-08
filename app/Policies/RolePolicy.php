<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    /**
     * Determine if the user can view any roles
     */
    public function viewAny(User $user): bool
    {
        // Check permission - case insensitive
        return $user->hasPermissionTo('view roles')
            || $user->hasRole('Admin')
            || $user->hasRole('admin');
    }

    /**
     * Determine if the user can view the role
     */
    public function view(User $user, Role $role): bool
    {
        return $user->hasPermissionTo('view roles')
            || $user->hasRole('Admin')
            || $user->hasRole('admin');
    }

    /**
     * Determine if the user can create roles
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create roles')
            || $user->hasRole('Admin')
            || $user->hasRole('admin');
    }

    /**
     * Determine if the user can update the role
     */
    public function update(User $user, Role $role): bool
    {
        return $user->hasPermissionTo('edit roles')
            || $user->hasRole('Admin')
            || $user->hasRole('admin');
    }

    /**
     * Determine if the user can delete the role
     */
    public function delete(User $user, Role $role): bool
    {
        return $user->hasPermissionTo('delete roles')
            || $user->hasRole('Admin')
            || $user->hasRole('admin');
    }
}
