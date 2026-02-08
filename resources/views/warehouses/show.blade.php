@extends('layouts.admin')

@section('title', 'Warehouse Details')
@section('page-title', 'Warehouse Details')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
<li class="breadcrumb-item"><a href="{{ route('warehouses.index') }}">Warehouses</a></li>
<li class="breadcrumb-item active">{{ $warehouse->name }}</li>
@endsection

@section('content')

<div class="row">
    <!-- Warehouse Information -->
    <div class="col-md-4">
        <div class="card card-primary card-outline">
            <div class="card-body box-profile">
                <div class="text-center">
                    <i class="fas fa-warehouse fa-4x text-info mb-3"></i>
                    <h3 class="profile-username text-center">{{ $warehouse->name }}</h3>
                    <p class="text-muted text-center">{{ $warehouse->code }}</p>
                </div>

                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>Company</b>
                        <span class="float-right">{{ $warehouse->company->name ?? '-' }}</span>
                    </li>
                    <li class="list-group-item">
                        <b>Status</b>
                        <span class="float-right">
                            @if($warehouse->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-danger">Inactive</span>
                            @endif
                        </span>
                    </li>
                    @if($warehouse->capacity)
                    <li class="list-group-item">
                        <b>Capacity</b>
                        <span class="float-right">{{ number_format($warehouse->capacity) }} units</span>
                    </li>
                    @endif
                    @if($warehouse->current_stock)
                    <li class="list-group-item">
                        <b>Current Stock</b>
                        <span class="float-right">
                            {{ number_format($warehouse->current_stock) }} units
                            @if($warehouse->capacity)
                                <br>
                                <small class="text-muted">
                                    ({{ round(($warehouse->current_stock / $warehouse->capacity) * 100) }}% full)
                                </small>
                            @endif
                        </span>
                    </li>
                    @endif
                </ul>

                @can('update', $warehouse)
                <a href="{{ route('warehouses.edit', $warehouse) }}" class="btn btn-warning btn-block">
                    <i class="fas fa-edit"></i> Edit Warehouse
                </a>
                @endcan

                <a href="{{ route('warehouses.index') }}" class="btn btn-default btn-block">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>

        <!-- Capacity Progress -->
        @if($warehouse->capacity && $warehouse->current_stock)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Stock Level</h3>
            </div>
            <div class="card-body">
                @php
                    $percentage = round(($warehouse->current_stock / $warehouse->capacity) * 100);
                    $progressColor = $percentage > 90 ? 'danger' : ($percentage > 70 ? 'warning' : 'success');
                @endphp
                <div class="progress progress-sm">
                    <div class="progress-bar bg-{{ $progressColor }}"
                         role="progressbar"
                         style="width: {{ $percentage }}%"
                         aria-valuenow="{{ $percentage }}"
                         aria-valuemin="0"
                         aria-valuemax="100">
                    </div>
                </div>
                <p class="mt-2 mb-0">
                    {{ number_format($warehouse->current_stock) }} / {{ number_format($warehouse->capacity) }} units
                    <span class="float-right">{{ $percentage }}%</span>
                </p>
            </div>
        </div>
        @endif
    </div>

    <!-- Contact & Location Information -->
    <div class="col-md-8">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> Contact Information</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong><i class="fas fa-map-marker-alt mr-1"></i> Address</strong>
                        <p class="text-muted">{{ $warehouse->address }}</p>
                        <hr>

                        <strong><i class="fas fa-city mr-1"></i> City</strong>
                        <p class="text-muted">{{ $warehouse->city ?? 'Not specified' }}</p>
                        <hr>

                        <strong><i class="fas fa-globe mr-1"></i> GPS Coordinates</strong>
                        <p class="text-muted">
                            Latitude: {{ $warehouse->latitude }}<br>
                            Longitude: {{ $warehouse->longitude }}
                        </p>
                    </div>

                    <div class="col-md-6">
                        <strong><i class="fas fa-user mr-1"></i> Contact Person</strong>
                        <p class="text-muted">{{ $warehouse->contact_person ?? 'Not specified' }}</p>
                        <hr>

                        <strong><i class="fas fa-phone mr-1"></i> Phone</strong>
                        <p class="text-muted">
                            @if($warehouse->phone)
                                <a href="tel:{{ $warehouse->phone }}">{{ $warehouse->phone }}</a>
                            @else
                                Not specified
                            @endif
                        </p>
                        <hr>

                        <strong><i class="fas fa-calendar mr-1"></i> Created</strong>
                        <p class="text-muted">{{ $warehouse->created_at->format('M d, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Map -->
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-map"></i> Warehouse Location</h3>
                <div class="card-tools">
                    <a href="https://www.google.com/maps?q={{ $warehouse->latitude }},{{ $warehouse->longitude }}"
                       target="_blank"
                       class="btn btn-tool">
                        <i class="fas fa-external-link-alt"></i> Open in Google Maps
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div id="map" style="height: 400px; width: 100%;"></div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<!-- Leaflet Map Library -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<script>
// Initialize map centered on warehouse location
var map = L.map('map').setView([{{ $warehouse->latitude }}, {{ $warehouse->longitude }}], 15);

// Add OpenStreetMap tile layer
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
}).addTo(map);

// Add marker at warehouse location
var marker = L.marker([{{ $warehouse->latitude }}, {{ $warehouse->longitude }}]).addTo(map);

// Add popup with warehouse info
marker.bindPopup("<b>{{ $warehouse->name }}</b><br>{{ $warehouse->address }}").openPopup();
</script>
@endpush
