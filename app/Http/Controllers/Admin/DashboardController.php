<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Result;
use App\Models\Student;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'studentCount' => Student::count(),
            'courseCount'  => Course::count(),
            'resultCount'  => Result::count(),
        ]);
    }
}
