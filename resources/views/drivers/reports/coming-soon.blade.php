@extends('layouts.admin')

@section('title', 'Coming Soon')
@section('page-title', 'Feature Coming Soon')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
<li class="breadcrumb-item active">Coming Soon</li>
@endsection

@section('content')

<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-body box-profile">
                <div class="text-center py-5">
                    <i class="fas fa-hammer fa-5x text-muted mb-4"></i>
                    <h3 class="profile-username">This Feature is Under Development</h3>
                    <p class="text-muted">
                        We're working hard to bring you this feature!<br>
                        It will be available soon.
                    </p>
                    <hr>
                    <a href="{{ route('dashboard') }}" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
