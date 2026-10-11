@extends('layouts.admin')

@section('title', 'Students')
@section('page-title', 'Students')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0"><i class="bi bi-people-fill me-2 text-primary"></i>All Students</h5>
    <a href="{{ route('admin.students.create') }}" class="btn btn-primary">
        <i class="bi bi-person-plus-fill me-1"></i>Add Student
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background:#1a3a5c;color:#fff;">
                    <tr>
                        <th class="ps-4">#</th>
                        <th>Full Name</th>
                        <th>Admission Number</th>
                        <th>Programme</th>
                        <th class="text-center">Year of Study</th>
                        <th class="text-center pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                        <tr>
                            <td class="ps-4 text-muted small">{{ $students->firstItem() + $loop->index }}</td>
                            <td class="fw-semibold">{{ $student->full_name }}</td>
                            <td><code>{{ $student->admission_number }}</code></td>
                            <td class="text-muted small">{{ $student->programme }}</td>
                            <td class="text-center">
                                <span class="badge bg-secondary">Year {{ $student->year_of_study }}</span>
                            </td>
                            <td class="text-center pe-4">
                                <form method="POST"
                                      action="{{ route('admin.students.destroy', $student) }}"
                                      onsubmit="return confirm('Delete {{ addslashes($student->full_name) }} and all their results? This cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash3-fill"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>No students found.
                                <a href="{{ route('admin.students.create') }}">Add the first student</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($students->hasPages())
        <div class="card-footer bg-white border-top py-3 px-4">
            {{ $students->links() }}
        </div>
    @endif
</div>
@endsection
