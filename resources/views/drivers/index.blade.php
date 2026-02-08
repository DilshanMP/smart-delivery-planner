@extends('layouts.admin')

@section('title', 'Drivers')
@section('page-title', 'Driver Management')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
<li class="breadcrumb-item active">Drivers</li>
@endsection

@section('content')

<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Drivers</h3>
        <div class="card-tools">
            @can('create', App\Models\Driver::class)
            <a href="{{ route('drivers.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add New Driver
            </a>
            @endcan
        </div>
    </div>
    <div class="card-body">
        <table id="driversTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th width="5%">ID</th>
                    <th width="20%">Driver Name</th>
                    <th width="12%">License No.</th>
                    <th width="12%">License Type</th>
                    <th width="15%">Company</th>
                    <th width="10%">Phone</th>
                    <th width="10%">Experience</th>
                    <th width="8%" class="text-center">Status</th>
                    <th width="8%" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($drivers as $driver)
                <tr>
                    <td>{{ $driver->id }}</td>
                    <td>
                        <i class="fas fa-user-tie text-primary"></i>
                        <strong>{{ $driver->name }}</strong>
                    </td>
                    <td>
                        <span class="badge badge-secondary">{{ $driver->license_number }}</span>
                    </td>
                    <td>
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
                        @if($driver->license_expiry)
                            <br>
                            <small class="text-muted">
                                Exp: {{ $driver->license_expiry->format('M Y') }}
                                @if($driver->license_expiry->isPast())
                                    <span class="badge badge-danger">Expired!</span>
                                @elseif($driver->license_expiry->diffInDays(now()) <= 30)
                                    <span class="badge badge-warning">Soon</span>
                                @endif
                            </small>
                        @endif
                    </td>
                    <td>{{ $driver->company->name ?? '-' }}</td>
                    <td>{{ $driver->phone ?? '-' }}</td>
                    <td>
                        @if($driver->experience_years)
                            <span class="badge badge-info">
                                {{ $driver->experience_years }} {{ $driver->experience_years == 1 ? 'year' : 'years' }}
                            </span>
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-center">
                        @if($driver->is_active)
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-danger">Inactive</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @can('view', $driver)
                        <a href="{{ route('drivers.show', $driver) }}"
                           class="btn btn-sm btn-info"
                           title="View Details">
                            <i class="fas fa-eye"></i>
                        </a>
                        @endcan

                        @can('update', $driver)
                        <a href="{{ route('drivers.edit', $driver) }}"
                           class="btn btn-sm btn-warning"
                           title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        @endcan

                        @can('delete', $driver)
                        <form action="{{ route('drivers.destroy', $driver) }}"
                              method="POST"
                              class="d-inline"
                              onsubmit="return confirm('Are you sure you want to delete this driver?');">
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
    $('#driversTable').DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "pageLength": 25,
        "order": [[0, 'desc']],
        "columnDefs": [
            { "orderable": false, "targets": [8] }
        ]
    });
});
</script>
@endpush
