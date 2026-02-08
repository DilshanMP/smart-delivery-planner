<?php

namespace App\Http\Controllers;

use App\Models\Route;
use App\Models\RouteOptimization;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Exception;

class RouteOptimizationController extends Controller
{
    public function index()
    {
        $routes = Route::with('stops')
            ->where('status', '!=', 'cancelled')
            ->orderBy('route_date', 'desc')
            ->get();

        return view('routes.optimize', compact('routes'));
    }

    public function applyOptimization(Request $request, Route $route): JsonResponse
    {
        try {
            Log::info('Optimization Request:', $request->all());

            // Validation with CORRECT table name
            $validated = $request->validate([
                'optimization' => 'required|array',
                'optimized_route' => 'required|array|min:1',
                'optimized_route.*.stop_id' => 'required|integer|exists:route_stops_new,id',
                'optimized_route.*.sequence' => 'required|integer|min:1'
            ]);

            $optimization = $validated['optimization'];

            // Update route
            $route->update([
                'is_optimized' => true,
                'optimization_algorithm' => strtoupper($optimization['algorithm'] ?? 'ACO'),
                'distance_saved_km' => $optimization['distance_saved'] ?? 0,
                'improvement_percentage' => $optimization['improvement_percentage'] ?? 0,
                'optimization_date' => now(),
                'estimated_distance_km' => $optimization['optimized_distance'] ?? $route->estimated_distance_km
            ]);

            // Update stop sequences - Your way (using relationship)
            foreach ($validated['optimized_route'] as $stopData) {
                $route->stops()
                    ->where('id', $stopData['stop_id'])
                    ->update(['stop_sequence' => $stopData['sequence']]);
            }

            // Log optimization history
            try {
                RouteOptimization::create([
                    'route_id' => $route->id,
                    'algorithm' => strtoupper($optimization['algorithm'] ?? 'ACO'),
                    'baseline_distance' => $optimization['baseline_distance'] ?? 0,
                    'optimized_distance' => $optimization['optimized_distance'] ?? 0,
                    'distance_saved' => $optimization['distance_saved'] ?? 0,
                    'improvement_percentage' => $optimization['improvement_percentage'] ?? 0,
                    'original_route' => json_encode($route->stops()->get()->toArray()),
                    'optimized_route' => json_encode($validated['optimized_route'])
                ]);
            } catch (Exception $e) {
                Log::warning('Could not save optimization history: ' . $e->getMessage());
            }

            Log::info('Optimization applied successfully for route: ' . $route->id);

            return response()->json([
                'success' => true,
                'message' => 'Optimization applied successfully!'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed:', ['errors' => $e->errors()]);

            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'details' => $e->errors()
            ], 422);

        } catch (Exception $e) {
            Log::error('Optimization failed:', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getOptimizationData(Route $route): JsonResponse
    {
        $stops = $route->stops()
            ->orderBy('stop_sequence')
            ->select('id', 'shop_name', 'shop_address', 'latitude', 'longitude', 'stop_sequence')
            ->get();

        return response()->json([
            'success' => true,
            'route' => [
                'id' => $route->id,
                'code' => $route->route_code,
                'distance' => $route->estimated_distance_km,
                'stops' => $stops
            ]
        ]);
    }
}
