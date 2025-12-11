@extends('layouts.admin')

@section('title', 'Edit Vehicle')
@section('page-title', 'Edit Vehicle')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
<li class="breadcrumb-item"><a href="{{ route('vehicles.index') }}">Vehicles</a></li>
<li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')

<div class="row">
    <div class="col-md-10 offset-md-1">
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">Edit Vehicle Information - {{ strtoupper($vehicle->registration_number) }}</h3>
            </div>

            <form action="{{ route('vehicles.update', $vehicle) }}" method="POST">
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
                                    {{ old('company_id', $vehicle->company_id) == $company->id ? 'selected' : '' }}>
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
                                       value="{{ old('registration_number', $vehicle->registration_number) }}"
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
                                    <option value="lorry" {{ old('vehicle_type', $vehicle->vehicle_type) == 'lorry' ? 'selected' : '' }}>Lorry (Large Truck)</option>
                                    <option value="truck" {{ old('vehicle_type', $vehicle->vehicle_type) == 'truck' ? 'selected' : '' }}>Truck</option>
                                    <option value="van" {{ old('vehicle_type', $vehicle->vehicle_type) == 'van' ? 'selected' : '' }}>Van</option>
                                    <option value="mini_truck" {{ old('vehicle_type', $vehicle->vehicle_type) == 'mini_truck' ? 'selected' : '' }}>Mini Truck</option>
                                    <option value="pickup" {{ old('vehicle_type', $vehicle->vehicle_type) == 'pickup' ? 'selected' : '' }}>Pickup</option>
                                    <option value="other" {{ old('vehicle_type', $vehicle->vehicle_type) == 'other' ? 'selected' : '' }}>Other</option>
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
                                       value="{{ old('make', $vehicle->make) }}">
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
                                       value="{{ old('model', $vehicle->model) }}">
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
                                       value="{{ old('year', $vehicle->year) }}"
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
                                       value="{{ old('capacity_weight', $vehicle->capacity_weight) }}"
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
                                       value="{{ old('capacity_volume', $vehicle->capacity_volume) }}"
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
                                    <option value="diesel" {{ old('fuel_type', $vehicle->fuel_type) == 'diesel' ? 'selected' : '' }}>Diesel</option>
                                    <option value="petrol" {{ old('fuel_type', $vehicle->fuel_type) == 'petrol' ? 'selected' : '' }}>Petrol</option>
                                    <option value="electric" {{ old('fuel_type', $vehicle->fuel_type) == 'electric' ? 'selected' : '' }}>Electric</option>
                                    <option value="hybrid" {{ old('fuel_type', $vehicle->fuel_type) == 'hybrid' ? 'selected' : '' }}>Hybrid</option>
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
                                       value="{{ old('fuel_efficiency', $vehicle->fuel_efficiency) }}"
                                       min="0"
                                       step="0.1">
                                @error('fuel_efficiency')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
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
                            <option value="excellent" {{ old('condition', $vehicle->condition) == 'excellent' ? 'selected' : '' }}>Excellent</option>
                            <option value="good" {{ old('condition', $vehicle->condition) == 'good' ? 'selected' : '' }}>Good</option>
                            <option value="fair" {{ old('condition', $vehicle->condition) == 'fair' ? 'selected' : '' }}>Fair</option>
                            <option value="poor" {{ old('condition', $vehicle->condition) == 'poor' ? 'selected' : '' }}>Poor</option>
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
                                       value="{{ old('last_service_date', $vehicle->last_service_date?->format('Y-m-d')) }}">
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
                                       value="{{ old('next_service_date', $vehicle->next_service_date?->format('Y-m-d')) }}">
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
                                       value="{{ old('insurance_expiry', $vehicle->insurance_expiry?->format('Y-m-d')) }}">
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
                                       value="{{ old('license_expiry', $vehicle->license_expiry?->format('Y-m-d')) }}">
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
                                   {{ old('is_active', $vehicle->is_active) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_active">Active (Available for routes)</label>
                        </div>
                    </div>

                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save"></i> Update Vehicle
                    </button>
                    <a href="{{ route('vehicles.index') }}" class="btn btn-default">
                        <i class="fas fa-times"></i> Cancel
                    </a>

                    @can('delete', $vehicle)
                    <button type="button" class="btn btn-danger float-right" data-toggle="modal" data-target="#deleteModal">
                        <i class="fas fa-trash"></i> Delete Vehicle
                    </button>
                    @endcan
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
@can('delete', $vehicle)
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="{{ route('vehicles.destroy', $vehicle) }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p>Are you sure you want to delete vehicle <strong>{{ strtoupper($vehicle->registration_number) }}</strong>?</p>
                    <p class="text-danger">This action cannot be undone!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Vehicle</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

@endsection
