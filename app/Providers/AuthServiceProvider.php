<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

// Models
use App\Models\User;
use App\Models\Role as SpatieRole; // if you prefer aliasing (optional)
use Spatie\Permission\Models\Role;
use App\Models\Company;
use App\Models\Warehouse;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\Route;
use App\Models\CostItem;        // <-- corrected (was RouteCostItem)
use App\Models\AiPrediction;

// Policies
use App\Policies\UserPolicy;
use App\Policies\RolePolicy;
use App\Policies\CompanyPolicy;
use App\Policies\WarehousePolicy;
use App\Policies\VehiclePolicy;
use App\Policies\DriverPolicy;
use App\Policies\RoutePolicy;
use App\Policies\CostItemPolicy;    // matches CostItem model above
use App\Policies\AiPredictionPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class          => UserPolicy::class,
        Role::class          => RolePolicy::class,         // Spatie Role model
        Company::class       => CompanyPolicy::class,
        Warehouse::class     => WarehousePolicy::class,
        Vehicle::class       => VehiclePolicy::class,
        Driver::class        => DriverPolicy::class,
        Route::class         => RoutePolicy::class,
        CostItem::class      => CostItemPolicy::class,     // <-- corrected mapping
        AiPrediction::class  => AiPredictionPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        //
    }
}
