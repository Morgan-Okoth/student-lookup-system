@extends('layouts.app')

@section('title', 'Results Lookup')

@push('styles')
<style>
    .lookup-card {
        max-width: 520px;
        width: 100%;
        border: none;
        border-radius: 1rem;
        box-shadow: 0 4px 24px rgba(26,58,92,0.10);
    }
    .lookup-card .card-header {
        background: linear-gradient(135deg, #1a3a5c 0%, #2d6a4f 100%);
        border-radius: 1rem 1rem 0 0;
        padding: 1.75rem 2rem;
    }
    .lookup-hero {
        min-height: calc(100vh - 140px);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .form-label { font-weight: 500; font-size: 0.9rem; }
    .btn-search {
        background: linear-gradient(135deg, #1a3a5c 0%, #2d6a4f 100%);
        border: none;
        letter-spacing: 0.03em;
    }
    .btn-search:hover { opacity: 0.9; }
</style>
@endpush

@section('content')
<div class="lookup-hero">
    <div class="lookup-card card">

        <!-- Card header -->
        <div class="card-header text-center">
            <div class="d-flex align-items-center justify-content-center gap-3 mb-2">
                <div style="width:56px;height:56px;background:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:1.2rem;color:#1a3a5c;">KU</div>
                <div class="text-start">
                    <div class="text-white fw-bold fs-5 lh-1">Kisii University</div>
                    <div style="color:#c8e6c9;font-size:.75rem;">Empowering Minds, Transforming Lives</div>
                </div>
            </div>
            <hr style="border-color:rgba(255,255,255,.2);margin:.75rem 0 .5rem;">
            <h5 class="text-white mb-0 fw-semibold">
                <i class="bi bi-search me-2"></i>Student Results Lookup
            </h5>
            <p class="text-white-50 small mb-0">Enter your admission number to view your academic results</p>
        </div>

        <div class="card-body p-4">

            {{-- Not found alert --}}
            @if(isset($notFound) && $notFound)
                <div class="alert alert-warning d-flex align-items-start gap-2 mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill mt-1 flex-shrink-0"></i>
                    <div>
                        <strong>No records found.</strong><br>
                        The admission number <strong>{{ $input['admission_number'] ?? '' }}</strong>
                        was not found in the system. Please verify and try again.
                    </div>
                </div>
            @endif

            {{-- Search form --}}
            <form method="GET" action="{{ route('results.lookup') }}" novalidate>

                {{-- Admission Number --}}
                <div class="mb-3">
                    <label for="admission_number" class="form-label">
                        <i class="bi bi-person-badge me-1"></i>Admission Number
                    </label>
                    <input
                        type="text"
                        id="admission_number"
                        name="admission_number"
                        class="form-control form-control-lg @error('admission_number') is-invalid @enderror"
                        placeholder="e.g. IN16/00088/24"
                        value="{{ old('admission_number', $input['admission_number'] ?? '') }}"
                        autocomplete="off"
                        autofocus
                    >
                    @error('admission_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Academic Year --}}
                <div class="mb-3">
                    <label for="academic_year" class="form-label">
                        <i class="bi bi-calendar3 me-1"></i>Academic Year
                    </label>
                    <select
                        id="academic_year"
                        name="academic_year"
                        class="form-select form-select-lg @error('academic_year') is-invalid @enderror"
                    >
                        <option value="" disabled {{ old('academic_year', $input['academic_year'] ?? '') === '' ? 'selected' : '' }}>— Select Year —</option>
                        @foreach(['Year 1','Year 2','Year 3','Year 4'] as $year)
                            <option value="{{ $year }}"
                                {{ old('academic_year', $input['academic_year'] ?? '') === $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>
                    @error('academic_year')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Semester --}}
                <div class="mb-4">
                    <label for="semester" class="form-label">
                        <i class="bi bi-journal-bookmark me-1"></i>Semester
                    </label>
                    <select
                        id="semester"
                        name="semester"
                        class="form-select form-select-lg @error('semester') is-invalid @enderror"
                    >
                        <option value="" disabled {{ old('semester', $input['semester'] ?? '') === '' ? 'selected' : '' }}>— Select Semester —</option>
                        @foreach(['Semester 1','Semester 2'] as $sem)
                            <option value="{{ $sem }}"
                                {{ old('semester', $input['semester'] ?? '') === $sem ? 'selected' : '' }}>
                                {{ $sem }}
                            </option>
                        @endforeach
                    </select>
                    @error('semester')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg btn-search text-white">
                        <i class="bi bi-search me-2"></i>Search Results
                    </button>
                </div>

            </form>
        </div>

        <div class="card-footer text-center text-muted small py-3 bg-light border-top-0 rounded-bottom">
            <i class="bi bi-shield-lock me-1"></i>
            Your results are private and secure. Only you can view your records.
        </div>
    </div>
</div>
@endsection
