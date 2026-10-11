<?php

use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ResultController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\ResultsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Redirect root to results lookup
Route::get('/', fn () => redirect()->route('results.lookup'));

// Student results lookup — throttled at 10 requests per minute per IP
Route::get('/results', [ResultsController::class, 'index'])
    ->middleware('throttle:10,1')
    ->name('results.lookup');

/*
|--------------------------------------------------------------------------
| Admin Routes — protected by auth middleware
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Redirect /admin to dashboard
    Route::get('/', fn () => redirect()->route('admin.dashboard'));

    // Students
    Route::get('/students',              [StudentController::class, 'index'])->name('students.index');
    Route::get('/students/create',       [StudentController::class, 'create'])->name('students.create');
    Route::post('/students',             [StudentController::class, 'store'])->name('students.store');
    Route::delete('/students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');

    // Courses
    Route::get('/courses',         [CourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/create',  [CourseController::class, 'create'])->name('courses.create');
    Route::post('/courses',        [CourseController::class, 'store'])->name('courses.store');

    // Results
    Route::get('/results',               [ResultController::class, 'index'])->name('results.index');
    Route::get('/results/create',        [ResultController::class, 'create'])->name('results.create');
    Route::post('/results',              [ResultController::class, 'store'])->name('results.store');
    Route::get('/results/{result}/edit', [ResultController::class, 'edit'])->name('results.edit');
    Route::put('/results/{result}',      [ResultController::class, 'update'])->name('results.update');
    Route::delete('/results/{result}',   [ResultController::class, 'destroy'])->name('results.destroy');
});

/*
|--------------------------------------------------------------------------
| Auth Routes (provided by Laravel Breeze)
|--------------------------------------------------------------------------
| Breeze publishes routes/auth.php which is included via bootstrap/app.php
| or you can include it explicitly:
*/
require __DIR__.'/auth.php';
