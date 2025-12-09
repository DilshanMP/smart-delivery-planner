<?php

namespace App\Http\Controllers;

use App\Models\Route;
use App\Models\Company;
use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RouteReportController extends Controller
{
    /**
     * Reports dashboard
     */
    public function index()
    {
        $stats = [
            'total_routes' => Route::count(),
            'completed_routes' => Route::where('status', 'completed')->count(),
            'in_progress' => Route::where('status', 'in_progress')->count(),
            'total_distance' => Route::sum('estimated_distance_km'),
            'total_cost' => Route::sum('actual_total_cost') ?: Route::sum('estimated_total_cost'),
            'total_sales' => DB::table('route_stops_new')->sum('sales_value'),
        ];

        // Recent routes
        $recentRoutes = Route::with(['company', 'driver', 'vehicle'])
                             ->latest('route_date')
                             ->limit(10)
                             ->get();

        return view('routes.reports.index', compact('stats', 'recentRoutes'));
    }

    /**
     * Estimated vs Actual Report
     */
    public function estimatedVsActual(Request $request)
    {
        $query = Route::with(['company', 'driver', 'vehicle'])
                      ->where('status', 'completed')
                      ->whereNotNull('actual_distance_km');

        // Date filter
        if ($request->filled('date_from')) {
            $query->where('route_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('route_date', '<=', $request->date_to);
        }

        // Company filter
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        // Driver filter
        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }

        $routes = $query->orderBy('route_date', 'desc')->paginate(25);

        // Calculate totals
        $totals = [
            'estimated_km' => $routes->sum('estimated_distance_km'),
            'actual_km' => $routes->sum('actual_distance_km'),
            'km_variance' => $routes->sum('km_variance'),
            'estimated_cost' => $routes->sum('estimated_total_cost'),
            'actual_cost' => $routes->sum('actual_total_cost'),
            'cost_variance' => $routes->sum('cost_variance'),
        ];

        // For filters
        $companies = Company::where('is_active', true)->orderBy('name')->get();
        $drivers = Driver::where('is_active', true)->orderBy('name')->get();

        return view('routes.reports.estimated-vs-actual', compact('routes', 'totals', 'companies', 'drivers'));
    }

    /**
     * Company Performance Report
     */
    public function companyPerformance(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::now()->subMonths(3)->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::now()->format('Y-m-d'));

        $companyPerformance = DB::table('route_company_allocations as rca')
            ->join('routes_new as r', 'rca.route_id', '=', 'r.id')
            ->join('companies as c', 'rca.company_id', '=', 'c.id')
            ->select(
                'c.id',
                'c.name',
                DB::raw('COUNT(DISTINCT r.id) as total_routes'),
                DB::raw('SUM(rca.number_of_stops) as total_stops'),
                DB::raw('SUM(rca.total_sales_value) as total_sales'),
                DB::raw('SUM(rca.total_sales_qty) as total_qty'),
                DB::raw('SUM(rca.allocated_cost) as total_cost'),
                DB::raw('SUM(rca.profit) as total_profit'),
                DB::raw('AVG(rca.profit_margin_percentage) as avg_profit_margin')
            )
            ->whereBetween('r.route_date', [$dateFrom, $dateTo])
            ->where('r.status', 'completed')
            ->groupBy('c.id', 'c.name')
            ->orderBy('total_sales', 'desc')
            ->get();

        return view('routes.reports.company-performance', compact('companyPerformance', 'dateFrom', 'dateTo'));
    }

    /**
     * Driver Performance Report
     */
    public function driverPerformance(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::now()->subMonths(3)->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::now()->format('Y-m-d'));

        $driverPerformance = DB::table('routes_new as r')
            ->join('drivers as d', 'r.driver_id', '=', 'd.id')
            ->leftJoin('route_stops_new as rs', 'r.id', '=', 'rs.route_id')
            ->select(
                'd.id',
                'd.name',
                DB::raw('COUNT(DISTINCT r.id) as total_routes'),
                DB::raw('SUM(r.estimated_distance_km) as total_km'),
                DB::raw('SUM(CASE WHEN r.status = "completed" THEN 1 ELSE 0 END) as completed_routes'),
                DB::raw('SUM(CASE WHEN r.cost_variance > 0 THEN 1 ELSE 0 END) as overbudget_count'),
                DB::raw('AVG(r.cost_variance_percentage) as avg_cost_variance_pct'),
                DB::raw('SUM(rs.sales_value) as total_sales'),
                DB::raw('AVG(CASE WHEN r.status = "completed"
                          THEN TIMESTAMPDIFF(HOUR, r.started_at, r.completed_at)
                          ELSE NULL END) as avg_route_hours')
            )
            ->whereBetween('r.route_date', [$dateFrom, $dateTo])
            ->groupBy('d.id', 'd.name')
            ->orderBy('total_routes', 'desc')
            ->get();

        return view('routes.reports.driver-performance', compact('driverPerformance', 'dateFrom', 'dateTo'));
    }

    /**
     * Route Profitability Report
     */
    public function profitability(Request $request)
    {
        $query = Route::with(['company', 'driver', 'vehicle'])
                      ->where('status', 'completed');

        // Date filter
        if ($request->filled('date_from')) {
            $query->where('route_date', '>=', $request->date_from);
        } else {
            $query->where('route_date', '>=', Carbon::now()->subMonths(3));
        }

        if ($request->filled('date_to')) {
            $query->where('route_date', '<=', $request->date_to);
        }

        // Company filter
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        $routes = $query->orderBy('route_date', 'desc')->get();

        // Calculate profitability for each route
        $profitabilityData = $routes->map(function($route) {
            $profitability = $route->getRouteProfitability();
            return [
                'route' => $route,
                'profitability' => $profitability,
            ];
        });

        // Totals
        $totals = [
            'total_sales' => $profitabilityData->sum('profitability.total_sales'),
            'return_sales' => $profitabilityData->sum('profitability.return_sales'),
            'net_sales' => $profitabilityData->sum('profitability.net_sales'),
            'total_cost' => $profitabilityData->sum('profitability.total_cost'),
            'profit' => $profitabilityData->sum('profitability.profit'),
        ];

        $totals['profit_margin'] = $totals['net_sales'] > 0
            ? ($totals['profit'] / $totals['net_sales']) * 100
            : 0;

        // For filters
        $companies = Company::where('is_active', true)->orderBy('name')->get();

        return view('routes.reports.profitability', compact('profitabilityData', 'totals', 'companies'));
    }

    /**
     * Vehicle Usage Report
     */
    public function vehicleUsage(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::now()->subMonths(3)->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::now()->format('Y-m-d'));

        $vehicleUsage = DB::table('routes_new as r')
            ->join('vehicles as v', 'r.vehicle_id', '=', 'v.id')
            ->select(
                'v.id',
                'v.registration_number',
                'v.vehicle_type',
                'v.fuel_efficiency',
                DB::raw('COUNT(r.id) as total_routes'),
                DB::raw('SUM(r.estimated_distance_km) as total_km'),
                DB::raw('SUM(CASE WHEN r.status = "completed" THEN 1 ELSE 0 END) as completed_routes'),
                DB::raw('SUM(r.estimated_fuel_cost) as total_fuel_cost'),
                DB::raw('AVG(r.estimated_fuel_cost / r.estimated_distance_km) as avg_cost_per_km')
            )
            ->whereBetween('r.route_date', [$dateFrom, $dateTo])
            ->groupBy('v.id', 'v.registration_number', 'v.vehicle_type', 'v.fuel_efficiency')
            ->orderBy('total_routes', 'desc')
            ->get();

        return view('routes.reports.vehicle-usage', compact('vehicleUsage', 'dateFrom', 'dateTo'));
    }

    /**
     * Export data for Power BI
     */
    public function exportForPowerBI(Request $request)
    {
        $type = $request->input('type', 'all');

        switch ($type) {
            case 'estimated-vs-actual':
                $data = $this->getEstimatedVsActualData($request);
                break;
            case 'company-allocation':
                $data = $this->getCompanyAllocationData($request);
                break;
            case 'driver-performance':
                $data = $this->getDriverPerformanceData($request);
                break;
            default:
                $data = $this->getAllRoutesData($request);
        }

        return response()->json($data);
    }

    private function getAllRoutesData($request)
    {
        return Route::with(['company', 'driver', 'vehicle', 'stops'])
                    ->get()
                    ->map(function($route) {
                        return [
                            'route_id' => $route->id,
                            'route_code' => $route->route_code,
                            'route_date' => $route->route_date,
                            'company' => $route->company->name,
                            'driver' => $route->driver->name,
                            'vehicle' => $route->vehicle->registration_number,
                            'estimated_distance_km' => $route->estimated_distance_km,
                            'actual_distance_km' => $route->actual_distance_km,
                            'km_variance' => $route->km_variance,
                            'estimated_cost' => $route->estimated_total_cost,
                            'actual_cost' => $route->actual_total_cost,
                            'cost_variance' => $route->cost_variance,
                            'status' => $route->status,
                            'total_stops' => $route->stops->count(),
                            'total_sales' => $route->stops->sum('sales_value'),
                        ];
                    });
    }

    private function getCompanyAllocationData($request)
    {
        return DB::table('route_company_allocations as rca')
            ->join('routes_new as r', 'rca.route_id', '=', 'r.id')
            ->join('companies as c', 'rca.company_id', '=', 'c.id')
            ->select(
                'r.route_code',
                'r.route_date',
                'c.name as company_name',
                'rca.total_sales_value',
                'rca.allocated_cost',
                'rca.profit',
                'rca.profit_margin_percentage'
            )
            ->get();
    }

    private function getDriverPerformanceData($request)
    {
        return DB::table('routes_new as r')
            ->join('drivers as d', 'r.driver_id', '=', 'd.id')
            ->select(
                'r.route_date',
                'd.name as driver_name',
                'r.estimated_distance_km',
                'r.actual_distance_km',
                'r.cost_variance_percentage'
            )
            ->get();
    }
}
