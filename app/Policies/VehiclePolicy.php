<?php

namespace App\Policies;

use App\Models\Vehicle;
use App\Models\User;

class VehiclePolicy
{
    /**
     * Determine if the user can view any vehicles
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view vehicles');
    }

    /**
     * Determine if the user can view the vehicle
     */
    public function view(User $user, Vehicle $vehicle): bool
    {
        return $user->can('view vehicles');
    }

    /**
     * Determine if the user can create vehicles
     */
    public function create(User $user): bool
    {
        return $user->can('create vehicles');
    }

    /**
     * Determine if the user can update the vehicle
     */
    public function update(User $user, Vehicle $vehicle): bool
    {
        return $user->can('edit vehicles');
    }

    /**
     * Determine if the user can delete the vehicle
     */
    public function delete(User $user, Vehicle $vehicle): bool
    {
        return $user->can('delete vehicles');
    }
}
