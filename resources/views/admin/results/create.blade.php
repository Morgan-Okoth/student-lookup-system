@extends('layouts.admin')

@section('title', 'Enter Result')
@section('page-title', 'Enter Result')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-7">

        <div class="d-flex align-items-center gap-2 mb-4">
            <a href="{{ route('admin.results.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h5 class="fw-bold mb-0"><i class="bi bi-pencil-square me-2 text-warning"></i>Enter New Result</h5>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('admin.results.store') }}" novalidate>
                    @csrf

                    <div class="mb-3">
                        <label for="student_id" class="form-label fw-semibold">Student <span class="text-danger">*</span></label>
                        <select id="student_id" name="student_id"
                                class="form-select @error('student_id') is-invalid @enderror">
                            <option value="" disabled {{ old('student_id') === null ? 'selected' : '' }}>— Select Student —</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                    {{ $student->full_name }} ({{ $student->admission_number }})
                                </option>
                            @endforeach
                        </select>
                        @error('student_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="course_id" class="form-label fw-semibold">Course <span class="text-danger">*</span></label>
                        <select id="course_id" name="course_id"
                                class="form-select @error('course_id') is-invalid @enderror">
                            <option value="" disabled {{ old('course_id') === null ? 'selected' : '' }}>— Select Course —</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                    {{ $course->course_code }} — {{ $course->course_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('course_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="academic_year" class="form-label fw-semibold">Academic Year <span class="text-danger">*</span></label>
                            <select id="academic_year" name="academic_year"
                                    class="form-select @error('academic_year') is-invalid @enderror">
                                <option value="" disabled {{ old('academic_year') === null ? 'selected' : '' }}>— Select Year —</option>
                                @foreach(['Year 1','Year 2','Year 3','Year 4'] as $year)
                                    <option value="{{ $year }}" {{ old('academic_year') === $year ? 'selected' : '' }}>{{ $year }}</option>
                                @endforeach
                            </select>
                            @error('academic_year')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="semester" class="form-label fw-semibold">Semester <span class="text-danger">*</span></label>
                            <select id="semester" name="semester"
                                    class="form-select @error('semester') is-invalid @enderror">
                                <option value="" disabled {{ old('semester') === null ? 'selected' : '' }}>— Select Semester —</option>
                                @foreach(['Semester 1','Semester 2'] as $sem)
                                    <option value="{{ $sem }}" {{ old('semester') === $sem ? 'selected' : '' }}>{{ $sem }}</option>
                                @endforeach
                            </select>
                            @error('semester')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row align-items-end mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label for="marks" class="form-label fw-semibold">Marks (0–100) <span class="text-danger">*</span></label>
                            <input type="number" id="marks" name="marks"
                                   class="form-control @error('marks') is-invalid @enderror"
                                   value="{{ old('marks') }}" min="0" max="100"
                                   placeholder="e.g. 75">
                            @error('marks')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-muted">Calculated Grade</label>
                            <div id="grade-preview" class="form-control bg-light fw-bold text-center fs-5 text-muted">
                                —
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-warning text-dark">
                            <i class="bi bi-check-circle-fill me-1"></i>Save Result
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
    // Live grade preview
    const marksInput  = document.getElementById('marks');
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
