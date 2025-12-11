@extends('layouts.admin')

@section('title', 'Edit User')
@section('page-title', 'Edit User')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
<li class="breadcrumb-item"><a href="{{ route('users.index') }}">Users</a></li>
<li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Edit User: {{ $user->name }}</h3>
            </div>
            <form action="{{ route('users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="card-body">
                    <!-- Name -->
                    <div class="form-group">
                        <label for="name">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name', $user->name) }}" required>
                        @error('name')
                        <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                        <label for="email">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                               id="email" name="email" value="{{ old('email', $user->email) }}" required>
                        @error('email')
                        <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Company -->
                    <div class="form-group">
                        <label for="company_id">Company</label>
                        <select class="form-control @error('company_id') is-invalid @enderror"
                                id="company_id" name="company_id">
                            <option value="">-- Select Company --</option>
                            @foreach($companies as $company)
                            <option value="{{ $company->id }}"
                                {{ old('company_id', $user->company_id) == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('company_id')
                        <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Phone -->
                    <div class="form-group">
                        <label for="phone_number">Phone Number</label>
                        <input type="text" class="form-control @error('phone_number') is-invalid @enderror"
                               id="phone_number" name="phone_number" value="{{ old('phone_number', $user->phone_number) }}">
                        @error('phone_number')
                        <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Role -->
                    <div class="form-group">
                        <label for="role">Role <span class="text-danger">*</span></label>
                        <select class="form-control @error('role') is-invalid @enderror"
                                id="role" name="role" required>
                            <option value="">-- Select Role --</option>
                            @foreach($roles as $role)
                            <option value="{{ $role->name }}"
                                {{ old('role', $user->roles->first()?->name) == $role->name ? 'selected' : '' }}>
                                {{ ucfirst($role->name) }}
                            </option>
                            @endforeach
                        </select>
                        @error('role')
                        <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Password (Optional) -->
                    <div class="form-group">
                        <label for="password">Password <small class="text-muted">(Leave blank to keep current password)</small></label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                               id="password" name="password">
                        @error('password')
                        <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Password Confirmation -->
                    <div class="form-group">
                        <label for="password_confirmation">Confirm Password</label>
                        <input type="password" class="form-control"
                               id="password_confirmation" name="password_confirmation">
                    </div>

                    <!-- Active Status -->
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1"
                                {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update User
                    </button>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
