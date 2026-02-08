@extends('layouts.admin')

@section('title', 'AI Route Optimization')
@section('page-title', 'AI Route Optimization')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
<li class="breadcrumb-item"><a href="{{ route('routes.index') }}">Routes</a></li>
<li class="breadcrumb-item active">AI Optimization</li>
@endsection

@section('content')

<div class="row">
    <!-- Left Column: Route Selection & Map -->
    <div class="col-md-8">

        <!-- Route Selection Card -->
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-route"></i> Select Route to Optimize</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="routeSelect">Choose Existing Route</label>
                    <select id="routeSelect" class="form-control select2" style="width: 100%;">
                        <option value="">-- Select a route --</option>
                        @foreach($routes as $route)
                            <option value="{{ $route->id }}"
                                    data-stops='@json($route->stops)'
                                    data-distance="{{ $route->estimated_distance_km }}"
                                    data-code="{{ $route->route_code }}"
                                    data-date="{{ $route->route_date->format('Y-m-d') }}">
                                {{ $route->route_code }} - {{ $route->route_date->format('M d, Y') }}
                                ({{ $route->stops->count() }} stops, {{ number_format($route->estimated_distance_km, 2) }}km)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div id="routeDetails" style="display:none;" class="mt-3">
                    <div class="alert alert-info">
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Route Code:</strong><br>
                                <span id="detailCode" class="h5 text-primary"></span>
                            </div>
                            <div class="col-md-4">
                                <strong>Total Stops:</strong><br>
                                <span id="detailStops" class="h5 text-success"></span>
                            </div>
                            <div class="col-md-4">
                                <strong>Current Distance:</strong><br>
                                <span id="detailDistance" class="h5 text-warning"></span> km
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Map Display -->
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-map-marked-alt"></i> Route Visualization</h3>
            </div>
            <div class="card-body">
                <div id="map" style="height: 500px; width: 100%; border-radius: 8px;"></div>

                <div class="mt-3 text-center">
                    <span class="badge badge-primary badge-lg mr-2" style="font-size: 14px; padding: 8px 15px;">
                        <i class="fas fa-circle" style="color: #0066FF;"></i> Original Route
                    </span>
                    <span class="badge badge-success badge-lg" style="font-size: 14px; padding: 8px 15px;">
                        <i class="fas fa-circle" style="color: #00FF00;"></i> Optimized Route
                    </span>
                </div>
            </div>
        </div>

        <!-- Stops Comparison -->
        <div class="card card-info" id="stopsCard" style="display:none;">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list-ol"></i> Route Stops Order</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="text-primary">
                            <i class="fas fa-map-pin"></i> Original Order
                        </h5>
                        <ol id="originalStopsList" class="list-group list-group-numbered"></ol>
                    </div>
                    <div class="col-md-6">
                        <h5 class="text-success">
                            <i class="fas fa-route"></i> Optimized Order
                        </h5>
                        <ol id="optimizedStopsList" class="list-group list-group-numbered"></ol>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Right Column: Optimization Controls -->
    <div class="col-md-4">

        <!-- Algorithm Selection -->
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-cogs"></i> Optimization Settings</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="algorithmSelect">Select Algorithm</label>
                    <select id="algorithmSelect" class="form-control" style="font-size: 15px; padding: 10px;">
                        <option value="aco">🐜 ACO - Ant Colony</option>
                        <option value="ga">🧬 GA - Genetic Algorithm</option>
                        <option value="both">📊 Compare Both</option>
                    </select>
                    <small class="form-text text-muted">
                        <strong>ACO:</strong> Best for 5-10 stops<br>
                        <strong>GA:</strong> Good for all sizes
                    </small>
                </div>

                <button type="button" class="btn btn-success btn-block btn-lg"
                        id="optimizeBtn" disabled style="font-size: 16px; padding: 12px;">
                    <i class="fas fa-magic"></i> Optimize Route
                </button>
            </div>
        </div>

        <!-- Results Card -->
        <div class="card card-danger" id="resultsCard" style="display:none;">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-line"></i> Optimization Results</h3>
            </div>
            <div class="card-body" id="resultsContent">
                <!-- Results will be inserted here -->
            </div>
        </div>

        <!-- Savings Calculator -->
        <div class="card card-warning" id="savingsCard" style="display:none;">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-piggy-bank"></i> Cost Savings</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Fuel Price (LKR/L)</label>
                    <input type="number" id="fuelPrice" class="form-control" value="350" min="0">
                </div>
                <div class="form-group">
                    <label>Vehicle Efficiency (km/L)</label>
                    <input type="number" id="fuelEfficiency" class="form-control" value="8" min="1">
                </div>
                <hr>
                <h5>Estimated Savings:</h5>
                <h2 class="text-success">Rs. <span id="costSavings">0</span></h2>
                <small class="text-muted">Per delivery</small>
            </div>
        </div>

        <!-- Save Optimization -->
        <div class="card" id="saveCard" style="display:none;">
            <div class="card-body">
                <button type="button" class="btn btn-primary btn-block btn-lg" id="saveOptimizationBtn">
                    <i class="fas fa-save"></i> Apply Optimization
                </button>
                <small class="text-muted d-block text-center mt-2">
                    This will update the route with optimized stop order
                </small>
            </div>
        </div>

    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css">
<style>
    /* Select2 Styling */
    .select2-container--bootstrap4 .select2-selection {
        height: 45px !important;
        padding: 8px 12px !important;
        font-size: 15px !important;
        border: 2px solid #d2d6de !important;
        border-radius: 6px !important;
    }

    .select2-container--bootstrap4 .select2-selection__rendered {
        line-height: 28px !important;
        color: #495057 !important;
    }

    .select2-container--bootstrap4 .select2-selection__arrow {
        height: 43px !important;
    }

    .select2-container--bootstrap4.select2-container--focus .select2-selection {
        border-color: #007bff !important;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25) !important;
    }

    .select2-dropdown {
        border: 2px solid #007bff !important;
        border-radius: 6px !important;
    }

    .select2-results__option {
        padding: 10px 15px !important;
        font-size: 14px !important;
    }

    .select2-results__option--highlighted {
        background-color: #007bff !important;
    }

    /* Map Styling */
    #map {
        border: 3px solid #ddd;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    /* List Group Numbered */
    .list-group-numbered {
        counter-reset: item;
        list-style: none;
        padding-left: 0;
    }

    .list-group-numbered li {
        counter-increment: item;
        margin-bottom: 8px;
        padding: 10px 12px;
        background: #f8f9fa;
        border-radius: 6px;
        border-left: 4px solid #007bff;
        transition: all 0.3s;
    }

    .list-group-numbered li:hover {
        background: #e9ecef;
        transform: translateX(5px);
    }

    .list-group-numbered li:before {
        content: counter(item) ". ";
        font-weight: bold;
        color: #007bff;
        margin-right: 8px;
        font-size: 16px;
    }

    /* Card Enhancements */
    .card {
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }

    .card-header {
        border-radius: 10px 10px 0 0 !important;
        font-weight: 600;
    }

    /* Button Styling */
    .btn-lg {
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s;
    }

    .btn-lg:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }

    /* Badge Styling */
    .badge-lg {
        padding: 10px 20px;
        border-radius: 20px;
    }

    /* Alert Improvements */
    .alert {
        border-radius: 8px;
        border-left: 5px solid;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}"></script>
<script>
let map;
let originalMarkers = [];
let optimizedMarkers = [];
let originalPolyline;
let optimizedPolyline;
let currentRoute = null;
let optimizationResult = null;
let optimizedStopsData = null;

$(document).ready(function() {
    // Initialize Select2 with Bootstrap theme
    $('#routeSelect').select2({
        theme: 'bootstrap4',
        placeholder: '-- Select a route --',
        allowClear: true,
        width: '100%'
    });

    initMap();

    $('#routeSelect').on('change', function() {
        const routeId = $(this).val();
        if (routeId) {
            loadRoute(routeId);
        } else {
            clearMap();
            $('#routeDetails').slideUp();
            $('#stopsCard').slideUp();
            $('#optimizeBtn').prop('disabled', true);
        }
    });

    $('#optimizeBtn').on('click', function() {
        optimizeRoute();
    });

    $('#saveOptimizationBtn').on('click', function() {
        saveOptimization();
    });

    $('#fuelPrice, #fuelEfficiency').on('change', function() {
        calculateSavings();
    });
});

function initMap() {
    const sriLanka = { lat: 7.8731, lng: 80.7718 };
    map = new google.maps.Map(document.getElementById('map'), {
        zoom: 8,
        center: sriLanka,
        mapTypeId: 'roadmap',
        styles: [
            {
                featureType: "poi",
                elementType: "labels",
                stylers: [{ visibility: "off" }]
            }
        ]
    });
}

function loadRoute(routeId) {
    const option = $(`#routeSelect option[value="${routeId}"]`);
    const stops = option.data('stops');
    const distance = option.data('distance');
    const routeCode = option.data('code');

    currentRoute = {
        id: routeId,
        stops: stops,
        distance: distance,
        code: routeCode
    };

    // Update details
    $('#detailCode').text(routeCode);
    $('#detailStops').text(stops.length);
    $('#detailDistance').text(parseFloat(distance).toFixed(2));
    $('#routeDetails').slideDown();

    // Display on map
    displayOriginalRoute(stops);

    // Enable optimize button
    $('#optimizeBtn').prop('disabled', false);

    // Show stops list
    displayStopsList(stops, 'original');
    $('#stopsCard').slideDown();

    // Hide previous results
    $('#resultsCard').slideUp();
    $('#savingsCard').slideUp();
    $('#saveCard').slideUp();
}

function clearMap() {
    originalMarkers.forEach(marker => marker.setMap(null));
    optimizedMarkers.forEach(marker => marker.setMap(null));
    originalMarkers = [];
    optimizedMarkers = [];
    if (originalPolyline) originalPolyline.setMap(null);
    if (optimizedPolyline) optimizedPolyline.setMap(null);

    map.setCenter({ lat: 7.8731, lng: 80.7718 });
    map.setZoom(8);
}

function displayOriginalRoute(stops) {
    clearMap();

    const bounds = new google.maps.LatLngBounds();
    stops.forEach((stop, index) => {
        const position = {
            lat: parseFloat(stop.latitude),
            lng: parseFloat(stop.longitude)
        };

        const marker = new google.maps.Marker({
            position: position,
            map: map,
            label: {
                text: (index + 1).toString(),
                color: 'white',
                fontWeight: 'bold'
            },
            title: stop.shop_name,
            icon: {
                path: google.maps.SymbolPath.CIRCLE,
                scale: 15,
                fillColor: '#0066FF',
                fillOpacity: 1,
                strokeColor: 'white',
                strokeWeight: 2
            }
        });

        originalMarkers.push(marker);
        bounds.extend(position);
    });

    // Draw polyline
    const path = stops.map(s => ({
        lat: parseFloat(s.latitude),
        lng: parseFloat(s.longitude)
    }));

    originalPolyline = new google.maps.Polyline({
        path: path,
        geodesic: true,
        strokeColor: '#0066FF',
        strokeOpacity: 0.8,
        strokeWeight: 4,
        map: map
    });

    map.fitBounds(bounds);
}

function displayStopsList(stops, type) {
    const listId = type === 'original' ? '#originalStopsList' : '#optimizedStopsList';
    $(listId).empty();

    stops.forEach((stop) => {
        $(listId).append(`<li>${stop.shop_name || stop.location_name}</li>`);
    });
}

async function optimizeRoute() {
    if (!currentRoute) {
        alert('Please select a route first!');
        return;
    }

    const algorithm = $('#algorithmSelect').val();
    const btn = $('#optimizeBtn');

    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Optimizing...');

    try {
        const locations = currentRoute.stops.map((stop, index) => ({
            id: index,
            name: stop.shop_name,
            lat: parseFloat(stop.latitude),
            lon: parseFloat(stop.longitude)
        }));

        const response = await fetch('http://localhost:5000/api/optimize-route', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                locations: locations,
                algorithm: algorithm === 'both' ? 'aco' : algorithm
            })
        });

        const result = await response.json();

        if (result.success) {
            optimizationResult = result.optimization;
            displayOptimizationResults(result);
            displayOptimizedRoute(result.route);
            calculateSavings();

            $('#resultsCard').slideDown();
            $('#savingsCard').slideDown();
            $('#saveCard').slideDown();
        } else {
            alert('Optimization failed: ' + result.error);
        }
    } catch (error) {
        console.error('Optimization Error:', error);
        alert('Could not connect to AI API. Make sure Flask server is running on port 5000.');
    } finally {
        btn.prop('disabled', false).html('<i class="fas fa-magic"></i> Optimize Route');
    }
}

function displayOptimizationResults(result) {
    const improvement = result.optimization.improvement_percentage;
    const distanceSaved = result.optimization.distance_saved;

    let badgeClass = 'success';
    let emoji = '🎉';
    let recommendation = 'Excellent optimization!';

    if (improvement < 10) {
        badgeClass = 'warning';
        emoji = '⚠️';
        recommendation = 'Moderate improvement';
    } else if (improvement < 5) {
        badgeClass = 'info';
        emoji = 'ℹ️';
        recommendation = 'Minor improvement';
    }

    const html = `
        <div class="alert alert-${badgeClass} text-center">
            <h4 style="font-size: 48px; margin: 0;">${emoji}</h4>
            <h5 class="mt-2"><i class="fas fa-check-circle"></i> Optimization Complete!</h5>
            <h2 class="mb-0">${improvement.toFixed(2)}% Improvement</h2>
        </div>

        <div class="row text-center mt-3">
            <div class="col-6">
                <h6 class="text-muted">ORIGINAL</h6>
                <h3 class="text-primary">${result.optimization.baseline_distance.toFixed(2)} km</h3>
            </div>
            <div class="col-6">
                <h6 class="text-muted">OPTIMIZED</h6>
                <h3 class="text-success">${result.optimization.optimized_distance.toFixed(2)} km</h3>
            </div>
        </div>

        <hr>

        <div class="text-center">
            <p class="mb-1"><strong>Distance Saved:</strong> <span class="text-success">${distanceSaved.toFixed(2)} km</span></p>
            <p class="mb-1"><strong>Algorithm:</strong> <span class="badge badge-info">${result.optimization.algorithm}</span></p>
            <p class="mb-0"><strong>Result:</strong> ${recommendation}</p>
        </div>
    `;

    $('#resultsContent').html(html);
}

function displayOptimizedRoute(optimizedStops) {
    // Clear previous optimized markers
    optimizedMarkers.forEach(marker => marker.setMap(null));
    optimizedStopsData = optimizedStops;
    optimizedMarkers = [];
    if (optimizedPolyline) optimizedPolyline.setMap(null);

    // Add new markers
    optimizedStops.forEach((stop, index) => {
        const position = { lat: stop.latitude, lng: stop.longitude };

        const marker = new google.maps.Marker({
            position: position,
            map: map,
            label: {
                text: (index + 1).toString(),
                color: 'white',
                fontWeight: 'bold'
            },
            title: stop.location_name,
            icon: {
                path: google.maps.SymbolPath.CIRCLE,
                scale: 15,
                fillColor: '#00CC00',
                fillOpacity: 1,
                strokeColor: 'white',
                strokeWeight: 2
            }
        });

        optimizedMarkers.push(marker);
    });

    // Draw optimized polyline
    const path = optimizedStops.map(s => ({ lat: s.latitude, lng: s.longitude }));

    optimizedPolyline = new google.maps.Polyline({
        path: path,
        geodesic: true,
        strokeColor: '#00FF00',
        strokeOpacity: 0.8,
        strokeWeight: 4,
        map: map
    });

    // Display optimized list
    displayStopsList(optimizedStops.map(s => ({ shop_name: s.location_name })), 'optimized');
}

function calculateSavings() {
    if (!optimizationResult) return;

    const distanceSaved = optimizationResult.distance_saved;
    const fuelPrice = parseFloat($('#fuelPrice').val()) || 350;
    const fuelEfficiency = parseFloat($('#fuelEfficiency').val()) || 8;

    const fuelSaved = distanceSaved / fuelEfficiency;
    const costSavings = fuelSaved * fuelPrice;

    $('#costSavings').text(costSavings.toFixed(0));
}

async function saveOptimization() {
    if (!optimizationResult || !currentRoute || !optimizedStopsData) {
        alert('Missing optimization data');
        return;
    }

    if (!confirm('Apply this optimization to the route?')) {
        return;
    }

    const btn = $('#saveOptimizationBtn');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

    try {
        // Helper function to find closest stop
        function findClosestStop(targetLat, targetLng, stops) {
            let closest = null;
            let minDistance = Infinity;

            stops.forEach(stop => {
                const stopLat = parseFloat(stop.latitude);
                const stopLng = parseFloat(stop.longitude);

                // Calculate distance (simple Euclidean for small areas)
                const distance = Math.sqrt(
                    Math.pow(stopLat - targetLat, 2) +
                    Math.pow(stopLng - targetLng, 2)
                );

                if (distance < minDistance) {
                    minDistance = distance;
                    closest = stop;
                }
            });

            return closest;
        }

        // Remove last stop if it's duplicate warehouse
        let stopsToProcess = optimizedStopsData;
        if (optimizedStopsData.length === currentRoute.stops.length + 1) {
            // Last stop is duplicate, remove it
            stopsToProcess = optimizedStopsData.slice(0, -1);
        }

        // Map each optimized stop to original stop
        const optimizedRoute = stopsToProcess.map((optStop, index) => {
            // Try using location_id first
            if (typeof optStop.location_id !== 'undefined') {
                const originalStop = currentRoute.stops[optStop.location_id];
                if (originalStop) {
                    return {
                        sequence: index + 1,
                        stop_id: originalStop.id,
                        latitude: optStop.latitude,
                        longitude: optStop.longitude
                    };
                }
            }

            // Fallback: find by closest coordinates
            const closestStop = findClosestStop(
                optStop.latitude,
                optStop.longitude,
                currentRoute.stops
            );

            if (!closestStop) {
                console.error('No stop found for:', optStop);
                return null;
            }

            return {
                sequence: index + 1,
                stop_id: closestStop.id,
                latitude: optStop.latitude,
                longitude: optStop.longitude
            };
        }).filter(Boolean);

        console.log('Mapping result:', {
            original: currentRoute.stops.length,
            optimized: stopsToProcess.length,
            mapped: optimizedRoute.length
        });

        if (optimizedRoute.length === 0) {
            throw new Error('No stops could be mapped');
        }

        const response = await fetch(`/routes/${currentRoute.id}/apply-optimization`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                optimization: optimizationResult,
                optimized_route: optimizedRoute
            })
        });

        const result = await response.json();

        if (result.success) {
            alert('✅ Optimization applied successfully!');
            window.location.href = '/routes';
        } else {
            alert('Failed to save: ' + result.error);
        }
    } catch (error) {
        console.error('Save Error:', error);
        alert('Could not save optimization: ' + error.message);
    } finally {
        btn.prop('disabled', false).html('<i class="fas fa-save"></i> Apply Optimization');
    }
}
</script>
@endpush
