@extends('layouts.admin')

@section('title', 'Warehouses')
@section('page-title', 'Warehouses')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
<li class="breadcrumb-item active">Warehouses</li>
@endsection

@section('content')

<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Warehouses</h3>
        <div class="card-tools">
            @can('create', App\Models\Warehouse::class)
            <a href="{{ route('warehouses.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Create New Warehouse
            </a>
            @endcan
        </div>
    </div>
    <div class="card-body">
        <table id="warehousesTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th width="5%">ID</th>
                    <th width="12%">Code</th>
                    <th width="18%">Warehouse Name</th>
                    <th width="15%">Company</th>
                    <th width="15%">City</th>
                    <th width="12%">Contact Person</th>
                    <th width="10%" class="text-center">Capacity</th>
                    <th width="8%" class="text-center">Status</th>
                    <th width="5%" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($warehouses as $warehouse)
                <tr>
                    <td>{{ $warehouse->id }}</td>
                    <td><strong>{{ $warehouse->code }}</strong></td>
                    <td>
                        <i class="fas fa-warehouse text-info"></i>
                        {{ $warehouse->name }}
                    </td>
                    <td>{{ $warehouse->company->name ?? '-' }}</td>
                    <td>{{ $warehouse->city ?? '-' }}</td>
                    <td>{{ $warehouse->contact_person ?? '-' }}</td>
                    <td class="text-center">
                        @if($warehouse->capacity)
                            <span class="badge badge-secondary">
                                {{ number_format($warehouse->capacity) }} units
                            </span>
                            @if($warehouse->current_stock)
                                <br>
                                <small class="text-muted">
                                    Stock: {{ number_format($warehouse->current_stock) }}
                                    ({{ round(($warehouse->current_stock / $warehouse->capacity) * 100) }}%)
                                </small>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-center">
                        @if($warehouse->is_active)
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-danger">Inactive</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @can('view', $warehouse)
                        <a href="{{ route('warehouses.show', $warehouse) }}"
                           class="btn btn-sm btn-info"
                           title="View Details">
                            <i class="fas fa-eye"></i>
                        </a>
                        @endcan

                        @can('update', $warehouse)
                        <a href="{{ route('warehouses.edit', $warehouse) }}"
                           class="btn btn-sm btn-warning"
                           title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        @endcan

                        @can('delete', $warehouse)
                        <form action="{{ route('warehouses.destroy', $warehouse) }}"
                              method="POST"
                              class="d-inline"
                              onsubmit="return confirm('Are you sure you want to delete this warehouse?');">
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
    $('#warehousesTable').DataTable({
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
