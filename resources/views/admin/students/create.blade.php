@extends('layouts.admin')

@section('title', 'Add Student')
@section('page-title', 'Add Student')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-7">

        <div class="d-flex align-items-center gap-2 mb-4">
            <a href="{{ route('admin.students.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h5 class="fw-bold mb-0"><i class="bi bi-person-plus-fill me-2 text-primary"></i>Add New Student</h5>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('admin.students.store') }}" novalidate>
                    @csrf

                    <div class="mb-3">
                        <label for="full_name" class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" id="full_name" name="full_name"
                               class="form-control @error('full_name') is-invalid @enderror"
                               value="{{ old('full_name') }}" placeholder="e.g. Jane Wanjiku Mwangi" autofocus>
                        @error('full_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="admission_number" class="form-label fw-semibold">Admission Number <span class="text-danger">*</span></label>
                        <input type="text" id="admission_number" name="admission_number"
                               class="form-control @error('admission_number') is-invalid @enderror"
                               value="{{ old('admission_number') }}" placeholder="e.g. IN16/00088/24">
                        @error('admission_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="programme" class="form-label fw-semibold">Programme <span class="text-danger">*</span></label>
                        <input type="text" id="programme" name="programme"
                               class="form-control @error('programme') is-invalid @enderror"
                               value="{{ old('programme') }}" placeholder="e.g. Bachelor of Science in Computer Science">
                        @error('programme')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="year_of_study" class="form-label fw-semibold">Year of Study <span class="text-danger">*</span></label>
                        <select id="year_of_study" name="year_of_study"
                                class="form-select @error('year_of_study') is-invalid @enderror">
                            <option value="" disabled {{ old('year_of_study') === null ? 'selected' : '' }}>— Select Year —</option>
                            @foreach([1,2,3,4] as $y)
                                <option value="{{ $y }}" {{ old('year_of_study') == $y ? 'selected' : '' }}>Year {{ $y }}</option>
                            @endforeach
                        </select>
                        @error('year_of_study')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle-fill me-1"></i>Save Student
                        </button>
                        <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
