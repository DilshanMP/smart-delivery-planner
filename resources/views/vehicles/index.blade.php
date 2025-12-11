@extends('layouts.admin')

@section('title', 'Vehicles')
@section('page-title', 'Fleet Management - Vehicles')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
<li class="breadcrumb-item active">Vehicles</li>
@endsection

@section('content')

<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Vehicles</h3>
        <div class="card-tools">
            @can('create', App\Models\Vehicle::class)
            <a href="{{ route('vehicles.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add New Vehicle
            </a>
            @endcan
        </div>
    </div>
    <div class="card-body">
        <table id="vehiclesTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th width="5%">ID</th>
                    <th width="12%">Registration</th>
                    <th width="10%">Type</th>
                    <th width="15%">Make & Model</th>
                    <th width="12%">Company</th>
                    <th width="10%">Capacity</th>
                    <th width="10%">Fuel</th>
                    <th width="10%" class="text-center">Condition</th>
                    <th width="8%" class="text-center">Status</th>
                    <th width="8%" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($vehicles as $vehicle)
                <tr>
                    <td>{{ $vehicle->id }}</td>
                    <td>
                        <strong>{{ strtoupper($vehicle->registration_number) }}</strong>
                    </td>
                    <td>
                        @php
                            $typeIcons = [
                                'lorry' => 'fas fa-truck text-primary',
                                'truck' => 'fas fa-truck-moving text-info',
                                'van' => 'fas fa-van-shuttle text-success',
                                'mini_truck' => 'fas fa-truck-pickup text-warning',
                                'pickup' => 'fas fa-truck-pickup text-secondary',
                                'other' => 'fas fa-car text-muted'
                            ];
                            $icon = $typeIcons[$vehicle->vehicle_type] ?? 'fas fa-car';
                        @endphp
                        <i class="{{ $icon }}"></i> {{ ucwords(str_replace('_', ' ', $vehicle->vehicle_type)) }}
                    </td>
                    <td>
                        @if($vehicle->make || $vehicle->model)
                            {{ $vehicle->make }} {{ $vehicle->model }}
                            @if($vehicle->year)
                                <br><small class="text-muted">({{ $vehicle->year }})</small>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $vehicle->company->name ?? '-' }}</td>
                    <td>
                        @if($vehicle->capacity_weight)
                            <i class="fas fa-weight-hanging"></i> {{ number_format($vehicle->capacity_weight) }} kg
                        @endif
                        @if($vehicle->capacity_volume)
                            <br><i class="fas fa-cube"></i> {{ number_format($vehicle->capacity_volume) }} m³
                        @endif
                        @if(!$vehicle->capacity_weight && !$vehicle->capacity_volume)
                            -
                        @endif
                    </td>
                    <td>
                        @php
                            $fuelBadges = [
                                'diesel' => 'badge-dark',
                                'petrol' => 'badge-warning',
                                'electric' => 'badge-success',
                                'hybrid' => 'badge-info'
                            ];
                            $badge = $fuelBadges[$vehicle->fuel_type] ?? 'badge-secondary';
                        @endphp
                        <span class="badge {{ $badge }}">{{ ucfirst($vehicle->fuel_type) }}</span>
                        @if($vehicle->fuel_efficiency)
                            <br><small class="text-muted">{{ $vehicle->fuel_efficiency }} km/L</small>
                        @endif
                    </td>
                    <td class="text-center">
                        @php
                            $conditionBadges = [
                                'excellent' => 'badge-success',
                                'good' => 'badge-info',
                                'fair' => 'badge-warning',
                                'poor' => 'badge-danger'
                            ];
                            $condBadge = $conditionBadges[$vehicle->condition] ?? 'badge-secondary';
                        @endphp
                        <span class="badge {{ $condBadge }}">{{ ucfirst($vehicle->condition) }}</span>
                    </td>
                    <td class="text-center">
                        @if($vehicle->is_active)
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-danger">Inactive</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @can('view', $vehicle)
                        <a href="{{ route('vehicles.show', $vehicle) }}"
                           class="btn btn-sm btn-info"
                           title="View Details">
                            <i class="fas fa-eye"></i>
                        </a>
                        @endcan

                        @can('update', $vehicle)
                        <a href="{{ route('vehicles.edit', $vehicle) }}"
                           class="btn btn-sm btn-warning"
                           title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        @endcan

                        @can('delete', $vehicle)
                        <form action="{{ route('vehicles.destroy', $vehicle) }}"
                              method="POST"
                              class="d-inline"
                              onsubmit="return confirm('Are you sure you want to delete this vehicle?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#vehiclesTable').DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "pageLength": 25,
        "order": [[0, 'desc']],
        "columnDefs": [
            { "orderable": false, "targets": [9] }
        ]
    });
});
</script>
@endpush
