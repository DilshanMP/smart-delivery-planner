@extends('layouts.admin')

@section('title', 'Plan New Route')
@section('page-title', 'Plan New Route')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
<li class="breadcrumb-item"><a href="{{ route('routes.index') }}">Routes</a></li>
<li class="breadcrumb-item active">Plan New Route</li>
@endsection

@section('content')

<form action="{{ route('routes.store') }}" method="POST" id="routeForm">
    @csrf

    <div class="row">
        <!-- Left Column: Route Planning -->
        <div class="col-md-8">

            <!-- Basic Info Card -->
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle"></i> Route Information</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Route Code <span class="text-danger">*</span></label>
                                <input type="text" name="route_code" class="form-control @error('route_code') is-invalid @enderror"
                                       value="{{ old('route_code', 'RT-' . date('Ymd') . '-' . rand(100, 999)) }}" required>
                                @error('route_code')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Route Date <span class="text-danger">*</span></label>
                                <input type="date" name="route_date" id="route_date" class="form-control @error('route_date') is-invalid @enderror"
                                       value="{{ old('route_date', date('Y-m-d')) }}" required>
                                @error('route_date')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Main Company <span class="text-danger">*</span></label>
                                <select name="company_id" class="form-control select2 @error('company_id') is-invalid @enderror" required>
                                    <option value="">Select Company</option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                            {{ $company->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('company_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Start Location (Warehouse)</label>
                                <select name="warehouse_id" id="warehouse_id" class="form-control select2">
                                    <option value="">Select Warehouse</option>
                                    @foreach($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}"
                                                data-lat="{{ $warehouse->latitude }}"
                                                data-lng="{{ $warehouse->longitude }}"
                                                {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                            {{ $warehouse->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Driver <span class="text-danger">*</span></label>
                                <select name="driver_id" id="driver_id" class="form-control select2 @error('driver_id') is-invalid @enderror" required>
                                    <option value="">Select Driver</option>
                                    @foreach($drivers as $driver)
                                        <option value="{{ $driver->id }}"
                                                data-experience="{{ $driver->experience_years ?? 5 }}"
                                                {{ old('driver_id') == $driver->id ? 'selected' : '' }}>
                                            {{ $driver->name }} - {{ $driver->license_number }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('driver_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Vehicle <span class="text-danger">*</span></label>
                                <select name="vehicle_id" id="vehicle_id" class="form-control select2 @error('vehicle_id') is-invalid @enderror" required>
                                    <option value="">Select Vehicle</option>
                                    @foreach($vehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}"
                                                data-efficiency="{{ $vehicle->fuel_efficiency }}"
                                                data-type="{{ $vehicle->vehicle_type }}"
                                                data-age="{{ $vehicle->vehicle_age ?? 3 }}"
                                                {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                                            {{ $vehicle->registration_number }} ({{ $vehicle->vehicle_type }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('vehicle_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Delivery Type <span class="text-danger">*</span></label>
                                <select name="delivery_type" class="form-control @error('delivery_type') is-invalid @enderror" required>
                                    <option value="own" {{ old('delivery_type') == 'own' ? 'selected' : '' }}>Own Delivery</option>
                                    <option value="outside" {{ old('delivery_type') == 'outside' ? 'selected' : '' }}>Outside Delivery</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Start Time</label>
                                <input type="time" name="start_time" class="form-control" value="{{ old('start_time') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>End Time</label>
                                <input type="time" name="end_time" class="form-control" value="{{ old('end_time') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- COMPANY-WISE SALES ENTRY -->
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-line"></i> Company-wise Sales (Total for Route)</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> <strong>Enter total sales for each company on this route.</strong>
                        <br>These will be used for AI cost prediction and profitability analysis.
                    </div>

                    <div id="companySalesContainer">
                        <div class="company-sales-row mb-3" data-index="0">
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label>Company</label>
                                        <select name="company_sales[0][company_id]" class="form-control select2" required>
                                            <option value="">Select Company</option>
                                            @foreach($companies as $company)
                                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Sales Value (LKR)</label>
                                        <input type="number" step="0.01" name="company_sales[0][sales_value]"
                                               class="form-control company-sales-value" min="0" required
                                               onchange="calculateTotalSales()">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Quantity</label>
                                        <input type="number" name="company_sales[0][sales_qty]"
                                               class="form-control" min="0" required>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <label>&nbsp;</label>
                                    <button type="button" class="btn btn-danger btn-block" onclick="removeCompanySales(0)" style="display:none;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-success btn-sm" onclick="addCompanySales()">
                        <i class="fas fa-plus"></i> Add Another Company
                    </button>

                    <hr>

                    <div class="row">
                        <div class="col-md-12">
                            <h5>Total Sales Value: LKR <span id="totalSalesValue" class="text-success">0</span></h5>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Google Maps Route Builder -->
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-map-marked-alt"></i> Route Planning</h3>
                </div>
                <div class="card-body">
                    <!-- ADDRESS SEARCH BOX -->
                    <div class="form-group">
                        <label><i class="fas fa-search"></i> Search Address</label>
                        <input id="searchBox" type="text" class="form-control form-control-lg"
                               placeholder="Type address (e.g., Galle Road, Colombo) and press Enter...">
                        <small class="text-muted">Search and click suggestion, OR click directly on map to add stops</small>
                    </div>

                    <div id="map" style="height: 500px; width: 100%;"></div>

                    <div class="mt-3">
                        <button type="button" class="btn btn-warning btn-sm" onclick="clearAllStops()">
                            <i class="fas fa-trash"></i> Clear All Stops
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" onclick="recalculateRoute()">
                            <i class="fas fa-sync"></i> Recalculate Route
                        </button>
                        <span class="ml-3 text-muted">
                            <i class="fas fa-info-circle"></i> Total Distance: <strong id="totalDistance">0 km</strong>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Stops List -->
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-map-pin"></i> Route Stops (<span id="stopCount">0</span>)</h3>
                </div>
                <div class="card-body">
                    <div id="stopsList" class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="25%">Shop Name</th>
                                    <th width="40%">Address</th>
                                    <th width="15%">Type</th>
                                    <th width="15%">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="stopsTableBody">
                                <tr id="noStopsRow">
                                    <td colspan="5" class="text-center text-muted">
                                        No stops added yet. Search address above or click on map.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Hidden inputs for stops data -->
                    <div id="stopsHiddenInputs"></div>
                </div>
            </div>

        </div>

        <!-- Right Column: Cost Estimation with AI -->
        <div class="col-md-4">

            <!-- AI COST PREDICTION CARD (NEW!) -->
            <div class="card card-danger">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-robot"></i> AI Cost Prediction</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Get AI-powered cost prediction with 95%+ accuracy
                    </div>

                    <button type="button" class="btn btn-danger btn-block btn-lg" id="aiPredictBtn" onclick="getAIPrediction()">
                        <i class="fas fa-brain"></i> Predict Cost with AI
                    </button>

                    <!-- AI Prediction Result (Hidden initially) -->
                    <div id="aiPredictionResult" style="display:none;" class="mt-3">
                        <div class="alert alert-success">
                            <h5><i class="fas fa-check-circle"></i> AI Prediction</h5>
                            <h3 class="mb-2">LKR <span id="aiPredictedCost">0</span></h3>
                            <div class="row">
                                <div class="col-6">
                                    <small>Cost %:</small><br>
                                    <strong><span id="aiCostPercentage">0</span>%</strong>
                                </div>
                                <div class="col-6">
                                    <small>Confidence:</small><br>
                                    <strong><span id="aiConfidence">0</span>%</strong>
                                </div>
                            </div>
                            <hr>
                            <div class="text-center">
                                <button type="button" class="btn btn-sm btn-success" onclick="useAIPrediction()">
                                    <i class="fas fa-check"></i> Use This Prediction
                                </button>
                            </div>
                        </div>

                        <!-- Confidence Interval -->
                        <div class="card bg-light">
                            <div class="card-body p-2">
                                <small class="text-muted">
                                    <i class="fas fa-chart-line"></i> Confidence Range:<br>
                                    LKR <span id="aiLowerBound">0</span> - <span id="aiUpperBound">0</span>
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden inputs for AI prediction -->
                    <input type="hidden" name="ai_predicted_cost" id="ai_predicted_cost">
                    <input type="hidden" name="ai_confidence" id="ai_confidence_hidden">
                </div>
            </div>

            <!-- Manual Cost Estimation Card -->
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-calculator"></i> Manual Cost Estimation</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Estimated Distance (km)</label>
                        <input type="number" step="0.01" name="estimated_distance_km" id="estimated_distance_km"
                               class="form-control" value="0" readonly>
                        <small class="text-muted">Auto-calculated from Google Maps</small>
                    </div>

                    <div class="form-group">
                        <label>Number of Days <span class="text-danger">*</span></label>
                        <input type="number" name="estimated_days" id="estimated_days"
                               class="form-control" value="1" min="1" required onchange="calculateCosts()">
                    </div>

                    <hr>

                    <div class="form-group">
                        <label>Fuel Rate (LKR per litre)</label>
                        <input type="number" step="0.01" name="estimated_fuel_rate_per_litre" id="fuel_rate"
                               class="form-control" value="350" onchange="calculateCosts()">
                    </div>

                    <div class="form-group">
                        <label>Estimated Fuel Cost (LKR)</label>
                        <input type="number" step="0.01" name="estimated_fuel_cost" id="estimated_fuel_cost"
                               class="form-control" value="0" readonly>
                        <small class="text-muted">Auto-calculated</small>
                    </div>

                    <div class="form-group">
                        <label>Meal Cost (LKR)</label>
                        <input type="number" step="0.01" name="estimated_meal_cost" id="meal_cost"
                               class="form-control" value="1500" onchange="calculateCosts()">
                    </div>

                    <div class="form-group">
                        <label>Accommodation Cost (LKR)</label>
                        <input type="number" step="0.01" name="estimated_accommodation_cost" id="accommodation_cost"
                               class="form-control" value="0" onchange="calculateCosts()">
                        <small class="text-muted">For multi-day routes</small>
                    </div>

                    <hr>

                    <div class="form-group">
                        <label><strong>TOTAL MANUAL COST</strong></label>
                        <h3 class="text-primary">LKR <span id="total_cost">0</span></h3>
                    </div>

                    <div class="alert alert-warning">
                        <h5><i class="fas fa-percentage"></i> Cost Ratio</h5>
                        <h3 class="mb-0"><span id="cost_percentage">0</span>%</h3>
                        <small>Cost as % of Total Sales</small>
                    </div>

                    <div class="form-group">
                        <label>Notes</label>
                        <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-success btn-block btn-lg" id="submitBtn">
                        <i class="fas fa-save"></i> Save Route
                    </button>
                    <a href="{{ route('routes.index') }}" class="btn btn-default btn-block">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </div>

        </div>
    </div>
</form>

@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<style>
    .stop-marker { cursor: pointer; }
    #map { border: 2px solid #ddd; border-radius: 5px; }
    .pac-container { z-index: 10000 !important; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}&libraries=places"></script>
<script>
let map;
let markers = [];
let stops = [];
let directionsService;
let directionsRenderer;
let geocoder;
let searchBox;
let companySalesIndex = 1;
let aiPredictionData = null;

$(document).ready(function() {
    $('.select2').select2();
    initMap();

    $('#estimated_days').on('change', function() {
        const days = parseInt($(this).val()) || 1;
        if (days > 1) {
            $('#accommodation_cost').val(3000 * (days - 1));
        } else {
            $('#accommodation_cost').val(0);
        }
        calculateCosts();
    });
});

function initMap() {
    const sriLanka = { lat: 7.8731, lng: 80.7718 };

    map = new google.maps.Map(document.getElementById('map'), {
        zoom: 8,
        center: sriLanka,
        mapTypeId: 'roadmap'
    });

    directionsService = new google.maps.DirectionsService();
    directionsRenderer = new google.maps.DirectionsRenderer({
        map: map,
        suppressMarkers: false
    });
    geocoder = new google.maps.Geocoder();

    // GOOGLE MAPS SEARCH BOX
    const input = document.getElementById('searchBox');
    searchBox = new google.maps.places.SearchBox(input);

    map.addListener('bounds_changed', function() {
        searchBox.setBounds(map.getBounds());
    });

    searchBox.addListener('places_changed', function() {
        const places = searchBox.getPlaces();

        if (places.length == 0) {
            return;
        }

        const place = places[0];

        if (!place.geometry) {
            alert('No details available for: ' + place.name);
            return;
        }

        addStop(place.geometry.location, place.formatted_address, place.name);
        map.setCenter(place.geometry.location);
        map.setZoom(15);
        input.value = '';
    });

    map.addListener('click', function(event) {
        addStop(event.latLng);
    });
}

function addStop(location, address = null, name = null) {
    const stopNumber = stops.length + 1;

    if (address && name) {
        const stop = {
            sequence: stopNumber,
            lat: location.lat(),
            lng: location.lng(),
            shop_name: name || 'Shop ' + stopNumber,
            address: address,
            stop_type: stopNumber === 1 ? 'warehouse' : 'shop'
        };

        stops.push(stop);
        addMarker(location, stopNumber);
        updateStopsList();
        recalculateRoute();
    } else {
        geocoder.geocode({ location: location }, function(results, status) {
            if (status === 'OK' && results[0]) {
                const address = results[0].formatted_address;

                const stop = {
                    sequence: stopNumber,
                    lat: location.lat(),
                    lng: location.lng(),
                    shop_name: 'Shop ' + stopNumber,
                    address: address,
                    stop_type: stopNumber === 1 ? 'warehouse' : 'shop'
                };

                stops.push(stop);
                addMarker(location, stopNumber);
                updateStopsList();
                recalculateRoute();
            }
        });
    }
}

function addMarker(location, number) {
    const marker = new google.maps.Marker({
        position: location,
        map: map,
        label: number.toString(),
        draggable: true
    });

    marker.addListener('dragend', function(event) {
        updateStopLocation(number - 1, event.latLng);
    });

    markers.push(marker);
}

function updateStopLocation(index, location) {
    stops[index].lat = location.lat();
    stops[index].lng = location.lng();

    geocoder.geocode({ location: location }, function(results, status) {
        if (status === 'OK' && results[0]) {
            stops[index].address = results[0].formatted_address;
            updateStopsList();
        }
    });

    recalculateRoute();
}

function updateStopsList() {
    const tbody = $('#stopsTableBody');
    const hiddenContainer = $('#stopsHiddenInputs');

    tbody.empty();
    hiddenContainer.empty();
    $('#stopCount').text(stops.length);

    if (stops.length === 0) {
        tbody.append(`
            <tr id="noStopsRow">
                <td colspan="5" class="text-center text-muted">
                    No stops added yet. Search address above or click on map.
                </td>
            </tr>
        `);
        return;
    }

    stops.forEach((stop, index) => {
        const row = `
            <tr>
                <td>
                    <span class="badge badge-primary">${stop.sequence}</span>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm"
                           value="${stop.shop_name}"
                           onchange="stops[${index}].shop_name = this.value">
                </td>
                <td><small>${stop.address}</small></td>
                <td>
                    <select class="form-control form-control-sm" onchange="stops[${index}].stop_type = this.value">
                        <option value="warehouse" ${stop.stop_type === 'warehouse' ? 'selected' : ''}>Warehouse</option>
                        <option value="shop" ${stop.stop_type === 'shop' ? 'selected' : ''}>Shop</option>
                        <option value="final" ${stop.stop_type === 'final' ? 'selected' : ''}>Final</option>
                    </select>
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-xs" onclick="removeStop(${index})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        tbody.append(row);

        hiddenContainer.append(`
            <input type="hidden" name="stops[${index}][shop_name]" value="${stop.shop_name}">
            <input type="hidden" name="stops[${index}][shop_address]" value="${stop.address}">
            <input type="hidden" name="stops[${index}][latitude]" value="${stop.lat}">
            <input type="hidden" name="stops[${index}][longitude]" value="${stop.lng}">
            <input type="hidden" name="stops[${index}][stop_type]" value="${stop.stop_type}">
        `);
    });
}

function removeStop(index) {
    stops.splice(index, 1);
    markers[index].setMap(null);
    markers.splice(index, 1);

    stops.forEach((stop, i) => {
        stop.sequence = i + 1;
        markers[i].setLabel((i + 1).toString());
    });

    updateStopsList();
    recalculateRoute();
}

function clearAllStops() {
    if (stops.length === 0) return;
    if (!confirm('Clear all stops?')) return;

    markers.forEach(marker => marker.setMap(null));
    markers = [];
    stops = [];
    updateStopsList();
    directionsRenderer.setDirections({ routes: [] });
    $('#estimated_distance_km').val(0);
    $('#totalDistance').text('0 km');
    calculateCosts();
}

function recalculateRoute() {
    if (stops.length < 2) {
        $('#estimated_distance_km').val(0);
        $('#totalDistance').text('0 km');
        calculateCosts();
        return;
    }

    const waypoints = stops.slice(1, -1).map(stop => ({
        location: new google.maps.LatLng(stop.lat, stop.lng),
        stopover: true
    }));

    const request = {
        origin: new google.maps.LatLng(stops[0].lat, stops[0].lng),
        destination: new google.maps.LatLng(stops[stops.length - 1].lat, stops[stops.length - 1].lng),
        waypoints: waypoints,
        travelMode: 'DRIVING'
    };

    directionsService.route(request, function(result, status) {
        if (status === 'OK') {
            directionsRenderer.setDirections(result);

            let totalDistance = 0;
            result.routes[0].legs.forEach(leg => {
                totalDistance += leg.distance.value;
            });

            const distanceKm = (totalDistance / 1000).toFixed(2);
            $('#estimated_distance_km').val(distanceKm);
            $('#totalDistance').text(distanceKm + ' km');
            calculateCosts();
        }
    });
}

function calculateCosts() {
    const distance = parseFloat($('#estimated_distance_km').val()) || 0;
    const fuelRate = parseFloat($('#fuel_rate').val()) || 350;
    const days = parseInt($('#estimated_days').val()) || 1;

    const vehicleSelect = $('#vehicle_id option:selected');
    const fuelEfficiency = parseFloat(vehicleSelect.data('efficiency')) || 10;

    const fuelNeeded = distance / fuelEfficiency;
    const fuelCost = fuelNeeded * fuelRate;
    $('#estimated_fuel_cost').val(fuelCost.toFixed(2));

    const mealCost = parseFloat($('#meal_cost').val()) || 0;
    const accommodationCost = parseFloat($('#accommodation_cost').val()) || 0;

    const totalCost = fuelCost + mealCost + accommodationCost;
    $('#total_cost').text(totalCost.toFixed(0));

    calculateCostPercentage();
}

function calculateTotalSales() {
    let total = 0;
    $('.company-sales-value').each(function() {
        total += parseFloat($(this).val()) || 0;
    });
    $('#totalSalesValue').text(total.toFixed(0));
    calculateCostPercentage();
}

function calculateCostPercentage() {
    const totalSales = parseFloat($('#totalSalesValue').text()) || 0;
    const totalCost = parseFloat($('#total_cost').text()) || 0;

    if (totalSales > 0) {
        const percentage = (totalCost / totalSales) * 100;
        $('#cost_percentage').text(percentage.toFixed(2));
    } else {
        $('#cost_percentage').text('0');
    }
}

function addCompanySales() {
    const index = companySalesIndex++;
    const html = `
        <div class="company-sales-row mb-3" data-index="${index}">
            <div class="row">
                <div class="col-md-5">
                    <div class="form-group">
                        <label>Company</label>
                        <select name="company_sales[${index}][company_id]" class="form-control" required>
                            <option value="">Select Company</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Sales Value (LKR)</label>
                        <input type="number" step="0.01" name="company_sales[${index}][sales_value]"
                               class="form-control company-sales-value" min="0" required
                               onchange="calculateTotalSales()">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Quantity</label>
                        <input type="number" name="company_sales[${index}][sales_qty]"
                               class="form-control" min="0" required>
                    </div>
                </div>
                <div class="col-md-1">
                    <label>&nbsp;</label>
                    <button type="button" class="btn btn-danger btn-block" onclick="removeCompanySales(${index})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    $('#companySalesContainer').append(html);
}

function removeCompanySales(index) {
    $(`.company-sales-row[data-index="${index}"]`).remove();
    calculateTotalSales();
}

// ============================================================================
// AI COST PREDICTION FUNCTION (NEW!)
// ============================================================================

async function getAIPrediction() {
    // Validation
    const distance = parseFloat($('#estimated_distance_km').val()) || 0;
    const totalStops = stops.length;
    const totalSales = parseFloat($('#totalSalesValue').text()) || 0;
    const vehicleSelect = $('#vehicle_id option:selected');
    const rawVehicleType = vehicleSelect.data('type');
    const vehicleType = mapVehicleTypeForAI(rawVehicleType);
    const routeDate = $('#route_date').val();

    if (distance === 0) {
        alert('Please plan route on map first!');
        return;
    }

    if (totalSales === 0) {
        alert('Please enter sales data first!');
        return;
    }

    if (!vehicleType) {
        alert('Please select a vehicle!');
        return;
    }

    // Get day of week
    const date = new Date(routeDate);
    const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    const dayOfWeek = days[date.getDay()];

    // Count unique companies
    const numCompanies = $('.company-sales-row').length;

    // Prepare AI request data
    const requestData = {
        total_distance_km: distance,
        total_stops: totalStops,
        num_companies: numCompanies,
        total_sales_value: totalSales,
        vehicle_type: vehicleType,
        day_of_week: dayOfWeek
    };

    // Show loading
    const btn = $('#aiPredictBtn');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Predicting...');

    try {
        // Call AI API
        const response = await fetch('/ai/predict-cost', {
            method: 'POST',
            headers: {
                 'Content-Type': 'application/json',
                 'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
             body: JSON.stringify(requestData)
        });

        const result = await response.json();

        if (result.success) {
            // Store prediction data
            aiPredictionData = result.prediction;

            // Calculate cost percentage
            const costPercentage = (result.prediction.cost / totalSales) * 100;
            const STANDARD_COST_PERCENTAGE = 0.60; // Standard threshold

            // Display main results
            $('#aiPredictedCost').text(result.prediction.cost.toLocaleString());
            $('#aiCostPercentage').text(costPercentage.toFixed(2));
            $('#aiConfidence').text(result.prediction.model_confidence.toFixed(1));
            $('#aiLowerBound').text(result.prediction.confidence_interval.lower.toLocaleString());
            $('#aiUpperBound').text(result.prediction.confidence_interval.upper.toLocaleString());

            // Calculate range cost percentages
            const lowerCostPct = (result.prediction.confidence_interval.lower / totalSales) * 100;
            const upperCostPct = (result.prediction.confidence_interval.upper / totalSales) * 100;

            // Determine delivery recommendation
            let recommendation = '';
            let recommendationClass = '';
            let suggestions = [];

            if (costPercentage <= STANDARD_COST_PERCENTAGE) {
                // GOOD - Below standard
                recommendation = '✅ GOOD TO DELIVER';
                recommendationClass = 'alert-success';
                suggestions.push(`Cost percentage (${costPercentage.toFixed(2)}%) is below standard (${STANDARD_COST_PERCENTAGE}%)`);
                suggestions.push('Profit margin is healthy');
                suggestions.push('Route is economically viable');
            } else if (costPercentage <= STANDARD_COST_PERCENTAGE * 1.5) {
                // WARNING - Slightly above
                recommendation = '⚠️ WARNING - MODERATE COST';
                recommendationClass = 'alert-warning';
                suggestions.push(`Cost percentage (${costPercentage.toFixed(2)}%) is above standard (${STANDARD_COST_PERCENTAGE}%)`);

                // Calculate required sales for 0.60%
                const requiredSales = result.prediction.cost / (STANDARD_COST_PERCENTAGE / 100);
                const additionalSales = requiredSales - totalSales;
                suggestions.push(`Need additional Rs. ${additionalSales.toLocaleString()} in sales to reach standard`);
                suggestions.push(`OR reduce route cost by Rs. ${(result.prediction.cost - (totalSales * STANDARD_COST_PERCENTAGE / 100)).toLocaleString()}`);
            } else {
                // DANGER - Way above
                recommendation = '🚫 HIGH RISK - NOT RECOMMENDED';
                recommendationClass = 'alert-danger';
                suggestions.push(`Cost percentage (${costPercentage.toFixed(2)}%) is significantly above standard (${STANDARD_COST_PERCENTAGE}%)`);

                // Calculate required sales
                const requiredSales = result.prediction.cost / (STANDARD_COST_PERCENTAGE / 100);
                suggestions.push(`Need Rs. ${requiredSales.toLocaleString()} in sales (currently: Rs. ${totalSales.toLocaleString()})`);
                suggestions.push(`Consider splitting route into multiple deliveries`);
                suggestions.push(`Or use more fuel-efficient vehicle`);
                suggestions.push(`Or optimize route to reduce distance`);
            }

            // Build recommendation HTML
            let recommendationHTML = `
                <div class="alert ${recommendationClass} mt-3">
                    <h5><strong>${recommendation}</strong></h5>
                    <hr>
                    <h6>Analysis:</h6>
                    <ul class="mb-0">
                        ${suggestions.map(s => `<li>${s}</li>`).join('')}
                    </ul>
                </div>

                <div class="card bg-light mt-2">
                    <div class="card-body p-2">
                        <small><strong>Cost Range:</strong></small><br>
                        <small>
                            Best case: Rs. ${result.prediction.confidence_interval.lower.toLocaleString()} (${lowerCostPct.toFixed(2)}%)<br>
                            Worst case: Rs. ${result.prediction.confidence_interval.upper.toLocaleString()} (${upperCostPct.toFixed(2)}%)
                        </small>
                    </div>
                </div>
            `;

            // Show result card with recommendation
            $('#aiPredictionResult').html(`
                <div class="alert alert-info">
                    <h5><i class="fas fa-robot"></i> AI Prediction</h5>
                    <h3 class="mb-2">Rs. ${result.prediction.cost.toLocaleString()}</h3>
                    <div class="row">
                        <div class="col-6">
                            <small>Cost %:</small><br>
                            <strong class="${costPercentage > STANDARD_COST_PERCENTAGE ? 'text-danger' : 'text-success'}">
                                ${costPercentage.toFixed(2)}%
                            </strong>
                            <small class="text-muted"> (Std: ${STANDARD_COST_PERCENTAGE}%)</small>
                        </div>
                        <div class="col-6">
                            <small>Confidence:</small><br>
                            <strong>${result.prediction.model_confidence.toFixed(1)}%</strong>
                        </div>
                    </div>
                </div>

                ${recommendationHTML}

                <div class="text-center mt-3">
                    <button type="button" class="btn btn-success btn-block" onclick="useAIPrediction()">
                        <i class="fas fa-check"></i> Use This Prediction
                    </button>
                </div>
            `).slideDown();

            // Success notification
            if (typeof toastr !== 'undefined') {
                toastr.success('AI prediction completed!', 'Success');
            }

        } else {
            alert('Prediction failed: ' + result.error);
        }
    } catch (error) {
        console.error('AI Prediction Error:', error);
        alert('Could not connect to AI API. Make sure Flask server is running on port 5000.');
    } finally {
        btn.prop('disabled', false).html('<i class="fas fa-brain"></i> Predict Cost with AI');
    }
}

function useAIPrediction() {
    if (!aiPredictionData) return;

    const aiCost = aiPredictionData.cost;
    const totalSales = parseFloat($('#totalSalesValue').text()) || 0;
    const costPercentage = (aiCost / totalSales) * 100;

    // Apply to manual cost field
    $('#total_cost').text(aiCost.toFixed(0));

    // Store in hidden fields for saving
    $('#ai_predicted_cost').val(aiCost);
    $('#ai_confidence_hidden').val(aiPredictionData.model_confidence);
    $('<input>').attr({
        type: 'hidden',
        name: 'ai_cost_percentage',
        value: costPercentage.toFixed(2)
    }).appendTo('#routeForm');

    // Update cost percentage display
    $('#cost_percentage').text(costPercentage.toFixed(2));

    // Success notification
    if (typeof toastr !== 'undefined') {
        toastr.success('AI prediction applied to route!', 'Applied');
    } else {
        alert('AI prediction applied to route cost!');
    }

    // Hide AI card
    $('#aiPredictionResult').slideUp();
}

function mapVehicleTypeForAI(uiType) {
    const map = {
        'van': 'Small Lorry',
        'Van': 'Small Lorry',
        'small lorry': 'Small Lorry',
        'Small Lorry': 'Small Lorry',
        'medium lorry': 'Medium Lorry',
        'Medium Lorry': 'Medium Lorry',
        'large lorry': 'Large Lorry',
        'Large Lorry': 'Large Lorry',
        'truck': 'Truck',
        'Truck': 'Truck'
    };
    return map[uiType] || 'Small Lorry';
}


function useAIPrediction() {
    if (!aiPredictionData) return;

    // Apply AI prediction to manual cost fields
    const aiCost = aiPredictionData.cost;

    // Set total cost
    $('#total_cost').text(aiCost.toFixed(0));

    // Store in hidden field
    $('#ai_predicted_cost').val(aiCost);
    $('#ai_confidence_hidden').val(aiPredictionData.model_confidence);

    // Show success message
    alert('AI prediction applied to route cost');


    // Optionally hide AI card
    $('#aiPredictionResult').slideUp();
}

// Form submission validation
$('#routeForm').on('submit', function(e) {
    if (stops.length < 2) {
        e.preventDefault();
        alert('Please add at least 2 stops to the route!');
        return false;
    }

    const totalSales = parseFloat($('#totalSalesValue').text()) || 0;
    if (totalSales === 0) {
        e.preventDefault();
        alert('Please enter sales data for at least one company!');
        return false;
    }

    $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
    return true;
});
</script>
@endpush
