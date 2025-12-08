<?php

namespace App\Policies;

use App\Models\Driver;
use App\Models\User;

class DriverPolicy
{
    /**
     * Determine if the user can view any drivers
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view drivers');
    }

    /**
     * Determine if the user can view the driver
     */
    public function view(User $user, Driver $driver): bool
    {
        return $user->can('view drivers');
    }

    /**
     * Determine if the user can create drivers
     */
    public function create(User $user): bool
    {
        return $user->can('create drivers');
    }

    /**
     * Determine if the user can update the driver
     */
    public function update(User $user, Driver $driver): bool
    {
        return $user->can('edit drivers');
    }

    /**
     * Determine if the user can delete the driver
     */
    public function delete(User $user, Driver $driver): bool
    {
        return $user->can('delete drivers');
    }
}
