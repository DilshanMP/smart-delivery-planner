@extends('layouts.admin')

@section('title', 'Edit Warehouse')
@section('page-title', 'Edit Warehouse')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
<li class="breadcrumb-item"><a href="{{ route('warehouses.index') }}">Warehouses</a></li>
<li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')

<div class="row">
    <div class="col-md-8">
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">Edit Warehouse Information</h3>
            </div>

            <form action="{{ route('warehouses.update', $warehouse) }}" method="POST">
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
                                    {{ old('company_id', $warehouse->company_id) == $company->id ? 'selected' : '' }}>
                                    {{ $company->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('company_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <!-- Warehouse Name -->
                            <div class="form-group">
                                <label for="name">Warehouse Name <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control @error('name') is-invalid @enderror"
                                       id="name"
                                       name="name"
                                       value="{{ old('name', $warehouse->name) }}"
                                       placeholder="Enter warehouse name"
                                       required>
                                @error('name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <!-- Warehouse Code -->
                            <div class="form-group">
                                <label for="code">Code <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control @error('code') is-invalid @enderror"
                                       id="code"
                                       name="code"
                                       value="{{ old('code', $warehouse->code) }}"
                                       placeholder="WH001"
                                       required>
                                @error('code')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="form-group">
                        <label for="address">Address <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('address') is-invalid @enderror"
                                  id="address"
                                  name="address"
                                  rows="2"
                                  placeholder="Enter warehouse address"
                                  required>{{ old('address', $warehouse->address) }}</textarea>
                        @error('address')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- City -->
                    <div class="form-group">
                        <label for="city">City</label>
                        <input type="text"
                               class="form-control @error('city') is-invalid @enderror"
                               id="city"
                               name="city"
                               value="{{ old('city', $warehouse->city) }}"
                               placeholder="Enter city">
                        @error('city')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <!-- Latitude -->
                            <div class="form-group">
                                <label for="latitude">Latitude <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control @error('latitude') is-invalid @enderror"
                                       id="latitude"
                                       name="latitude"
                                       value="{{ old('latitude', $warehouse->latitude) }}"
                                       placeholder="6.9271"
                                       readonly
                                       required>
                                @error('latitude')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <small class="form-text text-muted">Click map to update location</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <!-- Longitude -->
                            <div class="form-group">
                                <label for="longitude">Longitude <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control @error('longitude') is-invalid @enderror"
                                       id="longitude"
                                       name="longitude"
                                       value="{{ old('longitude', $warehouse->longitude) }}"
                                       placeholder="79.8612"
                                       readonly
                                       required>
                                @error('longitude')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <small class="form-text text-muted">Click map to update location</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <!-- Capacity -->
                            <div class="form-group">
                                <label for="capacity">Capacity (units)</label>
                                <input type="number"
                                       class="form-control @error('capacity') is-invalid @enderror"
                                       id="capacity"
                                       name="capacity"
                                       value="{{ old('capacity', $warehouse->capacity) }}"
                                       placeholder="Enter capacity"
                                       min="0"
                                       step="0.01">
                                @error('capacity')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <!-- Current Stock -->
                            <div class="form-group">
                                <label for="current_stock">Current Stock (units)</label>
                                <input type="number"
                                       class="form-control @error('current_stock') is-invalid @enderror"
                                       id="current_stock"
                                       name="current_stock"
                                       value="{{ old('current_stock', $warehouse->current_stock) }}"
                                       placeholder="Enter current stock"
                                       min="0"
                                       step="0.01">
                                @error('current_stock')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <!-- Contact Person -->
                            <div class="form-group">
                                <label for="contact_person">Contact Person</label>
                                <input type="text"
                                       class="form-control @error('contact_person') is-invalid @enderror"
                                       id="contact_person"
                                       name="contact_person"
                                       value="{{ old('contact_person', $warehouse->contact_person) }}"
                                       placeholder="Enter contact person">
                                @error('contact_person')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <!-- Phone -->
                            <div class="form-group">
                                <label for="phone">Phone</label>
                                <input type="text"
                                       class="form-control @error('phone') is-invalid @enderror"
                                       id="phone"
                                       name="phone"
                                       value="{{ old('phone', $warehouse->phone) }}"
                                       placeholder="Enter phone number">
                                @error('phone')
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
                                   {{ old('is_active', $warehouse->is_active) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_active">Active</label>
                        </div>
                    </div>

                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save"></i> Update Warehouse
                    </button>
                    <a href="{{ route('warehouses.index') }}" class="btn btn-default">
                        <i class="fas fa-times"></i> Cancel
                    </a>

                    @can('delete', $warehouse)
                    <button type="button" class="btn btn-danger float-right" data-toggle="modal" data-target="#deleteModal">
                        <i class="fas fa-trash"></i> Delete Warehouse
                    </button>
                    @endcan
                </div>
            </form>
        </div>
    </div>

    <!-- Map Column -->
    <div class="col-md-4">
        <div class="card card-info sticky-top" style="top: 10px;">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-map-marker-alt"></i> Location Picker</h3>
            </div>
            <div class="card-body p-0">
                <div id="map" style="height: 500px; width: 100%;"></div>
            </div>
            <div class="card-footer">
                <small class="text-muted">
                    <i class="fas fa-info-circle"></i> Click on the map or drag marker to update location
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
@can('delete', $warehouse)
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="{{ route('warehouses.destroy', $warehouse) }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p>Are you sure you want to delete warehouse <strong>{{ $warehouse->name }}</strong>?</p>
                    <p class="text-danger">This action cannot be undone!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Warehouse</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

@endsection

@push('styles')
<style>
.sticky-top {
    position: sticky;
    z-index: 1020;
}
</style>
@endpush

@push('scripts')
<!-- Leaflet Map Library (Free, No API Key) -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<script>
// Get existing coordinates from form
var existingLat = parseFloat(document.getElementById('latitude').value);
var existingLng = parseFloat(document.getElementById('longitude').value);

// Initialize map centered on warehouse location
var map = L.map('map').setView([existingLat, existingLng], 13);

// Add OpenStreetMap tile layer
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
}).addTo(map);

// Add draggable marker at warehouse location
var marker = L.marker([existingLat, existingLng], {draggable: true}).addTo(map);

// Update coordinates when marker is dragged
marker.on('dragend', function(e) {
    var position = marker.getLatLng();
    updateCoordinates(position.lat, position.lng);
});

// Update coordinates when map is clicked
map.on('click', function(e) {
    marker.setLatLng(e.latlng);
    updateCoordinates(e.latlng.lat, e.latlng.lng);
});

// Function to update coordinate inputs
function updateCoordinates(lat, lng) {
    document.getElementById('latitude').value = lat.toFixed(6);
    document.getElementById('longitude').value = lng.toFixed(6);
}
</script>
@endpush
