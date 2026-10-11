@extends('layouts.app')

@section('title', 'Page Not Found')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-6 text-center py-5">
        <div class="display-1 fw-bold text-secondary">404</div>
        <h2 class="fw-bold mt-2">Page Not Found</h2>
        <p class="text-muted mb-4">
            The page you are looking for does not exist or has been moved.
        </p>
        <a href="{{ route('results.lookup') }}" class="btn btn-primary">
            <i class="bi bi-house-fill me-1"></i>Back to Results Lookup
        </a>
    </div>
</div>
@endsection
