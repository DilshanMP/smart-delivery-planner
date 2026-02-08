<?php

namespace App\Http\Controllers;

use App\Models\Route;
use App\Models\RouteCompanyAllocation;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RouteReportController extends Controller
{
    /**
     * Reports Dashboard - Works with both /dashboard and /index routes
     */
    public function dashboard()
    {
        $stats = [
            'total_routes' => Route::where('status', 'completed')->count(),
            'total_distance' => Route::where('status', 'completed')->sum('actual_distance_km') ?? 0,
            'total_cost' => Route::where('status', 'completed')->sum('actual_total_cost') ?? 0,
            'total_sales' => RouteCompanyAllocation::sum('total_sales_value') ?? 0,
        ];

        return view('routes.reports.dashboard', compact('stats'));
    }

    /**
     * Alternative method - calls dashboard()
     */
    public function index()
    {
        return $this->dashboard();
    }

    /**
     * Estimated vs Actual Report
     */
    public function estimatedVsActual(Request $request)
    {
        $query = Route::with(['company', 'driver', 'vehicle'])
            ->where('status', 'completed')
            ->whereNotNull('actual_total_cost')
            ->whereNotNull('actual_distance_km');

        if ($request->filled('date_from')) {
            $query->where('route_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('route_date', '<=', $request->date_to);
        }
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        $routes = $query->orderBy('route_date', 'desc')->paginate(20);
        $companies = Company::where('is_active', true)->get();

        return view('routes.reports.estimated-vs-actual', compact('routes', 'companies'));
    }

    /**
     * Company Performance Report
     */
    public function companyPerformance(Request $request)
    {
        try {
            $query = DB::table('route_company_allocations')
                ->join('companies', 'route_company_allocations.company_id', '=', 'companies.id')
                ->join('routes_new', 'route_company_allocations.route_id', '=', 'routes_new.id')
                ->select(
                    'companies.id as company_id',
                    'companies.name as company_name',
                    DB::raw('COUNT(DISTINCT route_company_allocations.route_id) as total_routes'),
                    DB::raw('COALESCE(SUM(route_company_allocations.total_sales_qty), 0) as total_stops'),
                    DB::raw('COALESCE(SUM(route_company_allocations.total_sales_value), 0) as total_sales'),
                    DB::raw('COALESCE(SUM(route_company_allocations.allocated_cost), 0) as total_cost'),
                    DB::raw('(COALESCE(SUM(route_company_allocations.total_sales_value), 0) - COALESCE(SUM(route_company_allocations.allocated_cost), 0)) as total_profit'),
                    DB::raw('CASE WHEN SUM(route_company_allocations.total_sales_value) > 0 THEN (AVG((route_company_allocations.total_sales_value - route_company_allocations.allocated_cost) / NULLIF(route_company_allocations.total_sales_value, 0)) * 100) ELSE 0 END as avg_profit_margin')
                )
                ->where('routes_new.status', 'completed')
                ->groupBy('companies.id', 'companies.name');

            if ($request->filled('date_from')) {
                $query->where('routes_new.route_date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->where('routes_new.route_date', '<=', $request->date_to);
            }

            $companyPerformance = $query->get();

            return view('routes.reports.company-performance', compact('companyPerformance'));
        } catch (\Exception $e) {
            return back()->with('error', 'Error loading company performance: ' . $e->getMessage());
        }
    }

    /**
     * Driver Performance Report
     */
    public function driverPerformance(Request $request)
    {
        try {
            $query = DB::table('drivers')
                ->leftJoin('routes_new', function($join) use ($request) {
                    $join->on('drivers.id', '=', 'routes_new.driver_id')
                         ->where('routes_new.status', '=', 'completed');

                    if ($request->filled('date_from')) {
                        $join->where('routes_new.route_date', '>=', $request->date_from);
                    }
                    if ($request->filled('date_to')) {
                        $join->where('routes_new.route_date', '<=', $request->date_to);
                    }
                })
                ->select(
                    'drivers.id as driver_id',
                    'drivers.name as driver_name',
                    DB::raw('COALESCE(COUNT(routes_new.id), 0) as total_routes'),
                    DB::raw('COALESCE(SUM(routes_new.actual_distance_km), 0) as total_distance'),
                    DB::raw('COALESCE(AVG(routes_new.cost_variance_percentage), 0) as avg_cost_variance'),
                    DB::raw('COALESCE(SUM(CASE WHEN routes_new.cost_variance_percentage > 0 THEN 1 ELSE 0 END), 0) as over_budget_count')
                )
                ->where('drivers.is_active', 1)
                ->groupBy('drivers.id', 'drivers.name')
                ->havingRaw('COUNT(routes_new.id) > 0');

            $driverPerformance = $query->get();

            return view('routes.reports.driver-performance', compact('driverPerformance'));
        } catch (\Exception $e) {
            return back()->with('error', 'Error loading driver performance: ' . $e->getMessage());
        }
    }

    /**
     * Profitability Report
     */
    public function profitability(Request $request)
    {
        $query = Route::with(['company', 'companyAllocations'])
            ->where('status', 'completed')
            ->whereNotNull('actual_total_cost');

        if ($request->filled('date_from')) {
            $query->where('route_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('route_date', '<=', $request->date_to);
        }
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        $routes = $query->orderBy('route_date', 'desc')->paginate(20);
        $companies = Company::where('is_active', true)->get();

        return view('routes.reports.profitability', compact('routes', 'companies'));
    }

    /**
     * Vehicle Usage Report
     */
    public function vehicleUsage(Request $request)
    {
        try {
            $query = DB::table('vehicles')
                ->leftJoin('routes_new', function($join) use ($request) {
                    $join->on('vehicles.id', '=', 'routes_new.vehicle_id')
                         ->where('routes_new.status', '=', 'completed');

                    if ($request->filled('date_from')) {
                        $join->where('routes_new.route_date', '>=', $request->date_from);
                    }
                    if ($request->filled('date_to')) {
                        $join->where('routes_new.route_date', '<=', $request->date_to);
                    }
                })
                ->select(
                    'vehicles.id as vehicle_id',
                    'vehicles.registration_number as vehicle_number',
                    'vehicles.vehicle_type',
                    DB::raw('COALESCE(COUNT(routes_new.id), 0) as total_routes'),
                    DB::raw('COALESCE(SUM(routes_new.actual_distance_km), 0) as total_distance'),
                    DB::raw('COALESCE(AVG(routes_new.actual_distance_km), 0) as avg_distance_per_route'),
                    DB::raw('COALESCE(SUM(routes_new.actual_fuel_cost), 0) as total_fuel_cost'),
                    DB::raw('CASE WHEN SUM(routes_new.actual_distance_km) > 0 THEN COALESCE(SUM(routes_new.actual_fuel_cost) / NULLIF(SUM(routes_new.actual_distance_km), 0), 0) ELSE 0 END as avg_fuel_cost_per_km')
                )
                ->where('vehicles.is_active', 1)
                ->groupBy('vehicles.id', 'vehicles.registration_number', 'vehicles.vehicle_type')
                ->havingRaw('COUNT(routes_new.id) > 0');

            $vehicleUsage = $query->get();

            return view('routes.reports.vehicle-usage', compact('vehicleUsage'));
        } catch (\Exception $e) {
            return back()->with('error', 'Error loading vehicle usage: ' . $e->getMessage());
        }
    }
}
