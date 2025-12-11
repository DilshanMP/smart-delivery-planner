@extends('layouts.admin')

@section('title', 'Create Company')
@section('page-title', 'Create New Company')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
<li class="breadcrumb-item"><a href="{{ route('companies.index') }}">Companies</a></li>
<li class="breadcrumb-item active">Create</li>
@endsection

@section('content')

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Company Information</h3>
            </div>

            <form action="{{ route('companies.store') }}" method="POST">
                @csrf

                <div class="card-body">

                    <!-- Company Name -->
                    <div class="form-group">
                        <label for="name">Company Name <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control @error('name') is-invalid @enderror"
                               id="name"
                               name="name"
                               value="{{ old('name') }}"
                               placeholder="Enter company name"
                               required>
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Company Code -->
                    <div class="form-group">
                        <label for="code">Company Code <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control @error('code') is-invalid @enderror"
                               id="code"
                               name="code"
                               value="{{ old('code') }}"
                               placeholder="Enter company code (e.g., COMP001)"
                               required>
                        @error('code')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">Unique code for identification</small>
                    </div>

                    <!-- Address -->
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea class="form-control @error('address') is-invalid @enderror"
                                  id="address"
                                  name="address"
                                  rows="3"
                                  placeholder="Enter company address">{{ old('address') }}</textarea>
                        @error('address')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- City -->
                    <div class="form-group">
                        <label for="city">City</label>
                        <input type="text"
                               class="form-control @error('city') is-invalid @enderror"
                               id="city"
                               name="city"
                               value="{{ old('city') }}"
                               placeholder="Enter city">
                        @error('city')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <!-- Phone -->
                            <div class="form-group">
                                <label for="phone">Phone</label>
                                <input type="text"
                                       class="form-control @error('phone') is-invalid @enderror"
                                       id="phone"
                                       name="phone"
                                       value="{{ old('phone') }}"
                                       placeholder="Enter phone number">
                                @error('phone')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <!-- Email -->
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       id="email"
                                       name="email"
                                       value="{{ old('email') }}"
                                       placeholder="Enter email address">
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Contact Person -->
                    <div class="form-group">
                        <label for="contact_person">Contact Person</label>
                        <input type="text"
                               class="form-control @error('contact_person') is-invalid @enderror"
                               id="contact_person"
                               name="contact_person"
                               value="{{ old('contact_person') }}"
                               placeholder="Enter contact person name">
                        @error('contact_person')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox"
                                   class="custom-control-input"
                                   id="is_active"
                                   name="is_active"
                                   value="1"
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_active">Active</label>
                        </div>
                        <small class="form-text text-muted">Inactive companies won't appear in route planning</small>
                    </div>

                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Company
                    </button>
                    <a href="{{ route('companies.index') }}" class="btn btn-default">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
