@extends('layouts.admin')

@section('title', 'Vehicle Details')
@section('page-title', 'Vehicle Details')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
<li class="breadcrumb-item"><a href="{{ route('vehicles.index') }}">Vehicles</a></li>
<li class="breadcrumb-item active">{{ strtoupper($vehicle->registration_number) }}</li>
@endsection

@section('content')

<div class="row">
    <!-- Vehicle Profile -->
    <div class="col-md-4">
        <div class="card card-primary card-outline">
            <div class="card-body box-profile">
                <div class="text-center">
                    @php
                        $typeIcons = [
                            'lorry' => 'fas fa-truck fa-4x text-primary',
                            'truck' => 'fas fa-truck-moving fa-4x text-info',
                            'van' => 'fas fa-van-shuttle fa-4x text-success',
                            'mini_truck' => 'fas fa-truck-pickup fa-4x text-warning',
                            'pickup' => 'fas fa-truck-pickup fa-4x text-secondary',
                            'other' => 'fas fa-car fa-4x text-muted'
                        ];
                        $icon = $typeIcons[$vehicle->vehicle_type] ?? 'fas fa-car fa-4x';
                    @endphp
                    <i class="{{ $icon }} mb-3"></i>
                    <h3 class="profile-username text-center">{{ strtoupper($vehicle->registration_number) }}</h3>
                    <p class="text-muted text-center">{{ ucwords(str_replace('_', ' ', $vehicle->vehicle_type)) }}</p>
                </div>

                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>Company</b>
                        <span class="float-right">{{ $vehicle->company->name ?? '-' }}</span>
                    </li>
                    <li class="list-group-item">
                        <b>Status</b>
                        <span class="float-right">
                            @if($vehicle->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-danger">Inactive</span>
                            @endif
                        </span>
                    </li>
                    <li class="list-group-item">
                        <b>Condition</b>
                        <span class="float-right">
                            @php
                                $conditionBadges = [
                                    'excellent' => 'badge-success',
                                    'good' => 'badge-info',
                                    'fair' => 'badge-warning',
                                    'poor' => 'badge-danger'
                                ];
                                $badge = $conditionBadges[$vehicle->condition] ?? 'badge-secondary';
                            @endphp
                            <span class="badge {{ $badge }}">{{ ucfirst($vehicle->condition) }}</span>
                        </span>
                    </li>
                    <li class="list-group-item">
                        <b>Fuel Type</b>
                        <span class="float-right">
                            @php
                                $fuelBadges = [
                                    'diesel' => 'badge-dark',
                                    'petrol' => 'badge-warning',
                                    'electric' => 'badge-success',
                                    'hybrid' => 'badge-info'
                                ];
                                $fuelBadge = $fuelBadges[$vehicle->fuel_type] ?? 'badge-secondary';
                            @endphp
                            <span class="badge {{ $fuelBadge }}">{{ ucfirst($vehicle->fuel_type) }}</span>
                        </span>
                    </li>
                </ul>

                @can('update', $vehicle)
                <a href="{{ route('vehicles.edit', $vehicle) }}" class="btn btn-warning btn-block">
                    <i class="fas fa-edit"></i> Edit Vehicle
                </a>
                @endcan

                <a href="{{ route('vehicles.index') }}" class="btn btn-default btn-block">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    <!-- Vehicle Details -->
    <div class="col-md-8">
        <!-- Basic Information -->
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> Vehicle Information</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong><i class="fas fa-car mr-1"></i> Make & Model</strong>
                        <p class="text-muted">
                            @if($vehicle->make || $vehicle->model)
                                {{ $vehicle->make }} {{ $vehicle->model }}
                                @if($vehicle->year)
                                    ({{ $vehicle->year }})
                                @endif
                            @else
                                Not specified
                            @endif
                        </p>
                        <hr>

                        <strong><i class="fas fa-weight-hanging mr-1"></i> Weight Capacity</strong>
                        <p class="text-muted">
                            {{ $vehicle->capacity_weight ? number_format($vehicle->capacity_weight) . ' kg' : 'Not specified' }}
                        </p>
                        <hr>

                        <strong><i class="fas fa-cube mr-1"></i> Volume Capacity</strong>
                        <p class="text-muted">
                            {{ $vehicle->capacity_volume ? number_format($vehicle->capacity_volume) . ' m³' : 'Not specified' }}
                        </p>
                    </div>

                    <div class="col-md-6">
                        <strong><i class="fas fa-gas-pump mr-1"></i> Fuel Type</strong>
                        <p class="text-muted">{{ ucfirst($vehicle->fuel_type) }}</p>
                        <hr>

                        <strong><i class="fas fa-tachometer-alt mr-1"></i> Fuel Efficiency</strong>
                        <p class="text-muted">
                            {{ $vehicle->fuel_efficiency ? $vehicle->fuel_efficiency . ' km/L' : 'Not specified' }}
                        </p>
                        <hr>

                        <strong><i class="fas fa-tools mr-1"></i> Condition</strong>
                        <p class="text-muted">{{ ucfirst($vehicle->condition) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Maintenance & Service -->
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-wrench"></i> Maintenance & Service</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong><i class="fas fa-calendar-check mr-1"></i> Last Service Date</strong>
                        <p class="text-muted">
                            {{ $vehicle->last_service_date ? $vehicle->last_service_date->format('M d, Y') : 'Not recorded' }}
                        </p>
                        <hr>

                        <strong><i class="fas fa-calendar-alt mr-1"></i> Next Service Date</strong>
                        <p class="text-muted">
                            @if($vehicle->next_service_date)
                                {{ $vehicle->next_service_date->format('M d, Y') }}
                                @if($vehicle->next_service_date->isPast())
                                    <span class="badge badge-danger">Overdue!</span>
                                @elseif($vehicle->next_service_date->diffInDays(now()) <= 7)
                                    <span class="badge badge-warning">Due Soon</span>
                                @endif
                            @else
                                Not scheduled
                            @endif
                        </p>
                    </div>

                    <div class="col-md-6">
                        <strong><i class="fas fa-shield-alt mr-1"></i> Insurance Expiry</strong>
                        <p class="text-muted">
                            @if($vehicle->insurance_expiry)
                                {{ $vehicle->insurance_expiry->format('M d, Y') }}
                                @if($vehicle->insurance_expiry->isPast())
                                    <span class="badge badge-danger">Expired!</span>
                                @elseif($vehicle->insurance_expiry->diffInDays(now()) <= 30)
                                    <span class="badge badge-warning">Expiring Soon</span>
                                @endif
                            @else
                                Not specified
                            @endif
                        </p>
                        <hr>

                        <strong><i class="fas fa-id-card mr-1"></i> License Expiry</strong>
                        <p class="text-muted">
                            @if($vehicle->license_expiry)
                                {{ $vehicle->license_expiry->format('M d, Y') }}
                                @if($vehicle->license_expiry->isPast())
                                    <span class="badge badge-danger">Expired!</span>
                                @elseif($vehicle->license_expiry->diffInDays(now()) <= 30)
                                    <span class="badge badge-warning">Expiring Soon</span>
                                @endif
                            @else
                                Not specified
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expiry Alerts -->
        @if(
            ($vehicle->next_service_date && $vehicle->next_service_date->isPast()) ||
            ($vehicle->insurance_expiry && $vehicle->insurance_expiry->isPast()) ||
            ($vehicle->license_expiry && $vehicle->license_expiry->isPast())
        )
        <div class="alert alert-danger">
            <h5><i class="icon fas fa-ban"></i> Alert!</h5>
            <ul class="mb-0">
                @if($vehicle->next_service_date && $vehicle->next_service_date->isPast())
                    <li>Service is overdue! Last service: {{ $vehicle->last_service_date?->format('M d, Y') ?? 'Unknown' }}</li>
                @endif
                @if($vehicle->insurance_expiry && $vehicle->insurance_expiry->isPast())
                    <li>Insurance has expired! Expired on: {{ $vehicle->insurance_expiry->format('M d, Y') }}</li>
                @endif
                @if($vehicle->license_expiry && $vehicle->license_expiry->isPast())
                    <li>Vehicle license has expired! Expired on: {{ $vehicle->license_expiry->format('M d, Y') }}</li>
                @endif
            </ul>
        </div>
        @endif
    </div>
</div>

@endsection
