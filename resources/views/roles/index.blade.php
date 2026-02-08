@extends('layouts.admin')

@section('title', 'Roles & Permissions')
@section('page-title', 'Roles & Permissions Management')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
<li class="breadcrumb-item active">Roles</li>
@endsection

@section('content')

<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Roles</h3>
        <div class="card-tools">
            @can('create roles')
            <a href="{{ route('roles.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Create New Role
            </a>
            @endcan
        </div>
    </div>
    <div class="card-body">
        <table id="rolesTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th width="5%">ID</th>
                    <th width="20%">Role Name</th>
                    <th width="15%">Guard</th>
                    <th width="10%" class="text-center">Users Count</th>
                    <th width="35%">Permissions</th>
                    <th width="10%" class="text-center">Created</th>
                    <th width="10%" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($roles as $role)
                <tr>
                    <td>{{ $role->id }}</td>
                    <td>
                        <i class="fas fa-shield-alt text-primary"></i>
                        <strong>{{ $role->name }}</strong>
                    </td>
                    <td>
                        <span class="badge badge-info">{{ $role->guard_name }}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge badge-success">{{ $role->users->count() }}</span>
                    </td>
                    <td>
                        @if($role->permissions->count() > 0)
                            @foreach($role->permissions->take(3) as $permission)
                                <span class="badge badge-secondary">{{ $permission->name }}</span>
                            @endforeach
                            @if($role->permissions->count() > 3)
                                <span class="badge badge-light">+{{ $role->permissions->count() - 3 }} more</span>
                            @endif
                        @else
                            <span class="text-muted">No permissions</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <small>{{ $role->created_at->format('M d, Y') }}</small>
                    </td>
                    <td class="text-center">
                        @can('view roles')
                        <a href="{{ route('roles.show', $role) }}"
                           class="btn btn-sm btn-info"
                           title="View Details">
                            <i class="fas fa-eye"></i>
                        </a>
                        @endcan

                        @can('edit roles')
                        <a href="{{ route('roles.edit', $role) }}"
                           class="btn btn-sm btn-warning"
                           title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        @endcan

                        @can('delete roles')
                        @if(!in_array($role->name, ['Admin', 'Super Admin']))
                        <form action="{{ route('roles.destroy', $role) }}"
                              method="POST"
                              class="d-inline"
                              onsubmit="return confirm('Are you sure you want to delete this role?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="btn btn-sm btn-danger"
                                    title="Delete"
                                    {{ $role->users->count() > 0 ? 'disabled' : '' }}>
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        @else
                        <button type="button"
                                class="btn btn-sm btn-secondary"
                                title="System Role - Cannot Delete"
                                disabled>
                            <i class="fas fa-lock"></i>
                        </button>
                        @endif
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Role Statistics -->
<div class="row mt-3">
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-info"><i class="fas fa-shield-alt"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Roles</span>
                <span class="info-box-number">{{ $roles->count() }}</span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-success"><i class="fas fa-users"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Users</span>
                <span class="info-box-number">{{ $roles->sum(function($role) { return $role->users->count(); }) }}</span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-warning"><i class="fas fa-key"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Permissions</span>
                <span class="info-box-number">{{ \Spatie\Permission\Models\Permission::count() }}</span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-danger"><i class="fas fa-shield-alt"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">System Roles</span>
                <span class="info-box-number">{{ $roles->whereIn('name', ['Admin', 'Super Admin'])->count() }}</span>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#rolesTable').DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "pageLength": 10,
        "order": [[0, 'asc']],
        "columnDefs": [
            { "orderable": false, "targets": [6] }
        ]
    });
});
</script>
@endpush
