@extends('layouts.admin')

@section('title', 'Estimated vs Actual Report')
@section('page-title', 'Estimated vs Actual Variance Analysis')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
<li class="breadcrumb-item"><a href="{{ route('routes.reports.dashboard') }}">Reports</a></li>
<li class="breadcrumb-item active">Estimated vs Actual</li>
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
                <form action="{{ route('routes.reports.estimatedVsActual') }}" method="GET">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Company</label>
                                <select name="company_id" class="form-control">
                                    <option value="">All Companies</option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                                            {{ $company->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Date From</label>
                                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Date To</label>
                                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
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
                <h3 class="card-title"><i class="fas fa-balance-scale"></i> Variance Analysis</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-bordered table-hover table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th>Route Code</th>
                            <th>Date</th>
                            <th>Est. Dist (km)</th>
                            <th>Act. Dist (km)</th>
                            <th>KM Variance</th>
                            <th>Est. Cost (LKR)</th>
                            <th>Act. Cost (LKR)</th>
                            <th>Cost Variance</th>
                            <th>Var %</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($routes as $route)
                        @php
                            $kmVar = ($route->actual_distance_km ?? 0) - $route->estimated_distance_km;
                            $costVar = ($route->actual_total_cost ?? 0) - $route->estimated_total_cost;
                        @endphp
                        <tr>
                            <td><strong>{{ $route->route_code }}</strong></td>
                            <td>{{ $route->route_date ? $route->route_date->format('d M Y') : 'N/A' }}</td>
                            <td>{{ number_format($route->estimated_distance_km, 1) }}</td>
                            <td>{{ number_format($route->actual_distance_km ?? 0, 1) }}</td>
                            <td>
                                <span class="badge {{ $kmVar > 0 ? 'badge-danger' : 'badge-success' }}">
                                    {{ $kmVar > 0 ? '+' : '' }}{{ number_format($kmVar, 1) }}
                                </span>
                            </td>
                            <td>{{ number_format($route->estimated_total_cost, 0) }}</td>
                            <td>{{ number_format($route->actual_total_cost ?? 0, 0) }}</td>
                            <td>
                                <span class="badge {{ $costVar > 0 ? 'badge-danger' : 'badge-success' }}">
                                    {{ $costVar > 0 ? '+' : '' }}{{ number_format($costVar, 0) }}
                                </span>
                            </td>
                            <td>
                                @if($route->cost_variance_percentage)
                                    <span class="badge {{ abs($route->cost_variance_percentage) > 15 ? 'badge-danger' : (abs($route->cost_variance_percentage) > 5 ? 'badge-warning' : 'badge-info') }}">
                                        {{ number_format($route->cost_variance_percentage, 1) }}%
                                    </span>
                                @else
                                    <span class="badge badge-secondary">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($route->cost_variance_percentage && $route->cost_variance_percentage > 5)
                                    <span class="badge badge-danger">Over Budget</span>
                                @elseif($route->cost_variance_percentage && $route->cost_variance_percentage < -5)
                                    <span class="badge badge-success">Under Budget</span>
                                @else
                                    <span class="badge badge-info">On Target</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                <i class="fas fa-info-circle fa-2x mb-2"></i>
                                <p>No completed routes found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($routes->hasPages())
            <div class="card-footer">
                {{ $routes->links() }}
            </div>
            @endif
        </div>

    </div>
</div>

@endsection
