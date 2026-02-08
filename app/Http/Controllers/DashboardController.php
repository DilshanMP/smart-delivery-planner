<?php

namespace App\Http\Controllers;

use App\Models\Route;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\Company;
use App\Models\RouteCompanyAllocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with cost percentage tracking.
     */
    public function index()
    {
        // Basic statistics
        $stats = [
            'total_routes' => Route::count(),
            'active_vehicles' => Vehicle::where('is_active', true)->count(),
            'active_drivers' => Driver::where('is_active', true)->count(),
            'total_cost' => Route::whereMonth('route_date', now()->month)
                ->whereYear('route_date', now()->year)
                ->sum('actual_total_cost') ?? 0,
            'planned_routes' => Route::where('status', 'planned')->count(),
            'in_progress_routes' => Route::where('status', 'in_progress')->count(),
            'completed_routes' => Route::where('status', 'completed')->count(),
        ];

        // ✅ NEW: Calculate Current Month Cost Percentage
        $currentMonthRoutes = Route::whereMonth('route_date', now()->month)
            ->whereYear('route_date', now()->year)
            ->where('status', 'completed')
            ->get();

        $totalSalesCurrentMonth = RouteCompanyAllocation::whereIn('route_id', $currentMonthRoutes->pluck('id'))
            ->sum('total_sales_value');

        $totalCostCurrentMonth = $currentMonthRoutes->sum('actual_total_cost') ?:
                                $currentMonthRoutes->sum('estimated_total_cost');

        $stats['current_month_cost_percentage'] = ($totalSalesCurrentMonth > 0)
            ? round(($totalCostCurrentMonth / $totalSalesCurrentMonth) * 100, 2)
            : 0;

        $stats['current_month_sales'] = $totalSalesCurrentMonth;

        // Get recent routes with cost percentage
        $recent_routes = Route::with(['company', 'vehicle', 'driver', 'companyAllocations'])
            ->whereNotNull('route_date')
            ->latest('route_date')
            ->take(10)
            ->get()
            ->map(function($route) {
                $totalSales = $route->companyAllocations->sum('total_sales_value');
                $totalCost = $route->actual_total_cost ?: $route->estimated_total_cost;

                $route->total_sales = $totalSales;
                $route->cost_percentage = ($totalSales > 0)
                    ? round(($totalCost / $totalSales) * 100, 2)
                    : 0;

                return $route;
            });

        // ✅ NEW: Monthly Cost Percentage Trend (Last 6 Months)
        $monthly_data = [];
        $monthly_labels = [];
        $monthly_cost_percentage = [];
        $monthly_sales = [];
        $monthly_costs = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthly_labels[] = $date->format('M Y');

            $monthRoutes = Route::whereYear('route_date', $date->year)
                ->whereMonth('route_date', $date->month)
                ->where('status', 'completed')
                ->get();

            $monthly_data[] = $monthRoutes->count();

            $monthSales = RouteCompanyAllocation::whereIn('route_id', $monthRoutes->pluck('id'))
                ->sum('total_sales_value');

            $monthCost = $monthRoutes->sum('actual_total_cost') ?:
                        $monthRoutes->sum('estimated_total_cost');

            $monthly_sales[] = $monthSales;
            $monthly_costs[] = $monthCost;

            $monthly_cost_percentage[] = ($monthSales > 0)
                ? round(($monthCost / $monthSales) * 100, 2)
                : 0;
        }

        // ✅ NEW: Yearly Cost Percentage (Current Year by Month)
        $yearly_labels = [];
        $yearly_cost_percentage = [];
        $yearly_sales = [];
        $yearly_costs = [];

        for ($month = 1; $month <= 12; $month++) {
            $yearly_labels[] = date('M', mktime(0, 0, 0, $month, 1));

            $yearMonthRoutes = Route::whereYear('route_date', now()->year)
                ->whereMonth('route_date', $month)
                ->where('status', 'completed')
                ->get();

            $yearMonthSales = RouteCompanyAllocation::whereIn('route_id', $yearMonthRoutes->pluck('id'))
                ->sum('total_sales_value');

            $yearMonthCost = $yearMonthRoutes->sum('actual_total_cost') ?:
                            $yearMonthRoutes->sum('estimated_total_cost');

            $yearly_sales[] = $yearMonthSales;
            $yearly_costs[] = $yearMonthCost;

            $yearly_cost_percentage[] = ($yearMonthSales > 0)
                ? round(($yearMonthCost / $yearMonthSales) * 100, 2)
                : 0;
        }

        // ✅ NEW: Best & Worst Routes by Cost Percentage
        $completedRoutesWithPercentage = Route::with(['company', 'driver', 'companyAllocations'])
            ->where('status', 'completed')
            ->whereNotNull('route_date')
            ->get()
            ->map(function($route) {
                $totalSales = $route->companyAllocations->sum('total_sales_value');
                $totalCost = $route->actual_total_cost ?: $route->estimated_total_cost;

                $route->total_sales = $totalSales;
                $route->cost_percentage = ($totalSales > 0)
                    ? round(($totalCost / $totalSales) * 100, 2)
                    : 0;

                return $route;
            })
            ->filter(function($route) {
                return $route->total_sales > 0; // Only routes with sales
            });

        $best_routes = $completedRoutesWithPercentage->sortBy('cost_percentage')->take(5);
        $worst_routes = $completedRoutesWithPercentage->sortByDesc('cost_percentage')->take(5);

        return view('dashboard.index', compact(
            'stats',
            'recent_routes',
            'monthly_labels',
            'monthly_data',
            'monthly_cost_percentage',
            'monthly_sales',
            'monthly_costs',
            'yearly_labels',
            'yearly_cost_percentage',
            'yearly_sales',
            'yearly_costs',
            'best_routes',
            'worst_routes'
        ));
    }
}
