<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreResultRequest;
use App\Http\Requests\Admin\UpdateResultRequest;
use App\Models\Course;
use App\Models\Result;
use App\Models\Student;

class ResultController extends Controller
{
    public function index()
    {
        $results = Result::with(['student', 'course'])
            ->orderBy('created_at', 'desc')
            ->paginate(30);

        return view('admin.results.index', compact('results'));
    }

    public function create()
    {
        $students = Student::orderBy('full_name')->get();
        $courses  = Course::orderBy('course_code')->get();

        return view('admin.results.create', compact('students', 'courses'));
    }

    public function store(StoreResultRequest $request)
    {
        $marks = (int) $request->validated()['marks'];

        Result::create([
            'student_id'    => $request->validated()['student_id'],
            'course_id'     => $request->validated()['course_id'],
            'academic_year' => $request->validated()['academic_year'],
            'semester'      => $request->validated()['semester'],
            'marks'         => $marks,
            'grade'         => Result::calculateGrade($marks),
        ]);

        return redirect()
            ->route('admin.results.index')
            ->with('success', 'Result recorded successfully.');
    }

    public function edit(Result $result)
    {
        $result->load(['student', 'course']);
        return view('admin.results.edit', compact('result'));
    }

    public function update(UpdateResultRequest $request, Result $result)
    {
        $marks = (int) $request->validated()['marks'];

        $result->update([
            'marks' => $marks,
            'grade' => Result::calculateGrade($marks),
        ]);

        return redirect()
            ->route('admin.results.index')
            ->with('success', 'Result updated successfully.');
    }

    public function destroy(Result $result)
    {
        $result->delete();

        return redirect()
            ->route('admin.results.index')
            ->with('success', 'Result deleted successfully.');
    }
}
