@extends('layouts.app')

@section('title', 'AI Cost Prediction')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            
            <!-- Header -->
            <div class="text-center mb-5">
                <h1 class="display-4 fw-bold">AI Cost Prediction</h1>
                <p class="lead text-muted">Predict delivery costs using machine learning (95%+ accuracy)</p>
            </div>

            <!-- API Status Alert -->
            <div id="apiStatus" class="alert d-none"></div>

            <!-- Cost Prediction Form -->
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Route Details</h5>
                </div>
                <div class="card-body">
                    <form id="costPredictionForm">
                        
                        <!-- Route Information -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Total Distance (km)</label>
                                <input type="number" class="form-control" name="total_distance_km" 
                                       step="0.01" min="1" required placeholder="e.g., 125.5">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Number of Stops</label>
                                <input type="number" class="form-control" name="total_stops" 
                                       min="1" required placeholder="e.g., 5">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Number of Companies</label>
                                <input type="number" class="form-control" name="num_companies" 
                                       min="1" required placeholder="e.g., 3">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Total Sales Value (LKR)</label>
                                <input type="number" class="form-control" name="total_sales_value" 
                                       step="0.01" min="0" required placeholder="e.g., 250000">
                            </div>
                        </div>

                        <!-- Vehicle Information -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Vehicle Type</label>
                                <select class="form-select" name="vehicle_type" required>
                                    <option value="">Select vehicle...</option>
                                    <option value="Lorry">Lorry</option>
                                    <option value="Van">Van</option>
                                    <option value="Truck">Truck</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Day of Week</label>
                                <select class="form-select" name="day_of_week" required>
                                    <option value="">Select day...</option>
                                    <option value="Monday">Monday</option>
                                    <option value="Tuesday">Tuesday</option>
                                    <option value="Wednesday">Wednesday</option>
                                    <option value="Thursday">Thursday</option>
                                    <option value="Friday">Friday</option>
                                    <option value="Saturday">Saturday</option>
                                    <option value="Sunday">Sunday</option>
                                </select>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg" id="predictBtn">
                                <i class="bi bi-calculator me-2"></i>Predict Cost
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Results Card (Hidden initially) -->
            <div id="resultsCard" class="card shadow mt-4 d-none">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Prediction Results</h5>
                </div>
                <div class="card-body">
                    
                    <!-- Main Prediction -->
                    <div class="text-center mb-4">
                        <h2 class="display-3 fw-bold text-primary" id="predictedCost">-</h2>
                        <p class="text-muted">Predicted Delivery Cost</p>
                    </div>

                    <!-- Metrics Grid -->
                    <div class="row text-center">
                        <div class="col-md-4 mb-3">
                            <div class="p-3 border rounded">
                                <h4 class="fw-bold text-warning" id="costPercentage">-</h4>
                                <small class="text-muted">Cost as % of Sales</small>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="p-3 border rounded">
                                <h4 class="fw-bold text-info" id="modelConfidence">-</h4>
                                <small class="text-muted">Model Confidence</small>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="p-3 border rounded">
                                <h4 class="fw-bold text-secondary" id="confidenceRange">-</h4>
                                <small class="text-muted">Confidence Interval</small>
                            </div>
                        </div>
                    </div>

                    <!-- Recommendation -->
                    <div class="alert alert-info mt-3" id="recommendation"></div>

                </div>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
// Check API status on load
document.addEventListener('DOMContentLoaded', function() {
    checkAPIStatus();
});

// API Status Check
async function checkAPIStatus() {
    try {
        const response = await fetch('/ai/api-status');
        const data = await response.json();
        
        const statusDiv = document.getElementById('apiStatus');
        if (data.available) {
            statusDiv.className = 'alert alert-success';
            statusDiv.innerHTML = '<i class="bi bi-check-circle me-2"></i>AI API is online and ready';
        } else {
            statusDiv.className = 'alert alert-danger';
            statusDiv.innerHTML = '<i class="bi bi-x-circle me-2"></i>AI API is offline. Please start the Flask server.';
        }
        statusDiv.classList.remove('d-none');
    } catch (error) {
        console.error('Status check failed:', error);
    }
}

// Form Submission
document.getElementById('costPredictionForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const predictBtn = document.getElementById('predictBtn');
    const resultsCard = document.getElementById('resultsCard');
    
    // Disable button and show loading
    predictBtn.disabled = true;
    predictBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Predicting...';
    resultsCard.classList.add('d-none');
    
    // Collect form data
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    // Convert to proper types
    data.total_distance_km = parseFloat(data.total_distance_km);
    data.total_stops = parseInt(data.total_stops);
    data.num_companies = parseInt(data.num_companies);
    data.total_sales_value = parseFloat(data.total_sales_value);
    
    try {
        // Call API
        const response = await fetch('/ai/predict-cost', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Display results
            displayResults(result.prediction);
        } else {
            alert('Prediction failed: ' + result.error);
        }
        
    } catch (error) {
        alert('Error: ' + error.message);
    } finally {
        // Re-enable button
        predictBtn.disabled = false;
        predictBtn.innerHTML = '<i class="bi bi-calculator me-2"></i>Predict Cost';
    }
});

// Display Results
function displayResults(prediction) {
    // Update values
    document.getElementById('predictedCost').textContent = 
        'LKR ' + prediction.cost.toLocaleString('en-US', {minimumFractionDigits: 2});
    
    document.getElementById('costPercentage').textContent = 
        prediction.cost_percentage.toFixed(2) + '%';
    
    document.getElementById('modelConfidence').textContent = 
        prediction.model_confidence.toFixed(1) + '%';
    
    document.getElementById('confidenceRange').textContent = 
        'LKR ' + prediction.confidence_interval.lower.toLocaleString() + 
        ' - ' + prediction.confidence_interval.upper.toLocaleString();
    
    // Recommendation
    let recommendation = '';
    if (prediction.cost_percentage < 5.5) {
        recommendation = '<strong>Excellent!</strong> Cost is below target (5.5%). Route is highly efficient.';
    } else if (prediction.cost_percentage < 7) {
        recommendation = '<strong>Good.</strong> Cost is within acceptable range. Minor optimization possible.';
    } else {
        recommendation = '<strong>High Cost Alert!</strong> Consider route optimization to reduce costs.';
    }
    
    document.getElementById('recommendation').innerHTML = recommendation;
    
    // Show results card
    document.getElementById('resultsCard').classList.remove('d-none');
    
    // Scroll to results
    document.getElementById('resultsCard').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}
</script>
@endpush

@endsection
