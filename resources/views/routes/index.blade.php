@extends('layouts.admin')

@section('title', 'Routes')
@section('page-title', 'Route Management')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
<li class="breadcrumb-item active">Routes</li>
@endsection

@section('content')

<!-- Filter Card -->
<div class="card card-primary card-outline collapsed-card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-filter"></i> Filters</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-plus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <form action="{{ route('routes.index') }}" method="GET">
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
                        <label>Driver</label>
                        <select name="driver_id" class="form-control">
                            <option value="">All Drivers</option>
                            @foreach($drivers as $driver)
                                <option value="{{ $driver->id }}" {{ request('driver_id') == $driver->id ? 'selected' : '' }}>
                                    {{ $driver->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="planned" {{ request('status') == 'planned' ? 'selected' : '' }}>Planned</option>
                            <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Date From</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Date To</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Apply Filters
                    </button>
                    <a href="{{ route('routes.index') }}" class="btn btn-default">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Routes Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-route"></i> All Routes</h3>
        <div class="card-tools">
            @can('create', App\Models\Route::class)
            <a href="{{ route('routes.create') }}" class="btn btn-success btn-sm">
                <i class="fas fa-plus"></i> Plan New Route
            </a>
            @endcan
        </div>
    </div>
    <div class="card-body table-responsive">
        <table id="routesTable" class="table table-bordered table-striped table-hover">
            <thead>
                <tr>
                    <th width="8%">Route Code</th>
                    <th width="10%">Date</th>
                    <th width="12%">Company</th>
                    <th width="10%">Driver</th>
                    <th width="10%">Vehicle</th>
                    <th width="8%" class="text-center">Stops</th>
                    <th width="8%" class="text-right">Distance</th>
                    <th width="10%" class="text-right">Est. Cost</th>
                    <th width="8%" class="text-center">Status</th>
                    <th width="12%" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($routes as $route)
                <tr>
                    <td>
                        <strong class="text-primary">{{ $route->route_code }}</strong>
                        @if($route->delivery_type == 'outside')
                            <span class="badge badge-info badge-sm">External</span>
                        @endif
                    </td>
                    <td>
                        @if($route->route_date)
                            {{ $route->route_date->format('M d, Y') }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $route->company->name ?? '-' }}</td>
                    <td>
                        <i class="fas fa-user-tie text-muted"></i>
                        {{ $route->driver->name ?? '-' }}
                    </td>
                    <td>
                        <i class="fas fa-truck text-muted"></i>
                        {{ $route->vehicle->registration_number ?? '-' }}
                    </td>
                    <td class="text-center">
                        <span class="badge badge-secondary">
                            {{ $route->stops->count() }}
                        </span>
                    </td>
                    <td class="text-right">
                        @if($route->estimated_distance_km)
                            <strong>{{ number_format($route->estimated_distance_km, 1) }}</strong> km
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-right">
                        @if($route->estimated_total_cost)
                            <strong>LKR {{ number_format($route->estimated_total_cost, 0) }}</strong>
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-center">
                        @php
                            $statusBadges = [
                                'planned' => 'badge-warning',
                                'in_progress' => 'badge-info',
                                'completed' => 'badge-success',
                                'cancelled' => 'badge-danger'
                            ];
                            $badge = $statusBadges[$route->status] ?? 'badge-secondary';
                        @endphp
                        <span class="badge {{ $badge }}">
                            {{ ucfirst(str_replace('_', ' ', $route->status)) }}
                        </span>
                    </td>
                    <td class="text-center">
                        @can('view', $route)
                        <a href="{{ route('routes.show', $route) }}"
                           class="btn btn-info btn-xs"
                           title="View Details">
                            <i class="fas fa-eye"></i>
                        </a>
                        @endcan

                        @can('update', $route)
                        <a href="{{ route('routes.edit', $route) }}"
                           class="btn btn-warning btn-xs"
                           title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        @endcan

                        @if($route->status == 'planned')
                            <a href="{{ route('routes.actual.complete', $route) }}"
                               class="btn btn-success btn-xs"
                               title="Complete Route">
                                <i class="fas fa-check"></i>
                            </a>
                        @endif

                        @can('delete', $route)
                        <button type="button" class="btn btn-danger btn-xs"
                                onclick="deleteRoute({{ $route->id }})"
                                title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                        <form id="delete-form-{{ $route->id }}"
                              action="{{ route('routes.destroy', $route) }}"
                              method="POST"
                              style="display: none;">
                            @csrf
                            @method('DELETE')
                        </form>
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer clearfix">
        {{ $routes->links() }}
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#routesTable').DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "pageLength": 25,
        "order": [[1, 'desc']], // Sort by date desc
        "columnDefs": [
            { "orderable": false, "targets": [9] } // Actions column
        ],
        "dom": 'Bfrtip',
        "buttons": [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ]
    });
});

function deleteRoute(id) {
    if (confirm('Delete this route and all its data? This cannot be undone!')) {
        document.getElementById('delete-form-' + id).submit();
    }
}
</script>
@endpush
