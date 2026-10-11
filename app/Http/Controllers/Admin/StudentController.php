<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStudentRequest;
use App\Models\Student;

class StudentController extends Controller
{
    public function index()
    {
        $students = Student::orderBy('full_name')->paginate(20);
        return view('admin.students.index', compact('students'));
    }

    public function create()
    {
        return view('admin.students.create');
    }

    public function store(StoreStudentRequest $request)
    {
        Student::create($request->validated());

        return redirect()
            ->route('admin.students.index')
            ->with('success', 'Student added successfully.');
    }

    public function destroy(Student $student)
    {
        // Cascade delete removes associated results via DB foreign key constraint
        $student->delete();

        return redirect()
            ->route('admin.students.index')
            ->with('success', 'Student and all associated results have been deleted.');
    }
}
