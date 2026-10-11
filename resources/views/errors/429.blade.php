@extends('layouts.app')

@section('title', 'Too Many Requests')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-6 text-center py-5">
        <div class="display-1 fw-bold text-warning">429</div>
        <h2 class="fw-bold mt-2">Too Many Requests</h2>
        <p class="text-muted mb-4">
            You have made too many lookup requests in a short period.<br>
            Please wait a moment and try again.
        </p>
        <a href="{{ route('results.lookup') }}" class="btn btn-primary">
            <i class="bi bi-arrow-left me-1"></i>Back to Lookup
        </a>
    </div>
</div>
@endsection
