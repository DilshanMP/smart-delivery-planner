<?php

namespace App\Policies;

use App\Models\CostItem;
use App\Models\User;

class CostItemPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view cost items
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CostItem $costItem): bool
    {
        // Admin and managers can view any cost item
        if ($user->hasAnyRole(['admin', 'manager'])) {
            return true;
        }

        // Others can only view cost items from their company's routes
        return $user->company_id === $costItem->route->company_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Admin, managers, and coordinators can create cost items
        return $user->hasAnyRole(['admin', 'manager', 'coordinator']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CostItem $costItem): bool
    {
        // Admin and managers can update any cost item
        if ($user->hasAnyRole(['admin', 'manager'])) {
            return true;
        }

        // Coordinators can update cost items from their company's routes
        if ($user->hasRole('coordinator')) {
            return $user->company_id === $costItem->route->company_id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CostItem $costItem): bool
    {
        // Admin and managers can delete cost items
        return $user->hasAnyRole(['admin', 'manager']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CostItem $costItem): bool
    {
        // Admin and managers can restore cost items
        return $user->hasAnyRole(['admin', 'manager']);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CostItem $costItem): bool
    {
        // Only admins can force delete cost items
        return $user->hasRole('admin');
    }
}
