@extends('layouts.admin')

@section('title', 'Courses')
@section('page-title', 'Courses')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0"><i class="bi bi-book-fill me-2 text-primary"></i>All Courses</h5>
    <a href="{{ route('admin.courses.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle-fill me-1"></i>Add Course
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background:#1a3a5c;color:#fff;">
                    <tr>
                        <th class="ps-4">#</th>
                        <th>Course Code</th>
                        <th>Course Name</th>
                        <th class="text-center pe-4">Credit Hours</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($courses as $index => $course)
                        <tr>
                            <td class="ps-4 text-muted small">{{ $index + 1 }}</td>
                            <td><code>{{ $course->course_code }}</code></td>
                            <td class="fw-semibold">{{ $course->course_name }}</td>
                            <td class="text-center pe-4">
                                <span class="badge bg-secondary">{{ $course->credit_hours }} hrs</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>No courses found.
                                <a href="{{ route('admin.courses.create') }}">Add the first course</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
