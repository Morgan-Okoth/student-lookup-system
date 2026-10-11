@extends('layouts.admin')

@section('title', 'Add Course')
@section('page-title', 'Add Course')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-7">

        <div class="d-flex align-items-center gap-2 mb-4">
            <a href="{{ route('admin.courses.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h5 class="fw-bold mb-0"><i class="bi bi-plus-circle-fill me-2 text-primary"></i>Add New Course</h5>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('admin.courses.store') }}" novalidate>
                    @csrf

                    <div class="mb-3">
                        <label for="course_code" class="form-label fw-semibold">Course Code <span class="text-danger">*</span></label>
                        <input type="text" id="course_code" name="course_code"
                               class="form-control @error('course_code') is-invalid @enderror"
                               value="{{ old('course_code') }}" placeholder="e.g. CS101" autofocus>
                        @error('course_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="course_name" class="form-label fw-semibold">Course Name <span class="text-danger">*</span></label>
                        <input type="text" id="course_name" name="course_name"
                               class="form-control @error('course_name') is-invalid @enderror"
                               value="{{ old('course_name') }}" placeholder="e.g. Introduction to Computer Science">
                        @error('course_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="credit_hours" class="form-label fw-semibold">Credit Hours <span class="text-danger">*</span></label>
                        <input type="number" id="credit_hours" name="credit_hours"
                               class="form-control @error('credit_hours') is-invalid @enderror"
                               value="{{ old('credit_hours') }}" placeholder="e.g. 3" min="1">
                        @error('credit_hours')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle-fill me-1"></i>Save Course
                        </button>
                        <a href="{{ route('admin.courses.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
