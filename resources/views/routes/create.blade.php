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
                                <input type="date" name="route_date" class="form-control @error('route_date') is-invalid @enderror"
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
                                <select name="driver_id" class="form-control select2 @error('driver_id') is-invalid @enderror" required>
                                    <option value="">Select Driver</option>
                                    @foreach($drivers as $driver)
                                        <option value="{{ $driver->id }}" {{ old('driver_id') == $driver->id ? 'selected' : '' }}>
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

            <!-- COMPANY-WISE SALES ENTRY (NEW!) -->
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-line"></i> Company-wise Sales (Total for Route)</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> <strong>Enter total sales for each company on this route.</strong>
                        <br>These will be used to calculate cost allocation and profitability.
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

            <!-- Google Maps Route Builder with SEARCH -->
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-map-marked-alt"></i> Route Planning</h3>
                </div>
                <div class="card-body">
                    <!-- ADDRESS SEARCH BOX (NEW!) -->
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

            <!-- Stops List (NO SALES FIELDS HERE!) -->
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

        <!-- Right Column: Cost Estimation -->
        <div class="col-md-4">

            <!-- Estimated Costs Card -->
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-calculator"></i> Cost Estimation</h3>
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
                        <label><strong>TOTAL ESTIMATED COST</strong></label>
                        <h3 class="text-primary">LKR <span id="total_cost">0</span></h3>
                    </div>

                    <!-- COST PERCENTAGE (NEW!) -->
                    <div class="alert alert-success">
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

    // GOOGLE MAPS SEARCH BOX (NEW!)
    const input = document.getElementById('searchBox');
    searchBox = new google.maps.places.SearchBox(input);

    // Bias search to map viewport
    map.addListener('bounds_changed', function() {
        searchBox.setBounds(map.getBounds());
    });

    // Listen for search
    searchBox.addListener('places_changed', function() {
        const places = searchBox.getPlaces();

        if (places.length == 0) {
            return;
        }

        // Get first result
        const place = places[0];

        if (!place.geometry) {
            alert('No details available for: ' + place.name);
            return;
        }

        // Add as stop
        addStop(place.geometry.location, place.formatted_address, place.name);

        // Center map
        map.setCenter(place.geometry.location);
        map.setZoom(15);

        // Clear search box
        input.value = '';
    });

    // Click on map to add stop
    map.addListener('click', function(event) {
        addStop(event.latLng);
    });
}

function addStop(location, address = null, name = null) {
    const stopNumber = stops.length + 1;

    if (address && name) {
        // From search - address already available
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
        // From map click - need to geocode
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
        // Display row
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

        // Hidden inputs for form submission
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

    // Calculate cost percentage
    calculateCostPercentage();
}

function calculateTotalSales() {
    let total = 0;
    $('.company-sales-value').each(function() {
        total += parseFloat($(this).val()) || 0;
    });
    $('#totalSalesValue').text(total.toFixed(0));

    // Recalculate cost percentage
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
