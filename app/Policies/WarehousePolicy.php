<?php

namespace App\Policies;

use App\Models\Warehouse;
use App\Models\User;

class WarehousePolicy
{
    /**
     * Determine if the user can view any warehouses
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view warehouses');
    }

    /**
     * Determine if the user can view the warehouse
     */
    public function view(User $user, Warehouse $warehouse): bool
    {
        return $user->can('view warehouses');
    }

    /**
     * Determine if the user can create warehouses
     */
    public function create(User $user): bool
    {
        return $user->can('create warehouses');
    }

    /**
     * Determine if the user can update the warehouse
     */
    public function update(User $user, Warehouse $warehouse): bool
    {
        return $user->can('edit warehouses');
    }

    /**
     * Determine if the user can delete the warehouse
     */
    public function delete(User $user, Warehouse $warehouse): bool
    {
        return $user->can('delete warehouses');
    }
}
