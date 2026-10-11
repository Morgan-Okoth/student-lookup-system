@extends('layouts.admin')

@section('title', 'Results')
@section('page-title', 'Results')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0"><i class="bi bi-clipboard2-data-fill me-2 text-warning"></i>All Results</h5>
    <a href="{{ route('admin.results.create') }}" class="btn btn-warning text-dark">
        <i class="bi bi-pencil-square me-1"></i>Enter Result
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background:#1a3a5c;color:#fff;">
                    <tr>
                        <th class="ps-4">#</th>
                        <th>Student</th>
                        <th>Admission No.</th>
                        <th>Course</th>
                        <th>Year</th>
                        <th>Semester</th>
                        <th class="text-center">Marks</th>
                        <th class="text-center">Grade</th>
                        <th class="text-center pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($results as $result)
                        <tr>
                            <td class="ps-4 text-muted small">{{ $results->firstItem() + $loop->index }}</td>
                            <td class="fw-semibold small">{{ $result->student->full_name }}</td>
                            <td><code class="small">{{ $result->student->admission_number }}</code></td>
                            <td class="small">
                                <span class="fw-semibold">{{ $result->course->course_code }}</span>
                                <span class="text-muted ms-1">{{ Str::limit($result->course->course_name, 28) }}</span>
                            </td>
                            <td class="small">{{ $result->academic_year }}</td>
                            <td class="small">{{ $result->semester }}</td>
                            <td class="text-center fw-semibold">{{ $result->marks }}</td>
                            <td class="text-center">
                                @php
                                    $gradeColors = ['A'=>'success','B'=>'primary','C'=>'warning','D'=>'orange','F'=>'danger'];
                                    $color = match($result->grade) {
                                        'A' => 'success', 'B' => 'primary', 'C' => 'warning', 'D' => 'secondary', default => 'danger'
                                    };
                                @endphp
                                <span class="badge bg-{{ $color }} {{ $result->grade === 'C' ? 'text-dark' : 'text-white' }}">
                                    {{ $result->grade }}
                                </span>
                            </td>
                            <td class="text-center pe-4">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="{{ route('admin.results.edit', $result) }}"
                                       class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.results.destroy', $result) }}"
                                          onsubmit="return confirm('Delete this result record?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="bi bi-trash3-fill"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>No results recorded yet.
                                <a href="{{ route('admin.results.create') }}">Enter the first result</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($results->hasPages())
        <div class="card-footer bg-white border-top py-3 px-4">
            {{ $results->links() }}
        </div>
    @endif
</div>
@endsection
