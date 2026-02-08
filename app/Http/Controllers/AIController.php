<?php

namespace App\Http\Controllers;

use App\Services\AIService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

/**
 * AI Controller
 *
 * Handles AI-powered features for route optimization
 *
 * @author Pasindu
 * @date January 2026
 */
class AIController extends Controller
{
    protected $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Show AI dashboard
     */
    public function index()
    {
        $apiStatus = $this->aiService->isAvailable();

        return view('ai.dashboard', [
            'api_status' => $apiStatus
        ]);
    }

    /**
     * Cost prediction form
     */
    public function predictCostForm()
    {
        return view('ai.predict-cost');
    }

    /**
     * Handle cost prediction
     */
    public function predictCost(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'total_distance_km' => 'required|numeric|min:1',
                'total_stops' => 'required|integer|min:1',
                'num_companies' => 'required|integer|min:1',
                'total_sales_value' => 'required|numeric|min:0',
                'vehicle_type' => 'required|string',
                'day_of_week' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday'
            ]);

            $prediction = $this->aiService->predictCost($validated);

            return response()->json([
                'success' => true,
                'prediction' => $prediction
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Route optimization form
     */
    public function optimizeRouteForm()
    {
        return view('ai.optimize-route');
    }

    /**
     * Handle route optimization
     */
    public function optimizeRoute(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'locations' => 'required|array|min:3',
                'locations.*.name' => 'required|string',
                'locations.*.latitude' => 'required|numeric|between:-90,90',
                'locations.*.longitude' => 'required|numeric|between:-180,180',
                'algorithm' => 'required|in:aco,ga'
            ]);

            $result = $this->aiService->optimizeRoute(
                $validated['locations'],
                $validated['algorithm']
            );

            return response()->json([
                'success' => true,
                'result' => $result
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Compare algorithms
     */
    public function compareAlgorithms(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'locations' => 'required|array|min:3',
                'locations.*.name' => 'required|string',
                'locations.*.latitude' => 'required|numeric',
                'locations.*.longitude' => 'required|numeric'
            ]);

            $comparison = $this->aiService->compareAlgorithms($validated['locations']);

            return response()->json([
                'success' => true,
                'comparison' => $comparison
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * API status check
     */
    public function apiStatus(): JsonResponse
    {
        $isAvailable = $this->aiService->isAvailable();
        $stats = $this->aiService->getStats();

        return response()->json([
            'available' => $isAvailable,
            'stats' => $stats
        ]);
    }
}
