<?php

namespace App\Policies;

use App\Models\Route;
use App\Models\User;

class RoutePolicy
{
    /**
     * Determine if the user can view any routes.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view routes');
    }

    /**
     * Determine if the user can view the route.
     */
    public function view(User $user, Route $route): bool
    {
        return $user->can('view routes');
    }

    /**
     * Determine if the user can create routes.
     */
    public function create(User $user): bool
    {
        return $user->can('create routes');
    }

    /**
     * Determine if the user can update the route.
     */
    public function update(User $user, Route $route): bool
    {
        return $user->can('edit routes');
    }

    /**
     * Determine if the user can delete the route.
     */
    public function delete(User $user, Route $route): bool
    {
        return $user->can('delete routes');
    }
}
