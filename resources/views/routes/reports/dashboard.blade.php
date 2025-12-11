@extends('layouts.admin')

@section('title', 'Reports Dashboard')
@section('page-title', 'Reports & Analytics')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
<li class="breadcrumb-item active">Reports</li>
@endsection

@section('content')

<!-- Stats Row -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $stats['total_routes'] }}</h3>
                <p>Total Completed Routes</p>
            </div>
            <div class="icon">
                <i class="fas fa-route"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ number_format($stats['total_distance'], 0) }} <small>km</small></h3>
                <p>Total Distance Covered</p>
            </div>
            <div class="icon">
                <i class="fas fa-road"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>Rs. {{ number_format($stats['total_cost'], 0) }}</h3>
                <p>Total Costs</p>
            </div>
            <div class="icon">
                <i class="fas fa-money-bill-wave"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>Rs. {{ number_format($stats['total_sales'], 0) }}</h3>
                <p>Total Sales</p>
            </div>
            <div class="icon">
                <i class="fas fa-chart-line"></i>
            </div>
        </div>
    </div>
</div>

<!-- Reports Menu -->
<div class="row">
    <div class="col-md-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-bar"></i> Available Reports</h3>
            </div>
            <div class="card-body">
                <div class="row">

                    <!-- Estimated vs Actual -->
                    <div class="col-md-4 mb-3">
                        <div class="info-box bg-gradient-info">
                            <span class="info-box-icon"><i class="fas fa-balance-scale"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Variance Analysis</span>
                                <span class="info-box-number">Estimated vs Actual</span>
                                <a href="{{ route('routes.reports.estimatedVsActual') }}" class="btn btn-sm btn-light mt-2">
                                    View Report <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Company Performance -->
                    <div class="col-md-4 mb-3">
                        <div class="info-box bg-gradient-success">
                            <span class="info-box-icon"><i class="fas fa-building"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">By Company</span>
                                <span class="info-box-number">Company Performance</span>
                                <a href="{{ route('routes.reports.companyPerformance') }}" class="btn btn-sm btn-light mt-2">
                                    View Report <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Driver Performance -->
                    <div class="col-md-4 mb-3">
                        <div class="info-box bg-gradient-warning">
                            <span class="info-box-icon"><i class="fas fa-user-tie"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">By Driver</span>
                                <span class="info-box-number">Driver Performance</span>
                                <a href="{{ route('routes.reports.driverPerformance') }}" class="btn btn-sm btn-light mt-2">
                                    View Report <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Profitability -->
                    <div class="col-md-4 mb-3">
                        <div class="info-box bg-gradient-primary">
                            <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Profit Analysis</span>
                                <span class="info-box-number">Profitability Report</span>
                                <a href="{{ route('routes.reports.profitability') }}" class="btn btn-sm btn-light mt-2">
                                    View Report <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Vehicle Usage -->
                    <div class="col-md-4 mb-3">
                        <div class="info-box bg-gradient-secondary">
                            <span class="info-box-icon"><i class="fas fa-truck"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">By Vehicle</span>
                                <span class="info-box-number">Vehicle Usage</span>
                                <a href="{{ route('routes.reports.vehicleUsage') }}" class="btn btn-sm btn-light mt-2">
                                    View Report <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@endsection
