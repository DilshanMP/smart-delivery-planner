<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * AI Service
 * 
 * Handles communication with Flask API for ML predictions
 * 
 * @author Pasindu
 * @date January 2026
 */
class AIService
{
    /**
     * Flask API base URL
     */
    private $apiUrl;
    
    /**
     * Request timeout in seconds
     */
    private $timeout;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->apiUrl = config('services.ai.url', 'http://localhost:5000');
        $this->timeout = config('services.ai.timeout', 30);
    }
    
    /**
     * Check if API is available
     * 
     * @return bool
     */
    public function isAvailable(): bool
    {
        try {
            $response = Http::timeout(5)->get($this->apiUrl . '/health');
            return $response->successful() && $response->json('status') === 'healthy';
        } catch (Exception $e) {
            Log::error('AI API unavailable: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Predict delivery cost
     * 
     * @param array $routeData Route features
     * @return array Prediction result
     * @throws Exception
     */
    public function predictCost(array $routeData): array
    {
        try {
            // Validate required fields
            $required = [
                'total_distance_km',
                'total_stops',
                'num_companies',
                'total_sales_value',
                'vehicle_type',
                'day_of_week'
            ];
            
            foreach ($required as $field) {
                if (!isset($routeData[$field])) {
                    throw new Exception("Missing required field: {$field}");
                }
            }
            
            // Add default values for optional fields
            $routeData = array_merge([
                'month' => date('n'),
                'is_weekend' => in_array(date('N'), [6, 7]) ? 1 : 0,
                'driver_experience_years' => 5,
                'vehicle_age_years' => 3,
                'vehicle_fuel_efficiency' => 8.0,
                'base_fuel_price' => 350.0,
                'distance_fuel_cost' => $routeData['total_distance_km'] * 43.75,
                'driver_salary_cost' => 2500.0,
                'vehicle_maintenance_cost' => $routeData['total_distance_km'] * 12.0,
                'toll_charges' => $routeData['total_distance_km'] * 2.5
            ], $routeData);
            
            // Call API
            $response = Http::timeout($this->timeout)
                ->post($this->apiUrl . '/api/predict-cost', $routeData);
            
            if (!$response->successful()) {
                throw new Exception('API request failed: ' . $response->body());
            }
            
            $result = $response->json();
            
            if (!$result['success']) {
                throw new Exception($result['error'] ?? 'Unknown error');
            }
            
            return $result['prediction'];
            
        } catch (Exception $e) {
            Log::error('Cost prediction failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Optimize delivery route
     * 
     * @param array $locations Array of location objects
     * @param string $algorithm 'aco' or 'ga'
     * @return array Optimization result
     * @throws Exception
     */
    public function optimizeRoute(array $locations, string $algorithm = 'aco'): array
    {
        try {
            if (count($locations) < 3) {
                throw new Exception('Need at least 3 locations for optimization');
            }
            
            // Format locations
            $formattedLocations = [];
            foreach ($locations as $index => $location) {
                $formattedLocations[] = [
                    'id' => $location['id'] ?? $index,
                    'name' => $location['name'] ?? "Location {$index}",
                    'lat' => (float) $location['latitude'],
                    'lon' => (float) $location['longitude']
                ];
            }
            
            // Call API
            $response = Http::timeout($this->timeout * 2)
                ->post($this->apiUrl . '/api/optimize-route', [
                    'locations' => $formattedLocations,
                    'algorithm' => $algorithm
                ]);
            
            if (!$response->successful()) {
                throw new Exception('API request failed: ' . $response->body());
            }
            
            $result = $response->json();
            
            if (!$result['success']) {
                throw new Exception($result['error'] ?? 'Unknown error');
            }
            
            return [
                'optimization' => $result['optimization'],
                'route' => $result['route']
            ];
            
        } catch (Exception $e) {
            Log::error('Route optimization failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Compare ACO vs GA algorithms
     * 
     * @param array $locations Array of location objects
     * @return array Comparison result
     * @throws Exception
     */
    public function compareAlgorithms(array $locations): array
    {
        try {
            $aco = $this->optimizeRoute($locations, 'aco');
            $ga = $this->optimizeRoute($locations, 'ga');
            
            return [
                'aco' => $aco,
                'ga' => $ga,
                'recommendation' => $aco['optimization']['optimized_distance'] < 
                                  $ga['optimization']['optimized_distance'] ? 'ACO' : 'GA',
                'difference' => abs(
                    $aco['optimization']['optimized_distance'] - 
                    $ga['optimization']['optimized_distance']
                )
            ];
            
        } catch (Exception $e) {
            Log::error('Algorithm comparison failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get API statistics
     * 
     * @return array
     */
    public function getStats(): array
    {
        try {
            $response = Http::timeout(5)->get($this->apiUrl . '/');
            
            if ($response->successful()) {
                return $response->json();
            }
            
            return ['status' => 'unavailable'];
            
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
