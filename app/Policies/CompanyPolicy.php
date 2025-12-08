<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;

class CompanyPolicy
{
    /**
     * Determine if the user can view any companies
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view companies');
    }

    /**
     * Determine if the user can view the company
     */
    public function view(User $user, Company $company): bool
    {
        return $user->can('view companies');
    }

    /**
     * Determine if the user can create companies
     */
    public function create(User $user): bool
    {
        return $user->can('create companies');
    }

    /**
     * Determine if the user can update the company
     */
    public function update(User $user, Company $company): bool
    {
        return $user->can('edit companies');
    }

    /**
     * Determine if the user can delete the company
     */
    public function delete(User $user, Company $company): bool
    {
        return $user->can('delete companies');
    }
}


