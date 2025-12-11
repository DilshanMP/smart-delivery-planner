@extends('layouts.admin')

@section('title', 'Add Route Costs')
@section('page-title', 'Add Detailed Costs')

@section('content')

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-receipt"></i> Route: {{ $route->route_code }}
                </h3>
            </div>
            <form action="{{ route('routes.actual.storeCosts', $route) }}" method="POST">
                @csrf
                <div class="card-body">

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Add detailed cost breakdown for this route.</strong>
                        You can add multiple cost items (fuel receipts, meals, tolls, etc.)
                    </div>

                    <div id="costItemsContainer">
                        <div class="cost-item" data-index="0">
                            <div class="card card-outline card-primary">
                                <div class="card-header">
                                    <h5 class="card-title">Cost Item #1</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Cost Type <span class="text-danger">*</span></label>
                                                <select name="cost_items[0][cost_type]" class="form-control" required>
                                                    <option value="">Select Type</option>
                                                    <option value="fuel">Fuel</option>
                                                    <option value="meal">Meal</option>
                                                    <option value="accommodation">Accommodation</option>
                                                    <option value="toll">Toll/Parking</option>
                                                    <option value="maintenance">Maintenance</option>
                                                    <option value="other">Other</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <label>Description</label>
                                                <input type="text" name="cost_items[0][description]" class="form-control"
                                                       placeholder="E.g., Fuel - Colombo to Galle">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Amount (LKR) <span class="text-danger">*</span></label>
                                                <input type="number" step="0.01" name="cost_items[0][actual_amount]"
                                                       class="form-control cost-amount" min="0" required
                                                       onchange="calculateTotal()">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Receipt Number</label>
                                                <input type="text" name="cost_items[0][receipt_number]"
                                                       class="form-control" placeholder="Optional">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Expense Date</label>
                                                <input type="date" name="cost_items[0][expense_date]"
                                                       class="form-control" value="{{ date('Y-m-d') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-success" onclick="addCostItem()">
                        <i class="fas fa-plus"></i> Add Another Cost Item
                    </button>

                    <hr>

                    <div class="row">
                        <div class="col-md-12">
                            <h4>Total Actual Cost: Rs. <span id="totalCost">0</span></h4>
                        </div>
                    </div>

                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Costs
                    </button>
                    <a href="{{ route('routes.show', $route) }}" class="btn btn-default">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Route Summary</h3>
            </div>
            <div class="card-body">
                <dl>
                    <dt>Route Code:</dt>
                    <dd>{{ $route->route_code }}</dd>

                    <dt>Date:</dt>
                    <dd>{{ $route->route_date ? $route->route_date->format('d M Y') : 'N/A' }}</dd>

                    <dt>Driver:</dt>
                    <dd>{{ $route->driver->name ?? 'N/A' }}</dd>

                    <dt>Estimated Cost:</dt>
                    <dd>Rs. {{ number_format($route->estimated_total_cost ?? 0, 2) }}</dd>

                    <dt>Current Actual Cost:</dt>
                    <dd>Rs. {{ number_format($route->actual_total_cost ?? 0, 2) }}</dd>
                </dl>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let costItemIndex = 1;

function addCostItem() {
    const html = `
        <div class="cost-item" data-index="${costItemIndex}">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h5 class="card-title">Cost Item #${costItemIndex + 1}</h5>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" onclick="removeCostItem(${costItemIndex})">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Cost Type <span class="text-danger">*</span></label>
                                <select name="cost_items[${costItemIndex}][cost_type]" class="form-control" required>
                                    <option value="">Select Type</option>
                                    <option value="fuel">Fuel</option>
                                    <option value="meal">Meal</option>
                                    <option value="accommodation">Accommodation</option>
                                    <option value="toll">Toll/Parking</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Description</label>
                                <input type="text" name="cost_items[${costItemIndex}][description]" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Amount (LKR) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="cost_items[${costItemIndex}][actual_amount]"
                                       class="form-control cost-amount" min="0" required
                                       onchange="calculateTotal()">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Receipt Number</label>
                                <input type="text" name="cost_items[${costItemIndex}][receipt_number]" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Expense Date</label>
                                <input type="date" name="cost_items[${costItemIndex}][expense_date]"
                                       class="form-control" value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    $('#costItemsContainer').append(html);
    costItemIndex++;
}

function removeCostItem(index) {
    $(`.cost-item[data-index="${index}"]`).remove();
    calculateTotal();
}

function calculateTotal() {
    let total = 0;
    $('.cost-amount').each(function() {
        total += parseFloat($(this).val()) || 0;
    });
    $('#totalCost').text(total.toFixed(2));
}
</script>
@endpush
