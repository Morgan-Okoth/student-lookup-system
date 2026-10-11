@extends('layouts.app')

@section('title', 'Academic Results — ' . $student->full_name)

@push('styles')
<style>
    .results-card { border: none; border-radius: 1rem; box-shadow: 0 4px 24px rgba(26,58,92,.10); }
    .results-header {
        background: linear-gradient(135deg, #1a3a5c 0%, #2d6a4f 100%);
        border-radius: 1rem 1rem 0 0;
        padding: 1.5rem 2rem;
    }
    .student-meta dt { font-size: .78rem; color: #a8c4df; text-transform: uppercase; letter-spacing: .05em; }
    .student-meta dd { font-size: .95rem; color: #fff; font-weight: 600; margin-bottom: 0; }
    .grade-badge { font-size: .85rem; font-weight: 700; min-width: 2rem; text-align: center; }
    .grade-A { background-color: #198754; }
    .grade-B { background-color: #0d6efd; }
    .grade-C { background-color: #ffc107; color: #212529 !important; }
    .grade-D { background-color: #fd7e14; }
    .grade-F { background-color: #dc3545; }
    .summary-box {
        background: #f8fbff;
        border: 1px solid #dbe8f5;
        border-radius: .75rem;
    }
    .summary-stat { font-size: 1.5rem; font-weight: 700; color: #1a3a5c; }
    .summary-label { font-size: .78rem; text-transform: uppercase; letter-spacing: .05em; color: #6c757d; }
    table thead { background-color: #1a3a5c; color: #fff; }
    table thead th { border: none; font-size: .85rem; letter-spacing: .03em; font-weight: 600; }
</style>
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-xl-10">

        <!-- Back link -->
        <div class="mb-3">
            <a href="{{ route('results.lookup') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>New Search
            </a>
        </div>

        <div class="results-card card">

            {{-- Student header --}}
            <div class="results-header">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div style="width:48px;height:48px;background:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:1.1rem;color:#1a3a5c;flex-shrink:0;">KU</div>
                    <div>
                        <div class="text-white fw-bold fs-5 lh-1">Kisii University</div>
                        <div style="color:#c8e6c9;font-size:.75rem;">Official Academic Results Transcript</div>
                    </div>
                </div>

                <dl class="row student-meta g-3 mb-0">
                    <div class="col-6 col-md-4">
                        <dt>Student Name</dt>
                        <dd>{{ $student->full_name }}</dd>
                    </div>
                    <div class="col-6 col-md-3">
                        <dt>Admission Number</dt>
                        <dd>{{ $student->admission_number }}</dd>
                    </div>
                    <div class="col-12 col-md-5">
                        <dt>Programme</dt>
                        <dd>{{ $student->programme }}</dd>
                    </div>
                    <div class="col-6 col-md-4">
                        <dt>Academic Year</dt>
                        <dd>{{ $academicYear }}</dd>
                    </div>
                    <div class="col-6 col-md-3">
                        <dt>Semester</dt>
                        <dd>{{ $semester }}</dd>
                    </div>
                </dl>
            </div>

            <div class="card-body p-4">

                @if($results->isEmpty())
                    {{-- No results for this period --}}
                    <div class="alert alert-info d-flex align-items-start gap-2" role="alert">
                        <i class="bi bi-info-circle-fill mt-1 flex-shrink-0"></i>
                        <div>
                            <strong>No results available.</strong><br>
                            There are no recorded results for <strong>{{ $student->full_name }}</strong>
                            in <strong>{{ $academicYear }}, {{ $semester }}</strong>.
                            Please contact the Examinations Office if you believe this is an error.
                        </div>
                    </div>
                @else
                    {{-- Results table --}}
                    <h6 class="fw-semibold text-muted mb-3">
                        <i class="bi bi-table me-1"></i>Course Results
                    </h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Course Code</th>
                                    <th scope="col">Course Name</th>
                                    <th scope="col" class="text-center">Credit Hours</th>
                                    <th scope="col" class="text-center">Marks</th>
                                    <th scope="col" class="text-center">Grade</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($results as $i => $result)
                                    <tr>
                                        <td class="text-muted small">{{ $i + 1 }}</td>
                                        <td><code class="text-dark fw-semibold">{{ $result->course->course_code }}</code></td>
                                        <td>{{ $result->course->course_name }}</td>
                                        <td class="text-center">{{ $result->course->credit_hours }}</td>
                                        <td class="text-center fw-semibold">{{ $result->marks }}</td>
                                        <td class="text-center">
                                            <span class="badge grade-badge grade-{{ $result->grade }} text-white">
                                                {{ $result->grade }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Summary statistics --}}
                    <div class="summary-box p-4">
                        <h6 class="fw-semibold text-muted mb-3">
                            <i class="bi bi-bar-chart-fill me-1"></i>Summary
                        </h6>
                        <div class="row g-3 text-center">
                            <div class="col-4">
                                <div class="summary-stat">{{ $summary['total_courses'] }}</div>
                                <div class="summary-label">Total Courses</div>
                            </div>
                            <div class="col-4">
                                <div class="summary-stat">{{ $summary['total_credit_hours'] }}</div>
                                <div class="summary-label">Total Credit Hours</div>
                            </div>
                            <div class="col-4">
                                <div class="summary-stat">{{ number_format($summary['average_mark'], 2) }}</div>
                                <div class="summary-label">Average Mark</div>
                            </div>
                        </div>
                    </div>

                    {{-- Grade key --}}
                    <div class="mt-3 d-flex flex-wrap gap-2 small text-muted">
                        <span class="fw-semibold">Grade Key:</span>
                        <span><span class="badge grade-A text-white px-2">A</span> 70–100</span>
                        <span><span class="badge grade-B text-white px-2">B</span> 60–69</span>
                        <span><span class="badge grade-C px-2">C</span> 50–59</span>
                        <span><span class="badge grade-D text-white px-2">D</span> 40–49</span>
                        <span><span class="badge grade-F text-white px-2">F</span> 0–39</span>
                    </div>
                @endif

            </div>

            <div class="card-footer text-muted small text-center py-2 bg-light rounded-bottom">
                <i class="bi bi-shield-check me-1"></i>
                This is an official record. Printed on {{ now()->format('d F Y') }}.
            </div>
        </div>

    </div>
</div>
@endsection
