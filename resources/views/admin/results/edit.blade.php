@extends('layouts.admin')

@section('title', 'Edit Result')
@section('page-title', 'Edit Result')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-7">

        <div class="d-flex align-items-center gap-2 mb-4">
            <a href="{{ route('admin.results.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h5 class="fw-bold mb-0"><i class="bi bi-pencil-fill me-2 text-primary"></i>Edit Result</h5>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">

                {{-- Read-only context --}}
                <div class="alert alert-light border mb-4 small">
                    <div class="row g-2">
                        <div class="col-6"><strong>Student:</strong> {{ $result->student->full_name }}</div>
                        <div class="col-6"><strong>Admission No.:</strong> {{ $result->student->admission_number }}</div>
                        <div class="col-6"><strong>Course:</strong> {{ $result->course->course_code }} — {{ $result->course->course_name }}</div>
                        <div class="col-6"><strong>Period:</strong> {{ $result->academic_year }}, {{ $result->semester }}</div>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.results.update', $result) }}" novalidate>
                    @csrf
                    @method('PUT')

                    <div class="row align-items-end mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label for="marks" class="form-label fw-semibold">Marks (0–100) <span class="text-danger">*</span></label>
                            <input type="number" id="marks" name="marks"
                                   class="form-control @error('marks') is-invalid @enderror"
                                   value="{{ old('marks', $result->marks) }}" min="0" max="100">
                            @error('marks')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-muted">Calculated Grade</label>
                            <div id="grade-preview" class="form-control bg-light fw-bold text-center fs-5 text-muted">
                                {{ $result->grade }}
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle-fill me-1"></i>Update Result
                        </button>
                        <a href="{{ route('admin.results.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const marksInput   = document.getElementById('marks');
    const gradePreview = document.getElementById('grade-preview');

    function calcGrade(m) {
        if (m === '' || isNaN(m)) return '—';
        const v = parseInt(m, 10);
        if (v >= 70) return 'A';
        if (v >= 60) return 'B';
        if (v >= 50) return 'C';
        if (v >= 40) return 'D';
        return 'F';
    }

    const gradeClasses = { A: 'text-success', B: 'text-primary', C: 'text-warning', D: 'text-secondary', F: 'text-danger', '—': 'text-muted' };

    function updatePreview() {
        const grade = calcGrade(marksInput.value);
        gradePreview.textContent = grade;
        gradePreview.className = 'form-control bg-light fw-bold text-center fs-5 ' + (gradeClasses[grade] || 'text-muted');
    }

    marksInput.addEventListener('input', updatePreview);
    updatePreview();
</script>
@endpush
