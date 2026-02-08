@extends('layouts.admin')

@section('title', 'Edit Driver')
@section('page-title', 'Edit Driver')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
<li class="breadcrumb-item"><a href="{{ route('drivers.index') }}">Drivers</a></li>
<li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')

<div class="row">
    <div class="col-md-10 offset-md-1">
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">Edit Driver Information - {{ $driver->name }}</h3>
            </div>

            <form action="{{ route('drivers.update', $driver) }}" method="POST">
                @csrf
                @method('PUT')

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
                                <option value="{{ $company->id }}"
                                    {{ old('company_id', $driver->company_id) == $company->id ? 'selected' : '' }}>
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
                               value="{{ old('name', $driver->name) }}"
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
                                       value="{{ old('license_number', $driver->license_number) }}"
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
                                    <option value="car" {{ old('license_type', $driver->license_type) == 'car' ? 'selected' : '' }}>Car (Light Vehicle)</option>
                                    <option value="van" {{ old('license_type', $driver->license_type) == 'van' ? 'selected' : '' }}>Van (Medium Vehicle)</option>
                                    <option value="lorry" {{ old('license_type', $driver->license_type) == 'lorry' ? 'selected' : '' }}>Lorry (Heavy Vehicle)</option>
                                    <option value="heavy_vehicle" {{ old('license_type', $driver->license_type) == 'heavy_vehicle' ? 'selected' : '' }}>Heavy Vehicle</option>
                                    <option value="all" {{ old('license_type', $driver->license_type) == 'all' ? 'selected' : '' }}>All Categories</option>
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
                               value="{{ old('license_expiry', $driver->license_expiry?->format('Y-m-d')) }}"
                               min="{{ date('Y-m-d') }}">
                        @error('license_expiry')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
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
                                       value="{{ old('phone', $driver->phone) }}">
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
                                       value="{{ old('email', $driver->email) }}">
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
                                  rows="2">{{ old('address', $driver->address) }}</textarea>
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
                                       value="{{ old('date_of_birth', $driver->date_of_birth?->format('Y-m-d')) }}"
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
                                       value="{{ old('experience_years', $driver->experience_years) }}"
                                       min="0"
                                       max="50">
                                @error('experience_years')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
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
                                   {{ old('is_active', $driver->is_active) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_active">Active (Available for routes)</label>
                        </div>
                    </div>

                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save"></i> Update Driver
                    </button>
                    <a href="{{ route('drivers.index') }}" class="btn btn-default">
                        <i class="fas fa-times"></i> Cancel
                    </a>

                    @can('delete', $driver)
                    <button type="button" class="btn btn-danger float-right" data-toggle="modal" data-target="#deleteModal">
                        <i class="fas fa-trash"></i> Delete Driver
                    </button>
                    @endcan
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
@can('delete', $driver)
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="{{ route('drivers.destroy', $driver) }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p>Are you sure you want to delete driver <strong>{{ $driver->name }}</strong>?</p>
                    <p class="text-danger">This action cannot be undone!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Driver</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

@endsection
