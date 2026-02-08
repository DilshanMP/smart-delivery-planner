@extends('layouts.admin')

@section('title', 'Add New Vehicle')
@section('page-title', 'Add New Vehicle')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
<li class="breadcrumb-item"><a href="{{ route('vehicles.index') }}">Vehicles</a></li>
<li class="breadcrumb-item active">Add New</li>
@endsection

@section('content')

<div class="row">
    <div class="col-md-10 offset-md-1">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Vehicle Information</h3>
            </div>

            <form action="{{ route('vehicles.store') }}" method="POST">
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

                    <div class="row">
                        <div class="col-md-6">
                            <!-- Registration Number -->
                            <div class="form-group">
                                <label for="registration_number">Registration Number <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control @error('registration_number') is-invalid @enderror"
                                       id="registration_number"
                                       name="registration_number"
                                       value="{{ old('registration_number') }}"
                                       placeholder="e.g., WP CAB-1234"
                                       required>
                                @error('registration_number')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <!-- Vehicle Type -->
                            <div class="form-group">
                                <label for="vehicle_type">Vehicle Type <span class="text-danger">*</span></label>
                                <select class="form-control @error('vehicle_type') is-invalid @enderror"
                                        id="vehicle_type"
                                        name="vehicle_type"
                                        required>
                                    <option value="">-- Select Type --</option>
                                    <option value="lorry" {{ old('vehicle_type') == 'lorry' ? 'selected' : '' }}>Lorry (Large Truck)</option>
                                    <option value="truck" {{ old('vehicle_type') == 'truck' ? 'selected' : '' }}>Truck</option>
                                    <option value="van" {{ old('vehicle_type') == 'van' ? 'selected' : '' }}>Van</option>
                                    <option value="mini_truck" {{ old('vehicle_type') == 'mini_truck' ? 'selected' : '' }}>Mini Truck</option>
                                    <option value="pickup" {{ old('vehicle_type') == 'pickup' ? 'selected' : '' }}>Pickup</option>
                                    <option value="other" {{ old('vehicle_type') == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('vehicle_type')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <!-- Make -->
                            <div class="form-group">
                                <label for="make">Make / Brand</label>
                                <input type="text"
                                       class="form-control @error('make') is-invalid @enderror"
                                       id="make"
                                       name="make"
                                       value="{{ old('make') }}"
                                       placeholder="e.g., TATA, Isuzu, Mitsubishi">
                                @error('make')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <!-- Model -->
                            <div class="form-group">
                                <label for="model">Model</label>
                                <input type="text"
                                       class="form-control @error('model') is-invalid @enderror"
                                       id="model"
                                       name="model"
                                       value="{{ old('model') }}"
                                       placeholder="e.g., Dyna, Canter">
                                @error('model')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <!-- Year -->
                            <div class="form-group">
                                <label for="year">Year</label>
                                <input type="number"
                                       class="form-control @error('year') is-invalid @enderror"
                                       id="year"
                                       name="year"
                                       value="{{ old('year') }}"
                                       placeholder="{{ date('Y') }}"
                                       min="1900"
                                       max="{{ date('Y') + 1 }}">
                                @error('year')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <!-- Capacity Weight -->
                            <div class="form-group">
                                <label for="capacity_weight">Capacity - Weight (kg)</label>
                                <input type="number"
                                       class="form-control @error('capacity_weight') is-invalid @enderror"
                                       id="capacity_weight"
                                       name="capacity_weight"
                                       value="{{ old('capacity_weight') }}"
                                       placeholder="e.g., 2000"
                                       min="0"
                                       step="0.01">
                                @error('capacity_weight')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <!-- Capacity Volume -->
                            <div class="form-group">
                                <label for="capacity_volume">Capacity - Volume (m³)</label>
                                <input type="number"
                                       class="form-control @error('capacity_volume') is-invalid @enderror"
                                       id="capacity_volume"
                                       name="capacity_volume"
                                       value="{{ old('capacity_volume') }}"
                                       placeholder="e.g., 20"
                                       min="0"
                                       step="0.01">
                                @error('capacity_volume')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <!-- Fuel Type -->
                            <div class="form-group">
                                <label for="fuel_type">Fuel Type <span class="text-danger">*</span></label>
                                <select class="form-control @error('fuel_type') is-invalid @enderror"
                                        id="fuel_type"
                                        name="fuel_type"
                                        required>
                                    <option value="">-- Select Fuel Type --</option>
                                    <option value="diesel" {{ old('fuel_type') == 'diesel' ? 'selected' : '' }}>Diesel</option>
                                    <option value="petrol" {{ old('fuel_type') == 'petrol' ? 'selected' : '' }}>Petrol</option>
                                    <option value="electric" {{ old('fuel_type') == 'electric' ? 'selected' : '' }}>Electric</option>
                                    <option value="hybrid" {{ old('fuel_type') == 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                                </select>
                                @error('fuel_type')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <!-- Fuel Efficiency -->
                            <div class="form-group">
                                <label for="fuel_efficiency">Fuel Efficiency (km/L)</label>
                                <input type="number"
                                       class="form-control @error('fuel_efficiency') is-invalid @enderror"
                                       id="fuel_efficiency"
                                       name="fuel_efficiency"
                                       value="{{ old('fuel_efficiency') }}"
                                       placeholder="e.g., 8.5"
                                       min="0"
                                       step="0.1">
                                @error('fuel_efficiency')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <small class="form-text text-muted">Average fuel efficiency (optional)</small>
                            </div>
                        </div>
                    </div>

                    <!-- Condition -->
                    <div class="form-group">
                        <label for="condition">Vehicle Condition <span class="text-danger">*</span></label>
                        <select class="form-control @error('condition') is-invalid @enderror"
                                id="condition"
                                name="condition"
                                required>
                            <option value="">-- Select Condition --</option>
                            <option value="excellent" {{ old('condition') == 'excellent' ? 'selected' : '' }}>Excellent</option>
                            <option value="good" {{ old('condition', 'good') == 'good' ? 'selected' : '' }}>Good</option>
                            <option value="fair" {{ old('condition') == 'fair' ? 'selected' : '' }}>Fair</option>
                            <option value="poor" {{ old('condition') == 'poor' ? 'selected' : '' }}>Poor</option>
                        </select>
                        @error('condition')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <!-- Last Service Date -->
                            <div class="form-group">
                                <label for="last_service_date">Last Service Date</label>
                                <input type="date"
                                       class="form-control @error('last_service_date') is-invalid @enderror"
                                       id="last_service_date"
                                       name="last_service_date"
                                       value="{{ old('last_service_date') }}">
                                @error('last_service_date')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <!-- Next Service Date -->
                            <div class="form-group">
                                <label for="next_service_date">Next Service Date</label>
                                <input type="date"
                                       class="form-control @error('next_service_date') is-invalid @enderror"
                                       id="next_service_date"
                                       name="next_service_date"
                                       value="{{ old('next_service_date') }}">
                                @error('next_service_date')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <!-- Insurance Expiry -->
                            <div class="form-group">
                                <label for="insurance_expiry">Insurance Expiry Date</label>
                                <input type="date"
                                       class="form-control @error('insurance_expiry') is-invalid @enderror"
                                       id="insurance_expiry"
                                       name="insurance_expiry"
                                       value="{{ old('insurance_expiry') }}">
                                @error('insurance_expiry')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <!-- License Expiry -->
                            <div class="form-group">
                                <label for="license_expiry">Vehicle License Expiry Date</label>
                                <input type="date"
                                       class="form-control @error('license_expiry') is-invalid @enderror"
                                       id="license_expiry"
                                       name="license_expiry"
                                       value="{{ old('license_expiry') }}">
                                @error('license_expiry')
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
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_active">Active (Available for routes)</label>
                        </div>
                    </div>

                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Add Vehicle
                    </button>
                    <a href="{{ route('vehicles.index') }}" class="btn btn-default">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
