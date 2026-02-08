@extends('layouts.admin')

@section('title', 'Create Role')
@section('page-title', 'Create New Role')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
<li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Roles</a></li>
<li class="breadcrumb-item active">Create</li>
@endsection

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Role Information</h3>
            </div>
            <form action="{{ route('roles.store') }}" method="POST">
                @csrf

                <div class="card-body">

                    <!-- Role Name -->
                    <div class="form-group row">
                        <label for="name" class="col-sm-2 col-form-label">
                            Role Name <span class="text-danger">*</span>
                        </label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name') }}"
                                   placeholder="e.g., Sales Manager" required>
                            @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">
                                Enter a descriptive name for this role (will be converted to lowercase with underscores)
                            </small>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="form-group row">
                        <label for="description" class="col-sm-2 col-form-label">Description</label>
                        <div class="col-sm-10">
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="3"
                                      placeholder="Brief description of this role's purpose">{{ old('description') }}</textarea>
                            @error('description')
                            <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <hr>

                    <!-- Permissions Section -->
                    <h5 class="mb-3">
                        <i class="fas fa-lock"></i> Permissions
                        <small class="text-muted">(Select permissions for this role)</small>
                    </h5>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="20%">Module</th>
                                    <th width="15%" class="text-center">Select All</th>
                                    <th width="60%">Specific Permissions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $index = 1; @endphp
                                @foreach($permissionGroups as $moduleName => $group)
                                <tr>
                                    <td>{{ $index++ }}</td>
                                    <td>
                                        <strong>{{ $group['name'] }}</strong>
                                    </td>
                                    <td class="text-center">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input select-all-module"
                                                   id="select_all_{{ strtolower(str_replace(' ', '_', $moduleName)) }}"
                                                   data-module="{{ strtolower(str_replace(' ', '_', $moduleName)) }}">
                                            <label class="custom-control-label"
                                                   for="select_all_{{ strtolower(str_replace(' ', '_', $moduleName)) }}">
                                                Select All
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="row">
                                            @foreach(['view', 'create', 'edit', 'delete', 'complete', 'export', 'use ai predictions', 'manage users', 'manage roles', 'view audit logs'] as $action)
                                                @if(isset($group['permissions'][$action]))
                                                <div class="col-md-3 col-sm-6">
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" class="custom-control-input module-permission"
                                                               id="permission_{{ str_replace(' ', '_', $group['permissions'][$action]) }}"
                                                               name="permissions[]"
                                                               value="{{ $group['permissions'][$action] }}"
                                                               data-module="{{ strtolower(str_replace(' ', '_', $moduleName)) }}"
                                                               {{ in_array($group['permissions'][$action], old('permissions', [])) ? 'checked' : '' }}>
                                                        <label class="custom-control-label"
                                                               for="permission_{{ str_replace(' ', '_', $group['permissions'][$action]) }}">
                                                            {{ ucfirst($action) }}
                                                        </label>
                                                    </div>
                                                </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @error('permissions')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror

                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Role
                    </button>
                    <a href="{{ route('roles.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Select All functionality for each module
    $('.select-all-module').on('change', function() {
        var module = $(this).data('module');
        var isChecked = $(this).is(':checked');

        $('.module-permission[data-module="' + module + '"]').prop('checked', isChecked);
    });

    // Update Select All checkbox when individual permissions change
    $('.module-permission').on('change', function() {
        var module = $(this).data('module');
        var totalPermissions = $('.module-permission[data-module="' + module + '"]').length;
        var checkedPermissions = $('.module-permission[data-module="' + module + '"]:checked').length;

        $('#select_all_' + module).prop('checked', totalPermissions === checkedPermissions);
    });

    // Initialize Select All checkboxes on page load
    $('.select-all-module').each(function() {
        var module = $(this).data('module');
        var totalPermissions = $('.module-permission[data-module="' + module + '"]').length;
        var checkedPermissions = $('.module-permission[data-module="' + module + '"]:checked').length;

        $(this).prop('checked', totalPermissions === checkedPermissions && totalPermissions > 0);
    });
});
</script>
@endpush
