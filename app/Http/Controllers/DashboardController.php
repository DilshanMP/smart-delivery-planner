<?php

namespace App\Http\Controllers;

use App\Models\Route;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index()
    {
        // Calculate statistics
        $stats = [
            'total_routes' => Route::count(),
            'active_vehicles' => Vehicle::where('is_active', true)->count(),
            'active_drivers' => Driver::where('is_active', true)->count(),
            'total_cost' => Route::whereMonth('delivery_date', now()->month)
                ->whereYear('delivery_date', now()->year)
                ->sum('actual_cost'),

            // Route status counts
            'planned_routes' => Route::where('status', 'planned')->count(),
            'in_progress_routes' => Route::where('status', 'in_progress')->count(),
            'completed_routes' => Route::where('status', 'completed')->count(),
        ];

        // Get recent routes (last 10)
        $recent_routes = Route::with(['company', 'vehicle', 'driver'])
            ->latest()
            ->take(10)
            ->get();

        // Monthly trend data (last 6 months)
        $monthly_data = [];
        $monthly_labels = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthly_labels[] = $date->format('M Y');
            $monthly_data[] = Route::whereYear('delivery_date', $date->year)
                ->whereMonth('delivery_date', $date->month)
                ->count();
        }

        return view('dashboard.index', compact('stats', 'recent_routes', 'monthly_labels', 'monthly_data'));
    }
}
