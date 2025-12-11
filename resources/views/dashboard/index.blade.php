@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('breadcrumb')
<li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')

<!-- Stats Cards Row -->
<div class="row">
    <!-- Total Routes -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $stats['total_routes'] }}</h3>
                <p>Total Routes</p>
            </div>
            <div class="icon">
                <i class="fas fa-route"></i>
            </div>
            <a href="{{ route('routes.index') }}" class="small-box-footer">
                More info <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <!-- Active Vehicles -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $stats['active_vehicles'] }}</h3>
                <p>Active Vehicles</p>
            </div>
            <div class="icon">
                <i class="fas fa-truck"></i>
            </div>
            <a href="#" class="small-box-footer">
                More info <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <!-- Active Drivers -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $stats['active_drivers'] }}</h3>
                <p>Active Drivers</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-tie"></i>
            </div>
            <a href="#" class="small-box-footer">
                More info <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <!-- ✅ NEW: Current Month Cost Percentage -->
    <div class="col-lg-3 col-6">
        <div class="small-box {{ $stats['current_month_cost_percentage'] <= 10 ? 'bg-success' : ($stats['current_month_cost_percentage'] <= 15 ? 'bg-warning' : 'bg-danger') }}">
            <div class="inner">
                <h3>{{ $stats['current_month_cost_percentage'] }}%</h3>
                <p>Monthly Cost %</p>
            </div>
            <div class="icon">
                <i class="fas fa-percentage"></i>
            </div>
            <a href="#" class="small-box-footer">
                @if($stats['current_month_cost_percentage'] <= 10)
                    Excellent Efficiency <i class="fas fa-check-circle"></i>
                @elseif($stats['current_month_cost_percentage'] <= 15)
                    Good Performance <i class="fas fa-info-circle"></i>
                @else
                    Needs Attention <i class="fas fa-exclamation-triangle"></i>
                @endif
            </a>
        </div>
    </div>
</div>

<!-- Cost Performance Row -->
<div class="row">
    <!-- Current Month Summary -->
    <div class="col-lg-4">
        <div class="info-box bg-gradient-info">
            <span class="info-box-icon"><i class="fas fa-calendar-alt"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Current Month</span>
                <span class="info-box-number">{{ now()->format('F Y') }}</span>
                <div class="progress">
                    <div class="progress-bar" style="width: {{ min($stats['current_month_cost_percentage'], 100) }}%"></div>
                </div>
                <span class="progress-description">
                    Cost: Rs. {{ number_format($stats['total_cost'], 0) }} |
                    Sales: Rs. {{ number_format($stats['current_month_sales'], 0) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Completed Routes -->
    <div class="col-lg-4">
        <div class="info-box bg-gradient-success">
            <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Completed Routes</span>
                <span class="info-box-number">{{ $stats['completed_routes'] }}</span>
                <div class="progress">
                    <div class="progress-bar" style="width: {{ $stats['total_routes'] > 0 ? ($stats['completed_routes'] / $stats['total_routes']) * 100 : 0 }}%"></div>
                </div>
                <span class="progress-description">
                    {{ $stats['total_routes'] > 0 ? round(($stats['completed_routes'] / $stats['total_routes']) * 100, 1) : 0 }}% of total routes
                </span>
            </div>
        </div>
    </div>

    <!-- Pending Routes -->
    <div class="col-lg-4">
        <div class="info-box bg-gradient-warning">
            <span class="info-box-icon"><i class="fas fa-clock"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Pending Routes</span>
                <span class="info-box-number">{{ $stats['planned_routes'] + $stats['in_progress_routes'] }}</span>
                <div class="progress">
                    <div class="progress-bar" style="width: {{ $stats['total_routes'] > 0 ? (($stats['planned_routes'] + $stats['in_progress_routes']) / $stats['total_routes']) * 100 : 0 }}%"></div>
                </div>
                <span class="progress-description">
                    Planned: {{ $stats['planned_routes'] }} | In Progress: {{ $stats['in_progress_routes'] }}
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row">
    <!-- Route Status Chart -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-pie mr-1"></i>
                    Routes by Status
                </h3>
            </div>
            <div class="card-body">
                <canvas id="routeStatusChart" style="min-height: 250px; height: 250px; max-height: 250px;"></canvas>
            </div>
        </div>
    </div>

    <!-- ✅ NEW: Monthly Cost Percentage Trend (Last 6 Months) -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-line mr-1"></i>
                    Monthly Cost Percentage Trend (Last 6 Months)
                </h3>
            </div>
            <div class="card-body">
                <canvas id="monthlyCostPercentageChart" style="min-height: 250px; height: 250px; max-height: 250px;"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- ✅ NEW: Yearly Cost Percentage Chart -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-area mr-1"></i>
                    Yearly Cost Percentage - {{ now()->year }} (Monthly Breakdown)
                </h3>
            </div>
            <div class="card-body">
                <canvas id="yearlyCostPercentageChart" style="min-height: 300px; height: 300px; max-height: 300px;"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Best & Worst Routes Row -->
<div class="row">
    <!-- ✅ NEW: Best Routes (Lowest Cost %) -->
    <div class="col-md-6">
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-trophy mr-1"></i>
                    Top 5 Most Efficient Routes
                </h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Route</th>
                            <th>Company</th>
                            <th>Sales</th>
                            <th>Cost %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($best_routes as $route)
                        <tr>
                            <td><strong>{{ $route->route_code }}</strong></td>
                            <td>{{ $route->company->name ?? 'N/A' }}</td>
                            <td>Rs. {{ number_format($route->total_sales, 0) }}</td>
                            <td>
                                <span class="badge badge-success">
                                    <i class="fas fa-check"></i> {{ $route->cost_percentage }}%
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">No completed routes yet</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ✅ NEW: Worst Routes (Highest Cost %) -->
    <div class="col-md-6">
        <div class="card card-danger">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    Top 5 Routes Needing Attention
                </h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Route</th>
                            <th>Company</th>
                            <th>Sales</th>
                            <th>Cost %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($worst_routes as $route)
                        <tr>
                            <td><strong>{{ $route->route_code }}</strong></td>
                            <td>{{ $route->company->name ?? 'N/A' }}</td>
                            <td>Rs. {{ number_format($route->total_sales, 0) }}</td>
                            <td>
                                <span class="badge {{ $route->cost_percentage > 15 ? 'badge-danger' : 'badge-warning' }}">
                                    <i class="fas fa-exclamation-triangle"></i> {{ $route->cost_percentage }}%
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">No completed routes yet</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Recent Routes Table with Cost % -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list mr-2"></i>
                    Recent Routes
                </h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>Route Code</th>
                            <th>Company</th>
                            <th>Route Date</th>
                            <th>Driver</th>
                            <th>Status</th>
                            <th>Sales</th>
                            <th>Cost</th>
                            <th>Cost %</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recent_routes as $route)
                        <tr>
                            <td><strong>{{ $route->route_code }}</strong></td>
                            <td>{{ $route->company->name ?? 'N/A' }}</td>
                            <td>{{ $route->route_date ? $route->route_date->format('d M Y') : 'N/A' }}</td>
                            <td>{{ $route->driver->name ?? 'N/A' }}</td>
                            <td>
                                @if($route->status == 'completed')
                                    <span class="badge badge-success">Completed</span>
                                @elseif($route->status == 'in_progress')
                                    <span class="badge badge-info">In Progress</span>
                                @elseif($route->status == 'planned')
                                    <span class="badge badge-warning">Planned</span>
                                @else
                                    <span class="badge badge-secondary">{{ ucfirst($route->status) }}</span>
                                @endif
                            </td>
                            <td>Rs. {{ number_format($route->total_sales, 0) }}</td>
                            <td>Rs. {{ number_format($route->estimated_total_cost ?? 0, 0) }}</td>
                            <td>
                                @if($route->status == 'completed' && $route->total_sales > 0)
                                    @if($route->cost_percentage <= 10)
                                        <span class="badge badge-success">
                                            <i class="fas fa-check"></i> {{ $route->cost_percentage }}%
                                        </span>
                                    @elseif($route->cost_percentage <= 15)
                                        <span class="badge badge-warning">
                                            <i class="fas fa-info-circle"></i> {{ $route->cost_percentage }}%
                                        </span>
                                    @else
                                        <span class="badge badge-danger">
                                            <i class="fas fa-exclamation-triangle"></i> {{ $route->cost_percentage }}%
                                        </span>
                                    @endif
                                @else
                                    <span class="badge badge-secondary">N/A</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('routes.show', $route) }}" class="btn btn-sm btn-info" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                No routes found. <a href="{{ route('routes.create') }}">Create your first route</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($recent_routes->count() > 0)
            <div class="card-footer">
                <a href="{{ route('routes.index') }}" class="btn btn-primary">
                    <i class="fas fa-list"></i> View All Routes
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

@endsection

@push('scripts')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
$(document).ready(function() {

    // Route Status Pie Chart
    var ctxPie = document.getElementById('routeStatusChart').getContext('2d');
    var routeStatusChart = new Chart(ctxPie, {
        type: 'pie',
        data: {
            labels: ['Planned', 'In Progress', 'Completed'],
            datasets: [{
                data: [
                    {{ $stats['planned_routes'] }},
                    {{ $stats['in_progress_routes'] }},
                    {{ $stats['completed_routes'] }}
                ],
                backgroundColor: [
                    '#ffc107',
                    '#17a2b8',
                    '#28a745'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // ✅ NEW: Monthly Cost Percentage Trend Chart (Last 6 Months)
    var ctxMonthlyCost = document.getElementById('monthlyCostPercentageChart').getContext('2d');
    var monthlyCostPercentageChart = new Chart(ctxMonthlyCost, {
        type: 'line',
        data: {
            labels: {!! json_encode($monthly_labels) !!},
            datasets: [
                {
                    label: 'Cost %',
                    data: {!! json_encode($monthly_cost_percentage) !!},
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'y'
                },
                {
                    label: 'Routes Count',
                    data: {!! json_encode($monthly_data) !!},
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        afterLabel: function(context) {
                            if (context.datasetIndex === 0) {
                                return 'Target: 8-12%';
                            }
                        }
                    }
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Cost Percentage (%)'
                    },
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Routes Count'
                    },
                    grid: {
                        drawOnChartArea: false,
                    }
                }
            }
        }
    });

    // ✅ NEW: Yearly Cost Percentage Chart (Current Year)
    var ctxYearlyCost = document.getElementById('yearlyCostPercentageChart').getContext('2d');
    var yearlyCostPercentageChart = new Chart(ctxYearlyCost, {
        type: 'bar',
        data: {
            labels: {!! json_encode($yearly_labels) !!},
            datasets: [{
                label: 'Cost Percentage (%)',
                data: {!! json_encode($yearly_cost_percentage) !!},
                backgroundColor: function(context) {
                    var value = context.parsed.y;
                    if (value <= 10) return '#28a745'; // Green - Excellent
                    if (value <= 15) return '#ffc107'; // Yellow - Good
                    return '#dc3545'; // Red - Needs attention
                },
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        afterLabel: function(context) {
                            var value = context.parsed.y;
                            if (value <= 10) return '✓ Excellent';
                            if (value <= 15) return '⚠ Good';
                            return '⚠ Needs Attention';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Cost Percentage (%)'
                    },
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            }
        }
    });
});
</script>
@endpush
