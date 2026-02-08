@extends('layouts.admin')

@section('title', 'Profitability Report')
@section('page-title', 'Route Profitability Analysis')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
<li class="breadcrumb-item"><a href="{{ route('routes.reports.dashboard') }}">Reports</a></li>
<li class="breadcrumb-item active">Profitability</li>
@endsection

@section('content')

<div class="row">
    <div class="col-12">

        <!-- Filters -->
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
                <form action="{{ route('routes.reports.profitability') }}" method="GET">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Company</label>
                                <select name="company_id" class="form-control">
                                    <option value="">All Companies</option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                                            {{ $company->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Date From</label>
                                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Date To</label>
                                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-search"></i> Apply
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Report Table -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-dollar-sign"></i> Profitability by Route</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-bordered table-hover table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th>Route</th>
                            <th>Date</th>
                            <th>Company</th>
                            <th>Sales (LKR)</th>
                            <th>Returns (LKR)</th>
                            <th>Net Sales (LKR)</th>
                            <th>Cost (LKR)</th>
                            <th>Net Profit (LKR)</th>
                            <th>Margin %</th>
                            <th>Cost %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($routes as $route)
                        @php
                            $totalSales = $route->companyAllocations->sum('total_sales_value');
                            $netSales = $totalSales - ($route->return_sales_value ?? 0);
                            $totalCost = $route->actual_total_cost ?? $route->estimated_total_cost;
                            $netProfit = $netSales - $totalCost;
                            $profitMargin = $netSales > 0 ? ($netProfit / $netSales) * 100 : 0;
                            $costPercentage = $totalSales > 0 ? ($totalCost / $totalSales) * 100 : 0;
                        @endphp
                        <tr>
                            <td><strong>{{ $route->route_code }}</strong></td>
                            <td>{{ $route->route_date ? $route->route_date->format('d M Y') : 'N/A' }}</td>
                            <td>{{ $route->company->name ?? 'N/A' }}</td>
                            <td>{{ number_format($totalSales, 0) }}</td>
                            <td>{{ number_format($route->return_sales_value ?? 0, 0) }}</td>
                            <td>{{ number_format($netSales, 0) }}</td>
                            <td>{{ number_format($totalCost, 0) }}</td>
                            <td class="{{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">
                                <strong>{{ number_format($netProfit, 0) }}</strong>
                            </td>
                            <td>
                                <span class="badge {{ $profitMargin > 85 ? 'badge-success' : ($profitMargin > 70 ? 'badge-warning' : 'badge-danger') }}">
                                    {{ number_format($profitMargin, 1) }}%
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $costPercentage < 10 ? 'badge-success' : ($costPercentage < 15 ? 'badge-warning' : 'badge-danger') }}">
                                    {{ number_format($costPercentage, 1) }}%
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                <i class="fas fa-info-circle fa-2x mb-2"></i>
                                <p>No profitability data available</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($routes->hasPages())
            <div class="card-footer">
                {{ $routes->links() }}
            </div>
            @endif
        </div>

    </div>
</div>

@endsection
