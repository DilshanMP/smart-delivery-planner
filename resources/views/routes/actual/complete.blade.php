@extends('layouts.admin')

@section('title', 'Complete Route')
@section('page-title', 'Complete Route - Enter Actual Data')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
<li class="breadcrumb-item"><a href="{{ route('routes.index') }}">Routes</a></li>
<li class="breadcrumb-item active">Complete {{ $route->route_code }}</li>
@endsection

@section('content')

<form action="{{ route('routes.actual.store-completion', $route) }}" method="POST" id="completionForm">
    @csrf

    <div class="row">
        <!-- Left Column: Logbook Entry -->
        <div class="col-md-8">

            <!-- Route Summary -->
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-route"></i> Route: {{ $route->route_code }}</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Date:</strong> {{ $route->route_date->format('M d, Y') }}</p>
                            <p><strong>Driver:</strong> {{ $route->driver->name }}</p>
                            <p><strong>Vehicle:</strong> {{ $route->vehicle->registration_number }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Company:</strong> {{ $route->company->name }}</p>
                            <p><strong>Stops:</strong> {{ $route->stops->count() }}</p>
                            <p><strong>Estimated Distance:</strong> {{ number_format($route->estimated_distance_km, 1) }} km</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Logbook Entry (Most Important!) -->
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-book"></i> Logbook Entry (Start & End KM)</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Start KM (from logbook) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="actual_start_km" id="start_km"
                                       class="form-control form-control-lg @error('actual_start_km') is-invalid @enderror"
                                       value="{{ old('actual_start_km') }}"
                                       required
                                       onchange="calculateActualDistance()">
                                @error('actual_start_km')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <small class="text-muted">Enter odometer reading at start</small>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>End KM (from logbook) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="actual_end_km" id="end_km"
                                       class="form-control form-control-lg @error('actual_end_km') is-invalid @enderror"
                                       value="{{ old('actual_end_km') }}"
                                       required
                                       onchange="calculateActualDistance()">
                                @error('actual_end_km')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <small class="text-muted">Enter odometer reading at end</small>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Actual Distance (Auto-calculated)</label>
                                <input type="text" id="actual_distance"
                                       class="form-control form-control-lg bg-light"
                                       value="0 km"
                                       readonly>
                                <small class="text-muted">End KM - Start KM</small>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle"></i> How to Enter Logbook Data:</h5>
                        <ol class="mb-0">
                            <li>Check the vehicle logbook</li>
                            <li>Enter the <strong>Start KM</strong> (odometer at route start)</li>
                            <li>Enter the <strong>End KM</strong> (odometer at route end)</li>
                            <li>System will auto-calculate <strong>Actual Distance</strong></li>
                        </ol>
                    </div>
                </div>
            </div>

            <!-- Actual Costs Entry -->
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-money-bill-wave"></i> Actual Costs (Optional - defaults to estimated)</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Actual Fuel Cost (LKR)</label>
                                <input type="number" step="0.01" name="actual_fuel_cost"
                                       class="form-control"
                                       value="{{ old('actual_fuel_cost', $route->estimated_fuel_cost) }}"
                                       placeholder="Enter actual fuel cost">
                                <small class="text-muted">Default: LKR {{ number_format($route->estimated_fuel_cost, 0) }}</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Actual Meal Cost (LKR)</label>
                                <input type="number" step="0.01" name="actual_meal_cost"
                                       class="form-control"
                                       value="{{ old('actual_meal_cost', $route->estimated_meal_cost) }}"
                                       placeholder="Enter actual meal cost">
                                <small class="text-muted">Default: LKR {{ number_format($route->estimated_meal_cost, 0) }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Actual Accommodation Cost (LKR)</label>
                                <input type="number" step="0.01" name="actual_accommodation_cost"
                                       class="form-control"
                                       value="{{ old('actual_accommodation_cost', $route->estimated_accommodation_cost) }}"
                                       placeholder="Enter actual accommodation cost">
                                <small class="text-muted">Default: LKR {{ number_format($route->estimated_accommodation_cost, 0) }}</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Other Costs (Tolls, Parking, etc.)</label>
                                <input type="number" step="0.01" name="actual_other_costs"
                                       class="form-control"
                                       value="{{ old('actual_other_costs', 0) }}"
                                       placeholder="Enter other costs">
                                <small class="text-muted">Any additional expenses</small>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning">
                        <i class="fas fa-lightbulb"></i> <strong>Tip:</strong> If you leave costs empty,
                        system will use the estimated values. You can update detailed costs later.
                    </div>
                </div>
            </div>

            <!-- Returns Entry -->
            <div class="card card-danger">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-undo"></i> Returns</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Return Sales Value (LKR)</label>
                        <input type="number" step="0.01" name="return_sales_value"
                               class="form-control"
                               value="{{ old('return_sales_value', 0) }}"
                               placeholder="Enter total return value">
                        <small class="text-muted">Products returned by customers</small>
                    </div>
                </div>
            </div>

            <!-- Completion Notes -->
            <div class="card card-secondary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-sticky-note"></i> Completion Notes</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea name="completion_notes" class="form-control" rows="4"
                                  placeholder="Any issues, delays, or special notes about this route...">{{ old('completion_notes') }}</textarea>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Column: Summary -->
        <div class="col-md-4">

            <!-- Estimated vs Actual Preview -->
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-bar"></i> Estimated vs Actual</h3>
                </div>
                <div class="card-body">
                    <h5>Distance Comparison:</h5>
                    <dl class="row">
                        <dt class="col-sm-7">Estimated:</dt>
                        <dd class="col-sm-5 text-right">{{ number_format($route->estimated_distance_km, 1) }} km</dd>

                        <dt class="col-sm-7">Actual:</dt>
                        <dd class="col-sm-5 text-right">
                            <span id="actual_km_display" class="text-primary">
                                <strong>- km</strong>
                            </span>
                        </dd>

                        <dt class="col-sm-7">Variance:</dt>
                        <dd class="col-sm-5 text-right">
                            <span id="km_variance" class="badge badge-secondary">-</span>
                        </dd>
                    </dl>

                    <hr>

                    <h5>Cost Comparison:</h5>
                    <dl class="row">
                        <dt class="col-sm-7">Estimated Cost:</dt>
                        <dd class="col-sm-5 text-right">
                            LKR {{ number_format($route->estimated_total_cost, 0) }}
                        </dd>

                        <dt class="col-sm-7">Actual Cost:</dt>
                        <dd class="col-sm-5 text-right">
                            <span class="text-primary">Will calculate</span>
                        </dd>
                    </dl>
                </div>
            </div>

            <!-- Route Stops Summary -->
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-map-pin"></i> Stops Covered</h3>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        @foreach($route->stops->sortBy('stop_sequence') as $stop)
                        <li class="mb-2">
                            <span class="badge badge-primary">{{ $stop->stop_sequence }}</span>
                            <strong>{{ $stop->shop_name }}</strong>
                            <br>
                            <small class="text-muted">{{ $stop->shop_address }}</small>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <!-- Submit Section -->
            <div class="card card-success">
                <div class="card-body">
                    <button type="submit" class="btn btn-success btn-block btn-lg">
                        <i class="fas fa-check-circle"></i> Complete Route
                    </button>

                    <a href="{{ route('routes.show', $route) }}" class="btn btn-default btn-block mt-2">
                        <i class="fas fa-times"></i> Cancel
                    </a>

                    <hr>

                    <div class="alert alert-success mb-0">
                        <h6><i class="fas fa-info-circle"></i> What happens next?</h6>
                        <ul class="mb-0 small">
                            <li>Route marked as completed</li>
                            <li>Variance calculated automatically</li>
                            <li>Company allocations updated</li>
                            <li>Data ready for reports</li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
function calculateActualDistance() {
    const startKm = parseFloat($('#start_km').val()) || 0;
    const endKm = parseFloat($('#end_km').val()) || 0;

    if (startKm > 0 && endKm > startKm) {
        const actualDistance = endKm - startKm;
        $('#actual_distance').val(actualDistance.toFixed(1) + ' km');

        // Update display
        $('#actual_km_display').html('<strong>' + actualDistance.toFixed(1) + ' km</strong>');

        // Calculate variance
        const estimatedKm = {{ $route->estimated_distance_km }};
        const variance = actualDistance - estimatedKm;
        const varianceClass = variance > 0 ? 'badge-danger' : 'badge-success';
        const varianceSign = variance > 0 ? '+' : '';

        $('#km_variance').removeClass('badge-secondary badge-success badge-danger')
                        .addClass(varianceClass)
                        .text(varianceSign + variance.toFixed(1) + ' km');
    } else {
        $('#actual_distance').val('0 km');
        $('#actual_km_display').html('<strong>- km</strong>');
        $('#km_variance').removeClass('badge-success badge-danger')
                        .addClass('badge-secondary')
                        .text('-');
    }
}

$(document).ready(function() {
    // Validation
    $('#completionForm').on('submit', function(e) {
        const startKm = parseFloat($('#start_km').val()) || 0;
        const endKm = parseFloat($('#end_km').val()) || 0;

        if (startKm <= 0) {
            e.preventDefault();
            alert('Please enter Start KM from logbook');
            $('#start_km').focus();
            return false;
        }

        if (endKm <= 0) {
            e.preventDefault();
            alert('Please enter End KM from logbook');
            $('#end_km').focus();
            return false;
        }

        if (endKm <= startKm) {
            e.preventDefault();
            alert('End KM must be greater than Start KM!');
            $('#end_km').focus();
            return false;
        }

        return confirm('Complete this route with the entered data?');
    });
});
</script>
@endpush
