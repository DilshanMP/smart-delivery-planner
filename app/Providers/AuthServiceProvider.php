<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\RolePolicy;
use App\Models\Company;
use App\Models\Warehouse;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\Route;
use App\Models\CostItem;
use App\Models\AiPrediction;

use App\Policies\UserPolicy;
use App\Policies\CompanyPolicy;
use App\Policies\WarehousePolicy;
use App\Policies\VehiclePolicy;
use App\Policies\DriverPolicy;
use App\Policies\RoutePolicy;
use App\Policies\CostItemPolicy;
use App\Policies\AiPredictionPolicy;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Spatie\Permission\Models\Role;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Role::class => RolePolicy::class,
        Company::class => CompanyPolicy::class,
        Warehouse::class => WarehousePolicy::class,
        Vehicle::class => VehiclePolicy::class,
        Driver::class => DriverPolicy::class,
        Route::class => RoutePolicy::class,
        CostItem::class => CostItemPolicy::class,
        AiPrediction::class => AiPredictionPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
