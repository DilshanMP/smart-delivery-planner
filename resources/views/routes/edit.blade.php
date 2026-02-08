@extends('layouts.admin')

@section('title', 'Edit Route')
@section('page-title', 'Edit Route')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
<li class="breadcrumb-item"><a href="{{ route('routes.index') }}">Routes</a></li>
<li class="breadcrumb-item active">Edit Route</li>
@endsection

@section('content')

<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle"></i>
    <strong>Note:</strong> You can only edit routes that haven't been started yet (status: planned).
</div>

<form action="{{ route('routes.update', $route) }}" method="POST" id="routeForm">
    @csrf
    @method('PUT')

    <div class="row">
        <!-- Left Column -->
        <div class="col-md-8">

            <!-- Basic Info Card -->
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle"></i> Route Information</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Route Code</label>
                                <input type="text" name="route_code" class="form-control"
                                       value="{{ $route->route_code }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Route Date <span class="text-danger">*</span></label>
                                <input type="date" name="route_date" class="form-control"
                                       value="{{ $route->route_date ? $route->route_date->format('Y-m-d') : '' }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Company <span class="text-danger">*</span></label>
                                <select name="company_id" class="form-control select2" required>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->id }}" {{ $route->company_id == $company->id ? 'selected' : '' }}>
                                            {{ $company->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Driver <span class="text-danger">*</span></label>
                                <select name="driver_id" class="form-control select2" required>
                                    @foreach($drivers as $driver)
                                        <option value="{{ $driver->id }}" {{ $route->driver_id == $driver->id ? 'selected' : '' }}>
                                            {{ $driver->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Vehicle <span class="text-danger">*</span></label>
                                <select name="vehicle_id" id="vehicle_id" class="form-control select2" required>
                                    @foreach($vehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}"
                                                data-efficiency="{{ $vehicle->fuel_efficiency }}"
                                                {{ $route->vehicle_id == $vehicle->id ? 'selected' : '' }}>
                                            {{ $vehicle->registration_number }} ({{ $vehicle->vehicle_type }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Delivery Type <span class="text-danger">*</span></label>
                                <select name="delivery_type" class="form-control" required>
                                    <option value="own" {{ $route->delivery_type == 'own' ? 'selected' : '' }}>Own Delivery</option>
                                    <option value="outside" {{ $route->delivery_type == 'outside' ? 'selected' : '' }}>Outside Delivery</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Company-wise Sales -->
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-line"></i> Company-wise Sales</h3>
                </div>
                <div class="card-body">
                    <div id="companySalesContainer">
                        @foreach($route->companyAllocations as $index => $allocation)
                        <div class="company-sales-row mb-3" data-index="{{ $index }}">
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label>Company</label>
                                        <select name="company_sales[{{ $index }}][company_id]" class="form-control" required>
                                            @foreach($companies as $company)
                                                <option value="{{ $company->id }}" {{ $allocation->company_id == $company->id ? 'selected' : '' }}>
                                                    {{ $company->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Sales Value (LKR)</label>
                                        <input type="number" step="0.01" name="company_sales[{{ $index }}][sales_value]"
                                               class="form-control company-sales-value"
                                               value="{{ $allocation->total_sales_value }}" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Quantity</label>
                                        <input type="number" name="company_sales[{{ $index }}][sales_qty]"
                                               class="form-control"
                                               value="{{ $allocation->total_sales_qty }}" required>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <label>&nbsp;</label>
                                    <button type="button" class="btn btn-danger btn-block" onclick="removeCompanySales({{ $index }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <button type="button" class="btn btn-success btn-sm" onclick="addCompanySales()">
                        <i class="fas fa-plus"></i> Add Another Company
                    </button>

                    <hr>
                    <h5>Total Sales Value: LKR <span id="totalSalesValue" class="text-success">{{ $route->companyAllocations->sum('total_sales_value') }}</span></h5>
                </div>
            </div>

            <!-- Existing Stops -->
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-map-pin"></i> Route Stops ({{ $route->stops->count() }})</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        To modify stops, please create a new route. Existing stops are shown below for reference.
                    </div>

                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Shop Name</th>
                                <th>Address</th>
                                <th>Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($route->stops as $stop)
                            <tr>
                                <td>{{ $stop->stop_sequence }}</td>
                                <td>{{ $stop->shop_name }}</td>
                                <td>{{ $stop->shop_address }}</td>
                                <td>
                                    <span class="badge badge-secondary">{{ ucfirst($stop->stop_type) }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Right Column: Cost Estimation -->
        <div class="col-md-4">

            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-calculator"></i> Cost Estimation</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Estimated Distance (km)</label>
                        <input type="number" step="0.01" name="estimated_distance_km"
                               class="form-control" value="{{ $route->estimated_distance_km }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>Number of Days</label>
                        <input type="number" name="estimated_days" id="estimated_days"
                               class="form-control" value="{{ $route->estimated_days }}" min="1" required>
                    </div>

                    <div class="form-group">
                        <label>Fuel Rate (LKR per litre)</label>
                        <input type="number" step="0.01" name="estimated_fuel_rate_per_litre" id="fuel_rate"
                               class="form-control" value="{{ $route->estimated_fuel_rate_per_litre }}">
                    </div>

                    <div class="form-group">
                        <label>Estimated Fuel Cost (LKR)</label>
                        <input type="number" step="0.01" name="estimated_fuel_cost" id="estimated_fuel_cost"
                               class="form-control" value="{{ $route->estimated_fuel_cost }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>Meal Cost (LKR)</label>
                        <input type="number" step="0.01" name="estimated_meal_cost" id="meal_cost"
                               class="form-control" value="{{ $route->estimated_meal_cost }}">
                    </div>

                    <div class="form-group">
                        <label>Accommodation Cost (LKR)</label>
                        <input type="number" step="0.01" name="estimated_accommodation_cost" id="accommodation_cost"
                               class="form-control" value="{{ $route->estimated_accommodation_cost }}">
                    </div>

                    <hr>

                    <h4>TOTAL: LKR <span id="total_cost">{{ $route->estimated_total_cost }}</span></h4>

                    <div class="alert alert-success mt-3">
                        <h5>Cost Ratio: <span id="cost_percentage">0</span>%</h5>
                    </div>

                    <div class="form-group">
                        <label>Notes</label>
                        <textarea name="notes" class="form-control" rows="3">{{ $route->notes }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary btn-block btn-lg">
                        <i class="fas fa-save"></i> Update Route
                    </button>
                    <a href="{{ route('routes.show', $route) }}" class="btn btn-default btn-block">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </div>

        </div>
    </div>
</form>

@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
let companySalesIndex = {{ $route->companyAllocations->count() }};

$(document).ready(function() {
    $('.select2').select2();
    calculateCostPercentage();
});

function addCompanySales() {
    const html = `
        <div class="company-sales-row mb-3" data-index="${companySalesIndex}">
            <div class="row">
                <div class="col-md-5">
                    <div class="form-group">
                        <label>Company</label>
                        <select name="company_sales[${companySalesIndex}][company_id]" class="form-control" required>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Sales Value (LKR)</label>
                        <input type="number" step="0.01" name="company_sales[${companySalesIndex}][sales_value]"
                               class="form-control company-sales-value" required onchange="calculateTotalSales()">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Quantity</label>
                        <input type="number" name="company_sales[${companySalesIndex}][sales_qty]"
                               class="form-control" required>
                    </div>
                </div>
                <div class="col-md-1">
                    <label>&nbsp;</label>
                    <button type="button" class="btn btn-danger btn-block" onclick="removeCompanySales(${companySalesIndex})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    $('#companySalesContainer').append(html);
    companySalesIndex++;
}

function removeCompanySales(index) {
    $(`.company-sales-row[data-index="${index}"]`).remove();
    calculateTotalSales();
}

function calculateTotalSales() {
    let total = 0;
    $('.company-sales-value').each(function() {
        total += parseFloat($(this).val()) || 0;
    });
    $('#totalSalesValue').text(total.toFixed(0));
    calculateCostPercentage();
}

function calculateCostPercentage() {
    const totalSales = parseFloat($('#totalSalesValue').text()) || 0;
    const totalCost = parseFloat($('#total_cost').text()) || 0;

    if (totalSales > 0) {
        const percentage = (totalCost / totalSales) * 100;
        $('#cost_percentage').text(percentage.toFixed(2));
    }
}
</script>
@endpush
