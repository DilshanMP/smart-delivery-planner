@extends('layouts.admin')

@section('title', 'Route Details')
@section('page-title', 'Route Details')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
<li class="breadcrumb-item"><a href="{{ route('routes.index') }}">Routes</a></li>
<li class="breadcrumb-item active">{{ $route->route_code }}</li>
@endsection

@section('content')

<div class="row">
    <!-- Left Column -->
    <div class="col-md-8">

        <!-- Route Info Card -->
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-route"></i> {{ $route->route_code }}</h3>
                <div class="card-tools">
                    @php
                        $statusBadges = [
                            'planned' => 'badge-warning',
                            'in_progress' => 'badge-info',
                            'completed' => 'badge-success',
                            'cancelled' => 'badge-danger'
                        ];
                        $badge = $statusBadges[$route->status] ?? 'badge-secondary';
                    @endphp
                    <span class="badge {{ $badge }} badge-lg">
                        {{ ucfirst(str_replace('_', ' ', $route->status)) }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-5">Route Date:</dt>
                            <dd class="col-sm-7">{{ $route->route_date->format('M d, Y') }}</dd>

                            <dt class="col-sm-5">Company:</dt>
                            <dd class="col-sm-7">
                                <strong>{{ $route->company->name ?? '-' }}</strong>
                            </dd>

                            <dt class="col-sm-5">Driver:</dt>
                            <dd class="col-sm-7">
                                <i class="fas fa-user-tie"></i> {{ $route->driver->name ?? '-' }}
                            </dd>

                            <dt class="col-sm-5">Vehicle:</dt>
                            <dd class="col-sm-7">
                                <i class="fas fa-truck"></i> {{ $route->vehicle->registration_number ?? '-' }}
                            </dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-5">Delivery Type:</dt>
                            <dd class="col-sm-7">
                                @if($route->delivery_type == 'own')
                                    <span class="badge badge-success">Own Delivery</span>
                                @else
                                    <span class="badge badge-info">Outside Delivery</span>
                                @endif
                            </dd>

                            <dt class="col-sm-5">Total Stops:</dt>
                            <dd class="col-sm-7">
                                <span class="badge badge-secondary">{{ $route->stops->count() }}</span>
                            </dd>

                            <dt class="col-sm-5">Start Time:</dt>
                            <dd class="col-sm-7">{{ $route->start_time ?? '-' }}</dd>

                            <dt class="col-sm-5">End Time:</dt>
                            <dd class="col-sm-7">{{ $route->end_time ?? '-' }}</dd>
                        </dl>
                    </div>
                </div>

                @if($route->notes)
                <div class="alert alert-info">
                    <strong><i class="fas fa-info-circle"></i> Notes:</strong><br>
                    {{ $route->notes }}
                </div>
                @endif
            </div>
            <div class="card-footer">
                @can('update', $route)
                    @if($route->status == 'planned')
                        <form action="{{ route('routes.actual.start', $route) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-info">
                                <i class="fas fa-play"></i> Start Route
                            </button>
                        </form>
                    @endif

                    @if(in_array($route->status, ['planned', 'in_progress']))
                        <a href="{{ route('routes.actual.complete', $route) }}" class="btn btn-success">
                            <i class="fas fa-check"></i> Complete Route
                        </a>
                        <a href="{{ route('routes.edit', $route) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    @endif
                @endcan

                <a href="{{ route('routes.index') }}" class="btn btn-default">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>

        <!-- Google Maps Visualization -->
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-map-marked-alt"></i> Route Map</h3>
            </div>
            <div class="card-body">
                <div id="map" style="height: 500px; width: 100%;"></div>
            </div>
        </div>

        <!-- Stops List -->
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-map-pin"></i> Route Stops ({{ $route->stops->count() }})</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th width="5%">#</th>
                            <th width="20%">Shop Name</th>
                            <th width="30%">Address</th>
                            <th width="15%">Sales Company</th>
                            <th width="12%" class="text-right">Sales Value</th>
                            <th width="8%" class="text-right">Qty</th>
                            <th width="10%">Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($route->stops->sortBy('stop_sequence') as $stop)
                        <tr>
                            <td>
                                <span class="badge badge-primary">{{ $stop->stop_sequence }}</span>
                            </td>
                            <td><strong>{{ $stop->shop_name }}</strong></td>
                            <td>{{ $stop->shop_address }}</td>
                            <td>
                                @if($stop->salesCompany)
                                    <span class="badge badge-info">{{ $stop->salesCompany->name }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-right">
                                @if($stop->sales_value)
                                    LKR {{ number_format($stop->sales_value, 2) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-right">{{ $stop->sales_qty ?? '-' }}</td>
                            <td>
                                @php
                                    $typeBadges = [
                                        'warehouse' => 'badge-warning',
                                        'shop' => 'badge-success',
                                        'final' => 'badge-danger'
                                    ];
                                    $typeBadge = $typeBadges[$stop->stop_type] ?? 'badge-secondary';
                                @endphp
                                <span class="badge {{ $typeBadge }}">
                                    {{ ucfirst($stop->stop_type) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Company-wise Sales Allocation -->
        @if($route->companyAllocations->count() > 0)
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-building"></i> Company-wise Sales & Cost Allocation</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Company</th>
                            <th class="text-right">Sales Value</th>
                            <th class="text-right">Stops</th>
                            <th class="text-right">Allocated Cost</th>
                            <th class="text-right">Profit</th>
                            <th class="text-right">Margin %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($route->companyAllocations as $allocation)
                        <tr>
                            <td><strong>{{ $allocation->company->name }}</strong></td>
                            <td class="text-right">LKR {{ number_format($allocation->total_sales_value, 2) }}</td>
                            <td class="text-right">{{ $allocation->number_of_stops }}</td>
                            <td class="text-right">LKR {{ number_format($allocation->allocated_cost, 2) }}</td>
                            <td class="text-right">
                                <strong class="{{ $allocation->profit >= 0 ? 'text-success' : 'text-danger' }}">
                                    LKR {{ number_format($allocation->profit, 2) }}
                                </strong>
                            </td>
                            <td class="text-right">
                                <span class="badge {{ $allocation->profit >= 0 ? 'badge-success' : 'badge-danger' }}">
                                    {{ number_format($allocation->profit_margin_percentage, 1) }}%
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

    </div>

    <!-- Right Column: Cost Summary -->
    <div class="col-md-4">

        <!-- Estimated Costs -->
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-calculator"></i> Estimated Costs</h3>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-7">Distance:</dt>
                    <dd class="col-sm-5 text-right">
                        <strong>{{ number_format($route->estimated_distance_km, 1) }} km</strong>
                    </dd>

                    <dt class="col-sm-7">Days:</dt>
                    <dd class="col-sm-5 text-right">{{ $route->estimated_days }}</dd>

                    <dt class="col-sm-7">Fuel Cost:</dt>
                    <dd class="col-sm-5 text-right">LKR {{ number_format($route->estimated_fuel_cost, 0) }}</dd>

                    <dt class="col-sm-7">Meal Cost:</dt>
                    <dd class="col-sm-5 text-right">LKR {{ number_format($route->estimated_meal_cost, 0) }}</dd>

                    <dt class="col-sm-7">Accommodation:</dt>
                    <dd class="col-sm-5 text-right">LKR {{ number_format($route->estimated_accommodation_cost, 0) }}</dd>
                </dl>

                <hr>

                <dl class="row">
                    <dt class="col-sm-7"><strong>TOTAL ESTIMATED:</strong></dt>
                    <dd class="col-sm-5 text-right">
                        <h4 class="text-warning">LKR {{ number_format($route->estimated_total_cost, 0) }}</h4>
                    </dd>
                </dl>
            </div>
        </div>

        <!-- Actual Costs (if completed) -->
        @if($route->status == 'completed' && $route->actual_distance_km)
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-check-circle"></i> Actual Costs</h3>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-7">Actual Distance:</dt>
                    <dd class="col-sm-5 text-right">
                        <strong>{{ number_format($route->actual_distance_km, 1) }} km</strong>
                    </dd>

                    <dt class="col-sm-7">Start KM:</dt>
                    <dd class="col-sm-5 text-right">{{ number_format($route->actual_start_km, 0) }}</dd>

                    <dt class="col-sm-7">End KM:</dt>
                    <dd class="col-sm-5 text-right">{{ number_format($route->actual_end_km, 0) }}</dd>

                    <dt class="col-sm-7">Fuel Cost:</dt>
                    <dd class="col-sm-5 text-right">LKR {{ number_format($route->actual_fuel_cost, 0) }}</dd>

                    <dt class="col-sm-7">Other Costs:</dt>
                    <dd class="col-sm-5 text-right">LKR {{ number_format($route->actual_other_costs, 0) }}</dd>
                </dl>

                <hr>

                <dl class="row">
                    <dt class="col-sm-7"><strong>TOTAL ACTUAL:</strong></dt>
                    <dd class="col-sm-5 text-right">
                        <h4 class="text-success">LKR {{ number_format($route->actual_total_cost, 0) }}</h4>
                    </dd>
                </dl>
            </div>
        </div>

        <!-- Variance Analysis -->
        <div class="card {{ $route->cost_variance > 0 ? 'card-danger' : 'card-success' }}">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-line"></i> Variance Analysis</h3>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-7">KM Variance:</dt>
                    <dd class="col-sm-5 text-right">
                        <span class="{{ $route->km_variance > 0 ? 'text-danger' : 'text-success' }}">
                            {{ $route->km_variance > 0 ? '+' : '' }}{{ number_format($route->km_variance, 1) }} km
                        </span>
                    </dd>

                    <dt class="col-sm-7">Cost Variance:</dt>
                    <dd class="col-sm-5 text-right">
                        <span class="{{ $route->cost_variance > 0 ? 'text-danger' : 'text-success' }}">
                            {{ $route->cost_variance > 0 ? '+' : '' }}LKR {{ number_format($route->cost_variance, 0) }}
                        </span>
                    </dd>

                    <dt class="col-sm-7">Variance %:</dt>
                    <dd class="col-sm-5 text-right">
                        <span class="badge {{ $route->cost_variance > 0 ? 'badge-danger' : 'badge-success' }}">
                            {{ $route->cost_variance > 0 ? '+' : '' }}{{ number_format($route->cost_variance_percentage, 1) }}%
                        </span>
                    </dd>
                </dl>

                @if($route->cost_variance > 0)
                <div class="alert alert-warning mt-2">
                    <i class="fas fa-exclamation-triangle"></i> Route over budget
                </div>
                @else
                <div class="alert alert-success mt-2">
                    <i class="fas fa-check-circle"></i> Route within budget
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Profitability Summary -->
        @if($route->status == 'completed')
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-dollar-sign"></i> Profitability</h3>
            </div>
            <div class="card-body">
                @php
                    $prof = $profitability;
                @endphp
                <dl class="row">
                    <dt class="col-sm-7">Total Sales:</dt>
                    <dd class="col-sm-5 text-right">LKR {{ number_format($prof['total_sales'], 0) }}</dd>

                    <dt class="col-sm-7">Returns:</dt>
                    <dd class="col-sm-5 text-right">LKR {{ number_format($prof['return_sales'], 0) }}</dd>

                    <dt class="col-sm-7"><strong>Net Sales:</strong></dt>
                    <dd class="col-sm-5 text-right">
                        <strong>LKR {{ number_format($prof['net_sales'], 0) }}</strong>
                    </dd>

                    <dt class="col-sm-7">Total Cost:</dt>
                    <dd class="col-sm-5 text-right">LKR {{ number_format($prof['total_cost'], 0) }}</dd>
                </dl>

                <hr>

                <dl class="row">
                    <dt class="col-sm-7"><strong>PROFIT:</strong></dt>
                    <dd class="col-sm-5 text-right">
                        <h4 class="{{ $prof['profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                            LKR {{ number_format($prof['profit'], 0) }}
                        </h4>
                    </dd>

                    <dt class="col-sm-7">Profit Margin:</dt>
                    <dd class="col-sm-5 text-right">
                        <span class="badge {{ $prof['profit'] >= 0 ? 'badge-success' : 'badge-danger' }} badge-lg">
                            {{ number_format($prof['profit_margin'], 1) }}%
                        </span>
                    </dd>
                </dl>
            </div>
        </div>
        @endif

    </div>
</div>

@endsection

@push('scripts')
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}"></script>
<script>
let map;
let directionsService;
let directionsRenderer;

$(document).ready(function() {
    initMap();
    displayRoute();
});

function initMap() {
    const sriLanka = { lat: 7.8731, lng: 80.7718 };

    map = new google.maps.Map(document.getElementById('map'), {
        zoom: 8,
        center: sriLanka
    });

    directionsService = new google.maps.DirectionsService();
    directionsRenderer = new google.maps.DirectionsRenderer({
        map: map
    });
}

function displayRoute() {
    const stops = @json($route->stops->sortBy('stop_sequence')->values());

    if (stops.length < 2) return;

    const waypoints = stops.slice(1, -1).map(stop => ({
        location: new google.maps.LatLng(parseFloat(stop.latitude), parseFloat(stop.longitude)),
        stopover: true
    }));

    const request = {
        origin: new google.maps.LatLng(parseFloat(stops[0].latitude), parseFloat(stops[0].longitude)),
        destination: new google.maps.LatLng(
            parseFloat(stops[stops.length - 1].latitude),
            parseFloat(stops[stops.length - 1].longitude)
        ),
        waypoints: waypoints,
        travelMode: 'DRIVING'
    };

    directionsService.route(request, function(result, status) {
        if (status === 'OK') {
            directionsRenderer.setDirections(result);
        }
    });
}
</script>
@endpush
