@extends('layouts.admin')

@section('title', 'Driver Details')
@section('page-title', 'Driver Details')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
<li class="breadcrumb-item"><a href="{{ route('drivers.index') }}">Drivers</a></li>
<li class="breadcrumb-item active">{{ $driver->name }}</li>
@endsection

@section('content')

<div class="row">
    <!-- Driver Profile -->
    <div class="col-md-4">
        <div class="card card-primary card-outline">
            <div class="card-body box-profile">
                <div class="text-center">
                    <i class="fas fa-user-tie fa-4x text-primary mb-3"></i>
                    <h3 class="profile-username text-center">{{ $driver->name }}</h3>
                    <p class="text-muted text-center">{{ $driver->company->name ?? 'No Company' }}</p>
                </div>

                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>License Number</b>
                        <span class="float-right">
                            <span class="badge badge-secondary">{{ $driver->license_number }}</span>
                        </span>
                    </li>
                    <li class="list-group-item">
                        <b>License Type</b>
                        <span class="float-right">
                            @php
                                $licenseBadges = [
                                    'car' => 'badge-info',
                                    'van' => 'badge-primary',
                                    'lorry' => 'badge-warning',
                                    'heavy_vehicle' => 'badge-danger',
                                    'all' => 'badge-success'
                                ];
                                $badge = $licenseBadges[$driver->license_type] ?? 'badge-secondary';
                            @endphp
                            <span class="badge {{ $badge }}">{{ ucwords(str_replace('_', ' ', $driver->license_type)) }}</span>
                        </span>
                    </li>
                    <li class="list-group-item">
                        <b>Status</b>
                        <span class="float-right">
                            @if($driver->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-danger">Inactive</span>
                            @endif
                        </span>
                    </li>
                    @if($driver->experience_years)
                    <li class="list-group-item">
                        <b>Experience</b>
                        <span class="float-right">
                            <span class="badge badge-info">{{ $driver->experience_years }} {{ $driver->experience_years == 1 ? 'year' : 'years' }}</span>
                        </span>
                    </li>
                    @endif
                </ul>

                @can('update', $driver)
                <a href="{{ route('drivers.edit', $driver) }}" class="btn btn-warning btn-block">
                    <i class="fas fa-edit"></i> Edit Driver
                </a>
                @endcan

                <a href="{{ route('drivers.index') }}" class="btn btn-default btn-block">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    <!-- Driver Details -->
    <div class="col-md-8">
        <!-- Contact Information -->
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-address-card"></i> Contact Information</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong><i class="fas fa-phone mr-1"></i> Phone</strong>
                        <p class="text-muted">
                            @if($driver->phone)
                                <a href="tel:{{ $driver->phone }}">{{ $driver->phone }}</a>
                            @else
                                Not specified
                            @endif
                        </p>
                        <hr>

                        <strong><i class="fas fa-envelope mr-1"></i> Email</strong>
                        <p class="text-muted">
                            @if($driver->email)
                                <a href="mailto:{{ $driver->email }}">{{ $driver->email }}</a>
                            @else
                                Not specified
                            @endif
                        </p>
                    </div>

                    <div class="col-md-6">
                        <strong><i class="fas fa-map-marker-alt mr-1"></i> Address</strong>
                        <p class="text-muted">{{ $driver->address ?? 'Not specified' }}</p>
                        <hr>

                        <strong><i class="fas fa-birthday-cake mr-1"></i> Date of Birth</strong>
                        <p class="text-muted">
                            @if($driver->date_of_birth)
                                {{ $driver->date_of_birth->format('M d, Y') }}
                                <br>
                                <small>(Age: {{ $driver->date_of_birth->age }} years)</small>
                            @else
                                Not specified
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- License Information -->
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-id-card"></i> License Information</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong><i class="fas fa-id-badge mr-1"></i> License Number</strong>
                        <p class="text-muted">{{ $driver->license_number }}</p>
                        <hr>

                        <strong><i class="fas fa-car mr-1"></i> License Type</strong>
                        <p class="text-muted">{{ ucwords(str_replace('_', ' ', $driver->license_type)) }}</p>
                    </div>

                    <div class="col-md-6">
                        <strong><i class="fas fa-calendar-alt mr-1"></i> License Expiry</strong>
                        <p class="text-muted">
                            @if($driver->license_expiry)
                                {{ $driver->license_expiry->format('M d, Y') }}
                                @if($driver->license_expiry->isPast())
                                    <span class="badge badge-danger">Expired!</span>
                                @elseif($driver->license_expiry->diffInDays(now()) <= 30)
                                    <span class="badge badge-warning">Expiring Soon ({{ $driver->license_expiry->diffInDays(now()) }} days)</span>
                                @else
                                    <span class="badge badge-success">Valid</span>
                                @endif
                            @else
                                Not specified
                            @endif
                        </p>
                        <hr>

                        <strong><i class="fas fa-briefcase mr-1"></i> Experience</strong>
                        <p class="text-muted">
                            {{ $driver->experience_years ? $driver->experience_years . ' ' . ($driver->experience_years == 1 ? 'year' : 'years') : 'Not specified' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- License Expiry Alert -->
        @if($driver->license_expiry && $driver->license_expiry->isPast())
        <div class="alert alert-danger">
            <h5><i class="icon fas fa-ban"></i> Alert!</h5>
            <p class="mb-0">
                This driver's license has expired on {{ $driver->license_expiry->format('M d, Y') }}.
                Please update the license immediately!
            </p>
        </div>
        @elseif($driver->license_expiry && $driver->license_expiry->diffInDays(now()) <= 30)
        <div class="alert alert-warning">
            <h5><i class="icon fas fa-exclamation-triangle"></i> Warning!</h5>
            <p class="mb-0">
                This driver's license will expire in {{ $driver->license_expiry->diffInDays(now()) }} days
                ({{ $driver->license_expiry->format('M d, Y') }}).
                Please renew soon!
            </p>
        </div>
        @endif

        <!-- Additional Information -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> Additional Information</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong><i class="fas fa-building mr-1"></i> Company</strong>
                        <p class="text-muted">{{ $driver->company->name ?? 'Not assigned' }}</p>
                    </div>

                    <div class="col-md-6">
                        <strong><i class="fas fa-calendar mr-1"></i> Added On</strong>
                        <p class="text-muted">{{ $driver->created_at->format('M d, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
