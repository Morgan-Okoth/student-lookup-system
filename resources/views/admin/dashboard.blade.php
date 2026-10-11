@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="row g-4 mb-4">

    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3 p-4">
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                     style="width:56px;height:56px;background:#dbeafe;flex-shrink:0;">
                    <i class="bi bi-people-fill fs-4 text-primary"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold text-dark">{{ $studentCount }}</div>
                    <div class="text-muted small">Registered Students</div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-top py-2 px-4">
                <a href="{{ route('admin.students.index') }}" class="small text-primary text-decoration-none">
                    View all students <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3 p-4">
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                     style="width:56px;height:56px;background:#dcfce7;flex-shrink:0;">
                    <i class="bi bi-book-fill fs-4 text-success"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold text-dark">{{ $courseCount }}</div>
                    <div class="text-muted small">Active Courses</div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-top py-2 px-4">
                <a href="{{ route('admin.courses.index') }}" class="small text-success text-decoration-none">
                    View all courses <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3 p-4">
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                     style="width:56px;height:56px;background:#fef9c3;flex-shrink:0;">
                    <i class="bi bi-clipboard2-data-fill fs-4 text-warning"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold text-dark">{{ $resultCount }}</div>
                    <div class="text-muted small">Result Records</div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-top py-2 px-4">
                <a href="{{ route('admin.results.index') }}" class="small text-warning text-decoration-none">
                    View all results <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

</div>

<div class="row g-4">
    <div class="col-12 col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold border-bottom">
                <i class="bi bi-lightning-charge-fill text-warning me-2"></i>Quick Actions
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.students.create') }}" class="btn btn-outline-primary">
                        <i class="bi bi-person-plus-fill me-2"></i>Add New Student
                    </a>
                    <a href="{{ route('admin.courses.create') }}" class="btn btn-outline-success">
                        <i class="bi bi-plus-circle-fill me-2"></i>Add New Course
                    </a>
                    <a href="{{ route('admin.results.create') }}" class="btn btn-outline-warning">
                        <i class="bi bi-pencil-square me-2"></i>Enter Result
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold border-bottom">
                <i class="bi bi-info-circle-fill text-info me-2"></i>System Info
            </div>
            <div class="card-body small text-muted">
                <p class="mb-1"><strong>System:</strong> Student Results Lookup</p>
                <p class="mb-1"><strong>Institution:</strong> Kisii University</p>
                <p class="mb-1"><strong>Environment:</strong> Demonstration</p>
                <p class="mb-0"><strong>Logged in as:</strong> {{ auth()->user()->name ?? 'Administrator' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
