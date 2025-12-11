<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleMapsService
{
    private $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.google_maps.api_key');
    }

    /**
     * Calculate distance and duration between two points using Google Maps Distance Matrix API
     *
     * @param float $fromLat
     * @param float $fromLng
     * @param float $toLat
     * @param float $toLng
     * @return array ['distance_km' => float, 'duration_minutes' => int, 'status' => string]
     */
    public function calculateDistance($fromLat, $fromLng, $toLat, $toLng)
    {
        try {
            $origin = "{$fromLat},{$fromLng}";
            $destination = "{$toLat},{$toLng}";

            $response = Http::get('https://maps.googleapis.com/maps/api/distancematrix/json', [
                'origins' => $origin,
                'destinations' => $destination,
                'key' => $this->apiKey,
                'units' => 'metric',
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if ($data['status'] === 'OK' && isset($data['rows'][0]['elements'][0])) {
                    $element = $data['rows'][0]['elements'][0];

                    if ($element['status'] === 'OK') {
                        $distanceMeters = $element['distance']['value'];
                        $durationSeconds = $element['duration']['value'];

                        return [
                            'success' => true,
                            'distance_km' => round($distanceMeters / 1000, 2),
                            'duration_minutes' => round($durationSeconds / 60),
                            'distance_text' => $element['distance']['text'],
                            'duration_text' => $element['duration']['text'],
                            'status' => 'OK',
                            'method' => 'google_maps'
                        ];
                    }
                }
            }

            // If Google Maps fails, fallback to Haversine
            Log::warning('Google Maps API failed, using Haversine fallback', [
                'response' => $response->json()
            ]);

            return $this->haversineDistance($fromLat, $fromLng, $toLat, $toLng);

        } catch (\Exception $e) {
            Log::error('Google Maps API error: ' . $e->getMessage());
            return $this->haversineDistance($fromLat, $fromLng, $toLat, $toLng);
        }
    }

    /**
     * Calculate route with multiple waypoints using Google Maps Directions API
     *
     * @param array $stops [['lat' => float, 'lng' => float], ...]
     * @return array
     */
    public function calculateRoute(array $stops)
    {
        if (count($stops) < 2) {
            return ['success' => false, 'error' => 'Need at least 2 stops'];
        }

        try {
            $origin = "{$stops[0]['lat']},{$stops[0]['lng']}";
            $destination = "{$stops[count($stops) - 1]['lat']},{$stops[count($stops) - 1]['lng']}";

            // Build waypoints (middle stops)
            $waypoints = [];
            for ($i = 1; $i < count($stops) - 1; $i++) {
                $waypoints[] = "{$stops[$i]['lat']},{$stops[$i]['lng']}";
            }

            $params = [
                'origin' => $origin,
                'destination' => $destination,
                'key' => $this->apiKey,
                'units' => 'metric',
            ];

            if (!empty($waypoints)) {
                $params['waypoints'] = 'optimize:false|' . implode('|', $waypoints);
            }

            $response = Http::get('https://maps.googleapis.com/maps/api/directions/json', $params);

            if ($response->successful()) {
                $data = $response->json();

                if ($data['status'] === 'OK' && isset($data['routes'][0])) {
                    $route = $data['routes'][0];
                    $legs = $route['legs'];

                    $totalDistance = 0;
                    $totalDuration = 0;
                    $legDetails = [];

                    foreach ($legs as $index => $leg) {
                        $totalDistance += $leg['distance']['value'];
                        $totalDuration += $leg['duration']['value'];

                        $legDetails[] = [
                            'leg_number' => $index + 1,
                            'distance_km' => round($leg['distance']['value'] / 1000, 2),
                            'duration_minutes' => round($leg['duration']['value'] / 60),
                            'start_address' => $leg['start_address'],
                            'end_address' => $leg['end_address'],
                        ];
                    }

                    return [
                        'success' => true,
                        'total_distance_km' => round($totalDistance / 1000, 2),
                        'total_duration_minutes' => round($totalDuration / 60),
                        'legs' => $legDetails,
                        'polyline' => $route['overview_polyline']['points'] ?? null,
                        'method' => 'google_maps'
                    ];
                }
            }

            // Fallback to calculating each leg separately
            return $this->calculateRouteFallback($stops);

        } catch (\Exception $e) {
            Log::error('Google Maps Directions API error: ' . $e->getMessage());
            return $this->calculateRouteFallback($stops);
        }
    }

    /**
     * Fallback: Calculate route leg by leg using Haversine
     */
    private function calculateRouteFallback(array $stops)
    {
        $totalDistance = 0;
        $totalDuration = 0;
        $legDetails = [];

        for ($i = 0; $i < count($stops) - 1; $i++) {
            $from = $stops[$i];
            $to = $stops[$i + 1];

            $result = $this->haversineDistance(
                $from['lat'], $from['lng'],
                $to['lat'], $to['lng']
            );

            $totalDistance += $result['distance_km'];
            $totalDuration += $result['duration_minutes'];

            $legDetails[] = [
                'leg_number' => $i + 1,
                'distance_km' => $result['distance_km'],
                'duration_minutes' => $result['duration_minutes'],
            ];
        }

        return [
            'success' => true,
            'total_distance_km' => round($totalDistance, 2),
            'total_duration_minutes' => $totalDuration,
            'legs' => $legDetails,
            'method' => 'haversine'
        ];
    }

    /**
     * Haversine formula fallback
     */
    private function haversineDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        // Estimate duration assuming 40 km/h average
        $duration = ($distance / 40) * 60;

        return [
            'success' => true,
            'distance_km' => round($distance, 2),
            'duration_minutes' => round($duration),
            'status' => 'OK',
            'method' => 'haversine'
        ];
    }

    /**
     * Geocode address to coordinates
     */
    public function geocodeAddress($address)
    {
        try {
            $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
                'address' => $address,
                'key' => $this->apiKey,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if ($data['status'] === 'OK' && isset($data['results'][0])) {
                    $location = $data['results'][0]['geometry']['location'];

                    return [
                        'success' => true,
                        'latitude' => $location['lat'],
                        'longitude' => $location['lng'],
                        'formatted_address' => $data['results'][0]['formatted_address'],
                    ];
                }
            }

            return ['success' => false, 'error' => 'Address not found'];

        } catch (\Exception $e) {
            Log::error('Geocoding error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Reverse geocode coordinates to address
     */
    public function reverseGeocode($lat, $lng)
    {
        try {
            $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
                'latlng' => "{$lat},{$lng}",
                'key' => $this->apiKey,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if ($data['status'] === 'OK' && isset($data['results'][0])) {
                    return [
                        'success' => true,
                        'address' => $data['results'][0]['formatted_address'],
                    ];
                }
            }

            return ['success' => false, 'error' => 'Location not found'];

        } catch (\Exception $e) {
            Log::error('Reverse geocoding error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
