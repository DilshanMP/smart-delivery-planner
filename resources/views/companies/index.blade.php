@extends('layouts.admin')

@section('title', 'Companies')
@section('page-title', 'Companies')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
<li class="breadcrumb-item active">Companies</li>
@endsection

@section('content')

<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Companies</h3>
        <div class="card-tools">
            @can('create', App\Models\Company::class)
            <a href="{{ route('companies.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Create New Company
            </a>
            @endcan
        </div>
    </div>
    <div class="card-body">
        <table id="companiesTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th width="5%">ID</th>
                    <th width="15%">Code</th>
                    <th width="20%">Company Name</th>
                    <th width="15%">City</th>
                    <th width="12%">Contact Person</th>
                    <th width="10%">Phone</th>
                    <th width="8%" class="text-center">Status</th>
                    <th width="8%" class="text-center">Users</th>
                    <th width="7%" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($companies as $company)
                <tr>
                    <td>{{ $company->id }}</td>
                    <td><strong>{{ $company->code }}</strong></td>
                    <td>
                        <i class="fas fa-building text-primary"></i>
                        {{ $company->name }}
                    </td>
                    <td>{{ $company->city ?? '-' }}</td>
                    <td>{{ $company->contact_person ?? '-' }}</td>
                    <td>{{ $company->phone ?? '-' }}</td>
                    <td class="text-center">
                        @if($company->is_active)
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-danger">Inactive</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="badge badge-info">{{ $company->users_count }}</span>
                    </td>
                    <td class="text-center">
                        @can('view', $company)
                        <a href="{{ route('companies.show', $company) }}" class="btn btn-sm btn-info" title="View Details">
                            <i class="fas fa-eye"></i>
                        </a>
                        @endcan

                        @can('update', $company)
                        <a href="{{ route('companies.edit', $company) }}" class="btn btn-sm btn-warning" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        @endcan

                        @can('delete', $company)
                        <form action="{{ route('companies.destroy', $company) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Are you sure you want to delete this company?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#companiesTable').DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "pageLength": 25,
        "order": [[0, 'desc']],
        "columnDefs": [
            { "orderable": false, "targets": [8] }
        ]
    });
});
</script>
@endpush
