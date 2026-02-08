@extends('layouts.admin')

@section('title', 'Vehicle Usage Report')
@section('page-title', 'Vehicle Usage Statistics')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
<li class="breadcrumb-item"><a href="{{ route('routes.reports.dashboard') }}">Reports</a></li>
<li class="breadcrumb-item active">Vehicle Usage</li>
@endsection

@section('content')

<div class="row">
    <div class="col-12">

        <!-- Filters -->
        <div class="card card-primary collapsed-card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-filter"></i> Filters</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('routes.reports.vehicleUsage') }}" method="GET">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Date From</label>
                                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Date To</label>
                                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-search"></i> Apply
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Report Table -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-truck"></i> Usage by Vehicle</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>Vehicle Number</th>
                            <th>Type</th>
                            <th>Total Routes</th>
                            <th>Total Distance (km)</th>
                            <th>Avg Distance/Route</th>
                            <th>Total Fuel Cost (LKR)</th>
                            <th>Avg Cost/km</th>
                            <th>Efficiency</th>
                            <th>Utilization</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vehicleUsage as $vehicle)
                        @php
                            $expectedEfficiency = 10; // Baseline
                            $actualEfficiency = $vehicle->avg_fuel_cost_per_km > 0 ?
                                ($expectedEfficiency / $vehicle->avg_fuel_cost_per_km) * 100 : 0;
                            $maxRoutes = 30; // Max routes per month
                            $utilization = min(($vehicle->total_routes / $maxRoutes) * 100, 100);
                        @endphp
                        <tr>
                            <td><strong>{{ $vehicle->vehicle_number }}</strong></td>
                            <td>
                                <span class="badge badge-secondary">{{ $vehicle->vehicle_type }}</span>
                            </td>
                            <td>{{ $vehicle->total_routes }}</td>
                            <td>{{ number_format($vehicle->total_distance, 0) }} km</td>
                            <td>{{ number_format($vehicle->avg_distance_per_route, 0) }} km</td>
                            <td>Rs. {{ number_format($vehicle->total_fuel_cost, 0) }}</td>
                            <td>Rs. {{ number_format($vehicle->avg_fuel_cost_per_km, 2) }}</td>
                            <td>
                                <span class="badge {{ $actualEfficiency > 95 ? 'badge-success' : ($actualEfficiency > 85 ? 'badge-warning' : 'badge-danger') }}">
                                    {{ number_format($actualEfficiency, 0) }}%
                                </span>
                            </td>
                            <td>
                                <div class="progress" style="height: 25px;">
                                    <div class="progress-bar {{ $utilization > 70 ? 'bg-success' : ($utilization > 50 ? 'bg-warning' : 'bg-danger') }}"
                                         style="width: {{ $utilization }}%">
                                        {{ number_format($utilization, 0) }}%
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fas fa-info-circle fa-2x mb-2"></i>
                                <p>No vehicle usage data available</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

@endsection
