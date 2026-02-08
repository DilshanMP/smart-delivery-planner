@extends('layouts.admin')

@section('title', 'Driver Performance Report')
@section('page-title', 'Driver Performance Analysis')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
<li class="breadcrumb-item"><a href="{{ route('routes.reports.dashboard') }}">Reports</a></li>
<li class="breadcrumb-item active">Driver Performance</li>
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
                <form action="{{ route('routes.reports.driverPerformance') }}" method="GET">
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
                <h3 class="card-title"><i class="fas fa-user-tie"></i> Performance by Driver</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>Driver Name</th>
                            <th>Total Routes</th>
                            <th>Total Distance (km)</th>
                            <th>Avg Cost Variance</th>
                            <th>Over Budget Count</th>
                            <th>Success Rate</th>
                            <th>Performance Rating</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($driverPerformance as $driver)
                        @php
                            $totalRoutes = $driver->total_routes ?? 0;
                            $overBudgetCount = $driver->over_budget_count ?? 0;
                            $successRate = $totalRoutes > 0 ?
                                (($totalRoutes - $overBudgetCount) / $totalRoutes) * 100 : 0;
                            $avgVariance = $driver->avg_cost_variance ?? 0;
                        @endphp
                        <tr>
                            <td><strong>{{ $driver->driver_name }}</strong></td>
                            <td>{{ $totalRoutes }}</td>
                            <td>{{ number_format($driver->total_distance ?? 0, 0) }} km</td>
                            <td>
                                <span class="badge {{ $avgVariance > 0 ? 'badge-danger' : 'badge-success' }}">
                                    {{ $avgVariance > 0 ? '+' : '' }}{{ number_format($avgVariance, 1) }}%
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-warning">{{ $overBudgetCount }}</span>
                            </td>
                            <td>
                                <div class="progress" style="height: 25px;">
                                    <div class="progress-bar {{ $successRate > 80 ? 'bg-success' : ($successRate > 60 ? 'bg-warning' : 'bg-danger') }}"
                                         style="width: {{ $successRate }}%">
                                        {{ number_format($successRate, 0) }}%
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($avgVariance < 5 && $successRate > 80)
                                    <span class="badge badge-success badge-lg">
                                        <i class="fas fa-star"></i> Excellent
                                    </span>
                                @elseif($avgVariance < 10 && $successRate > 60)
                                    <span class="badge badge-info badge-lg">
                                        <i class="fas fa-check"></i> Good
                                    </span>
                                @elseif($avgVariance < 15)
                                    <span class="badge badge-warning badge-lg">
                                        <i class="fas fa-exclamation-triangle"></i> Average
                                    </span>
                                @else
                                    <span class="badge badge-danger badge-lg">
                                        <i class="fas fa-times"></i> Needs Improvement
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-info-circle fa-2x mb-2"></i>
                                <p>No driver performance data available</p>
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
