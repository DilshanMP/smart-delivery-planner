@extends('layouts.admin')

@section('title', 'Company Performance Report')
@section('page-title', 'Company Performance Analysis')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
<li class="breadcrumb-item"><a href="{{ route('routes.reports.dashboard') }}">Reports</a></li>
<li class="breadcrumb-item active">Company Performance</li>
@endsection

@section('content')

<div class="row">
    <div class="col-12">

        <!-- Filters Card -->
        <div class="card card-primary collapsed-card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-filter"></i> Filters</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('routes.reports.companyPerformance') }}" method="GET">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Date From</label>
                                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Date To</label>
                                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-search"></i> Apply Filters
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Report Table -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-building"></i> Performance by Company</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" onclick="window.print()">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>Company</th>
                            <th>Total Routes</th>
                            <th>Total Stops</th>
                            <th>Total Sales (LKR)</th>
                            <th>Total Cost (LKR)</th>
                            <th>Total Profit (LKR)</th>
                            <th>Avg Margin %</th>
                            <th>Performance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($companyPerformance as $company)
                        <tr>
                            <td><strong>{{ $company->company_name }}</strong></td>
                            <td>{{ $company->total_routes }}</td>
                            <td>{{ number_format($company->total_stops ?? 0) }}</td>
                            <td>Rs. {{ number_format($company->total_sales ?? 0, 0) }}</td>
                            <td>Rs. {{ number_format($company->total_cost ?? 0, 0) }}</td>
                            <td class="{{ ($company->total_profit ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                <strong>Rs. {{ number_format($company->total_profit ?? 0, 0) }}</strong>
                            </td>
                            <td>
                                @php
                                    $margin = $company->avg_profit_margin ?? 0;
                                @endphp
                                <span class="badge badge-lg {{ $margin > 80 ? 'badge-success' : ($margin > 70 ? 'badge-warning' : 'badge-danger') }}">
                                    {{ number_format($margin, 1) }}%
                                </span>
                            </td>
                            <td>
                                @php
                                    $margin = $company->avg_profit_margin ?? 0;
                                @endphp
                                @if($margin > 85)
                                    <span class="badge badge-success">
                                        <i class="fas fa-star"></i> Excellent
                                    </span>
                                @elseif($margin > 75)
                                    <span class="badge badge-info">
                                        <i class="fas fa-check"></i> Good
                                    </span>
                                @elseif($margin > 60)
                                    <span class="badge badge-warning">
                                        <i class="fas fa-exclamation-triangle"></i> Average
                                    </span>
                                @else
                                    <span class="badge badge-danger">
                                        <i class="fas fa-times"></i> Needs Review
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-info-circle fa-2x mb-2"></i>
                                <p>No data available. Please complete some routes first.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($companyPerformance->count() > 0)
                    <tfoot class="thead-light">
                        <tr>
                            <th>TOTAL</th>
                            <th>{{ $companyPerformance->sum('total_routes') }}</th>
                            <th>{{ number_format($companyPerformance->sum('total_stops') ?? 0) }}</th>
                            <th>Rs. {{ number_format($companyPerformance->sum('total_sales') ?? 0, 0) }}</th>
                            <th>Rs. {{ number_format($companyPerformance->sum('total_cost') ?? 0, 0) }}</th>
                            <th class="text-success">
                                <strong>Rs. {{ number_format($companyPerformance->sum('total_profit') ?? 0, 0) }}</strong>
                            </th>
                            <th colspan="2">
                                @php
                                    $totalSales = $companyPerformance->sum('total_sales') ?? 0;
                                    $totalProfit = $companyPerformance->sum('total_profit') ?? 0;
                                    $overallMargin = $totalSales > 0 ? ($totalProfit / $totalSales) * 100 : 0;
                                @endphp
                                Overall: {{ number_format($overallMargin, 1) }}%
                            </th>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>

    </div>
</div>

@endsection
