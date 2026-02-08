@extends('layouts.admin')

@section('title', 'Users')
@section('page-title', 'Users Management')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
<li class="breadcrumb-item active">Users</li>
@endsection

@section('content')

<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Users</h3>
        <div class="card-tools">
            <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add New User
            </a>
        </div>
    </div>
    <div class="card-body">
        <table id="usersTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Company</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->company->name ?? 'N/A' }}</td>
                    <td>
                        @if($user->roles->count() > 0)
                            @foreach($user->roles as $role)
                            <span class="badge badge-info">{{ ucfirst($role->name) }}</span>
                            @endforeach
                        @else
                            <span class="badge badge-secondary">No Role</span>
                        @endif
                    </td>
                    <td>
                        @if($user->is_active)
                        <span class="badge badge-success">Active</span>
                        @else
                        <span class="badge badge-danger">Inactive</span>
                        @endif
                    </td>
                    <td>{{ $user->created_at->format('Y-m-d') }}</td>
                    <td>
                        <!-- Edit Button -->
                        <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-warning" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>

                        <!-- Delete Button -->
                        @if($user->id !== auth()->id())
                        <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Are you sure you want to delete this user?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        @else
                        <button class="btn btn-sm btn-secondary" disabled title="Cannot delete yourself">
                            <i class="fas fa-trash"></i>
                        </button>
                        @endif
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
    $('#usersTable').DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
    });
});
</script>
@endpush
