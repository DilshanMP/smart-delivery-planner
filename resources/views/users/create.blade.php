@extends('layouts.admin')

@section('title', 'Create User')
@section('page-title', 'Create New User')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
<li class="breadcrumb-item"><a href="{{ route('users.index') }}">Users</a></li>
<li class="breadcrumb-item active">Create</li>
@endsection

@section('content')

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">User Information</h3>
            </div>

            <form action="{{ route('users.store') }}" method="POST">
                @csrf

                <div class="card-body">
                    <!-- Name -->
                    <div class="form-group">
                        <label for="name">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                        <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                        <label for="email">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                               id="email" name="email" value="{{ old('email') }}" required>
                        @error('email')
                        <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <label for="password">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                               id="password" name="password" required>
                        @error('password')
                        <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">Minimum 8 characters</small>
                    </div>

                    <!-- Password Confirmation -->
                    <div class="form-group">
                        <label for="password_confirmation">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control"
                               id="password_confirmation" name="password_confirmation" required>
                    </div>

                    <!-- Company -->
                    <div class="form-group">
                        <label for="company_id">Company</label>
                        <select class="form-control @error('company_id') is-invalid @enderror"
                                id="company_id" name="company_id">
                            <option value="">-- Select Company (Optional for Admins) --</option>
                            @foreach($companies as $company)
                            <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('company_id')
                        <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Phone Number -->
                    <div class="form-group">
                        <label for="phone_number">Phone Number</label>
                        <input type="text" class="form-control @error('phone_number') is-invalid @enderror"
                               id="phone_number" name="phone_number" value="{{ old('phone_number') }}"
                               placeholder="+94771234567">
                        @error('phone_number')
                        <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Role -->
                    <div class="form-group">
                        <label>Role <span class="text-danger">*</span></label>
                        <div>
                            @foreach($roles as $role)
                            <div class="form-check">
                                <input class="form-check-input" type="radio"
                                       name="role" id="role_{{ $role->id }}"
                                       value="{{ $role->name }}"
                                       {{ old('role') == $role->name ? 'checked' : '' }} required>
                                <label class="form-check-label" for="role_{{ $role->id }}">
                                    <strong>{{ ucfirst($role->name) }}</strong>
                                    <span class="text-muted">
                                        -
                                        @if($role->name == 'admin') Full system access
                                        @elseif($role->name == 'manager') Operational management
                                        @elseif($role->name == 'coordinator') Route coordination
                                        @else View-only access
                                        @endif
                                    </span>
                                </label>
                            </div>
                            @endforeach
                        </div>
                        @error('role')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input"
                                   id="is_active" name="is_active" value="1"
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create User
                    </button>
                    <a href="{{ route('users.index') }}" class="btn btn-default">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
