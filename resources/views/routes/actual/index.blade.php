@extends('layouts.admin')

@section('title', 'Routes to Complete')
@section('page-title', 'Routes to Complete')

@section('content')

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-clipboard-check"></i> Pending Routes
                </h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>Route Code</th>
                            <th>Date</th>
                            <th>Driver</th>
                            <th>Vehicle</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($routes as $route)
                        <tr>
                            <td><strong>{{ $route->route_code }}</strong></td>
                            <td>{{ $route->route_date ? $route->route_date->format('d M Y') : 'N/A' }}</td>
                            <td>{{ $route->driver->name ?? 'N/A' }}</td>
                            <td>{{ $route->vehicle->registration_number ?? 'N/A' }}</td>
                            <td>
                                @if($route->status == 'in_progress')
                                    <span class="badge badge-info">In Progress</span>
                                @else
                                    <span class="badge badge-warning">Planned</span>
                                @endif
                            </td>
                            <td>
                                @if($route->status == 'planned')
                                    <form action="{{ route('routes.actual.start', $route) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="fas fa-play"></i> Start
                                        </button>
                                    </form>
                                @endif

                                <a href="{{ route('routes.actual.complete', $route) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-check"></i> Complete
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">No pending routes</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
