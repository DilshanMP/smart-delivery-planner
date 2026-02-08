@extends('layouts.admin')

@section('title', 'Add New Driver')
@section('page-title', 'Add New Driver')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
<li class="breadcrumb-item"><a href="{{ route('drivers.index') }}">Drivers</a></li>
<li class="breadcrumb-item active">Add New</li>
@endsection

@section('content')

<div class="row">
    <div class="col-md-10 offset-md-1">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Driver Information</h3>
            </div>

            <form action="{{ route('drivers.store') }}" method="POST">
                @csrf

                <div class="card-body">

                    <!-- Company Selection -->
                    <div class="form-group">
                        <label for="company_id">Company <span class="text-danger">*</span></label>
                        <select class="form-control @error('company_id') is-invalid @enderror"
                                id="company_id"
                                name="company_id"
                                required>
                            <option value="">-- Select Company --</option>
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

                    <!-- Driver Name -->
                    <div class="form-group">
                        <label for="name">Driver Name <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control @error('name') is-invalid @enderror"
                               id="name"
                               name="name"
                               value="{{ old('name') }}"
                               placeholder="Enter full name"
                               required>
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <!-- License Number -->
                            <div class="form-group">
                                <label for="license_number">License Number <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control @error('license_number') is-invalid @enderror"
                                       id="license_number"
                                       name="license_number"
                                       value="{{ old('license_number') }}"
                                       placeholder="e.g., B1234567"
                                       required>
                                @error('license_number')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <!-- License Type -->
                            <div class="form-group">
                                <label for="license_type">License Type <span class="text-danger">*</span></label>
                                <select class="form-control @error('license_type') is-invalid @enderror"
                                        id="license_type"
                                        name="license_type"
                                        required>
                                    <option value="">-- Select License Type --</option>
                                    <option value="car" {{ old('license_type') == 'car' ? 'selected' : '' }}>Car (Light Vehicle)</option>
                                    <option value="van" {{ old('license_type') == 'van' ? 'selected' : '' }}>Van (Medium Vehicle)</option>
                                    <option value="lorry" {{ old('license_type') == 'lorry' ? 'selected' : '' }}>Lorry (Heavy Vehicle)</option>
                                    <option value="heavy_vehicle" {{ old('license_type') == 'heavy_vehicle' ? 'selected' : '' }}>Heavy Vehicle</option>
                                    <option value="all" {{ old('license_type') == 'all' ? 'selected' : '' }}>All Categories</option>
                                </select>
                                @error('license_type')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- License Expiry Date -->
                    <div class="form-group">
                        <label for="license_expiry">License Expiry Date</label>
                        <input type="date"
                               class="form-control @error('license_expiry') is-invalid @enderror"
                               id="license_expiry"
                               name="license_expiry"
                               value="{{ old('license_expiry') }}"
                               min="{{ date('Y-m-d') }}">
                        @error('license_expiry')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">System will alert when license is about to expire</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <!-- Phone -->
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="text"
                                       class="form-control @error('phone') is-invalid @enderror"
                                       id="phone"
                                       name="phone"
                                       value="{{ old('phone') }}"
                                       placeholder="+94 71 234 5678">
                                @error('phone')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <!-- Email -->
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       id="email"
                                       name="email"
                                       value="{{ old('email') }}"
                                       placeholder="driver@example.com">
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea class="form-control @error('address') is-invalid @enderror"
                                  id="address"
                                  name="address"
                                  rows="2"
                                  placeholder="Enter full address">{{ old('address') }}</textarea>
                        @error('address')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <!-- Date of Birth -->
                            <div class="form-group">
                                <label for="date_of_birth">Date of Birth</label>
                                <input type="date"
                                       class="form-control @error('date_of_birth') is-invalid @enderror"
                                       id="date_of_birth"
                                       name="date_of_birth"
                                       value="{{ old('date_of_birth') }}"
                                       max="{{ date('Y-m-d') }}">
                                @error('date_of_birth')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <!-- Experience Years -->
                            <div class="form-group">
                                <label for="experience_years">Years of Experience</label>
                                <input type="number"
                                       class="form-control @error('experience_years') is-invalid @enderror"
                                       id="experience_years"
                                       name="experience_years"
                                       value="{{ old('experience_years') }}"
                                       placeholder="e.g., 5"
                                       min="0"
                                       max="50">
                                @error('experience_years')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <small class="form-text text-muted">Total years of driving experience</small>
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox"
                                   class="custom-control-input"
                                   id="is_active"
                                   name="is_active"
                                   value="1"
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_active">Active (Available for routes)</label>
                        </div>
                    </div>

                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Add Driver
                    </button>
                    <a href="{{ route('drivers.index') }}" class="btn btn-default">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
