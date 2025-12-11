<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\RouteReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RouteActualController;

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| All main web routes for the application. All CRUD/management routes are
| protected by the "auth" middleware.
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

// All below routes require authentication
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // User Management
    Route::resource('users', UserController::class);

    // Role Management
    Route::resource('roles', RoleController::class);
    Route::get('roles/{role}/permissions', [RoleController::class, 'permissions'])->name('roles.permissions');

    // Companies
    Route::resource('companies', CompanyController::class);

    // Warehouses
    Route::resource('warehouses', WarehouseController::class);

    // Vehicles
    Route::resource('vehicles', VehicleController::class);

    // Drivers
    Route::resource('drivers', DriverController::class);

    /**
     * IMPORTANT:
     * Place special route groups (static segments under /routes/*)
     * BEFORE Route::resource('routes', ...) to avoid conflicts
     * with the resource "show" route (GET /routes/{route}).
     */

    /**
     * ACTUAL ROUTE COMPLETION (static segment: /routes/actual)
     */
    Route::prefix('routes/actual')->name('routes.actual.')->group(function () {
        Route::get('/', [RouteActualController::class, 'index'])->name('index');
        Route::post('/{route}/start', [RouteActualController::class, 'start'])->name('start');
        Route::get('/{route}/complete', [RouteActualController::class, 'showComplete'])->name('complete');
        Route::post('/{route}/complete', [RouteActualController::class, 'storeCompletion'])->name('store-completion');

        // Optional detailed costs endpoints
        Route::get('/{route}/costs', [RouteActualController::class, 'showCosts'])->name('costs.show');
        Route::post('/{route}/costs', [RouteActualController::class, 'storeCosts'])->name('costs.store');
    });

    /**
     * REPORTS & ANALYTICS (static segment: /routes/reports)
     * FIXED: Changed ->name('') to ->name('dashboard')
     */
    Route::prefix('routes/reports')->name('routes.reports.')->group(function () {
        Route::get('/', [RouteReportController::class, 'dashboard'])->name('dashboard');
        Route::get('/estimated-vs-actual', [RouteReportController::class, 'estimatedVsActual'])->name('estimatedVsActual');
        Route::get('/company-performance', [RouteReportController::class, 'companyPerformance'])->name('companyPerformance');
        Route::get('/driver-performance', [RouteReportController::class, 'driverPerformance'])->name('driverPerformance');
        Route::get('/profitability', [RouteReportController::class, 'profitability'])->name('profitability');
        Route::get('/vehicle-usage', [RouteReportController::class, 'vehicleUsage'])->name('vehicleUsage');
    });

    /**
     * MAIN ROUTES CRUD (resource)
     * Now declared AFTER the special static subpaths to avoid capture.
     */
    Route::resource('routes', RouteController::class);

    /**
     * AJAX API ENDPOINTS (real-time calculations)
     */
    Route::prefix('api/routes')->name('api.routes.')->group(function () {
        Route::post('/calculate-distance', [RouteController::class, 'calculateDistance'])->name('calculate-distance');
        Route::post('/calculate-costs', [RouteController::class, 'calculateCosts'])->name('calculate-costs');
        Route::get('/{route}/company-allocations', [RouteController::class, 'getCompanyAllocations'])->name('company-allocations');
    });

}); // auth middleware group end

require __DIR__ . '/auth.php';
