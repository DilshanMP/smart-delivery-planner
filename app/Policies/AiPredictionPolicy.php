<?php

namespace App\Policies;

use App\Models\AiPrediction;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AiPredictionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view AI predictions
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AiPrediction $aiPrediction): bool
    {
        // Admin and managers can view any prediction
        if ($user->hasAnyRole(['admin', 'manager'])) {
            return true;
        }

        // Others can only view predictions from their company's routes
        return $user->company_id === $aiPrediction->route->company_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Admin, managers, and coordinators can create AI predictions
        return $user->hasAnyRole(['admin', 'manager', 'coordinator']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AiPrediction $aiPrediction): bool
    {
        // Only admins can update AI predictions (for retraining purposes)
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AiPrediction $aiPrediction): bool
    {
        // Only admins can delete AI predictions
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AiPrediction $aiPrediction): bool
    {
        // Only admins can restore AI predictions
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AiPrediction $aiPrediction): bool
    {
        // Only admins can force delete AI predictions
        return $user->hasRole('admin');
    }
}
