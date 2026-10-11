<?php

namespace App\Http\Controllers;

use App\Http\Requests\LookupRequest;
use App\Models\Student;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class ResultsController extends Controller
{
    /**
     * Show the lookup form or the results page.
     *
     * GET /results                              → blank form
     * GET /results?admission_number=...&...     → validated search
     */
    public function index(Request $request)
    {
        // If none of the three search params are present, show blank form
        if (! $request->hasAny(['admission_number', 'academic_year', 'semester'])) {
            return view('results.lookup');
        }

        // Delegate to a separate validated method so Laravel runs LookupRequest
        return $this->search(
            app(LookupRequest::class)
        );
    }

    /**
     * Run after LookupRequest validation passes.
     */
    private function search(LookupRequest $request)
    {
        $admissionNumber = $request->admission_number; // already trimmed + uppercased
        $academicYear    = $request->academic_year;
        $semester        = $request->semester;

        // Look up student — never expose DB ID in URL
        $student = Student::whereAdmissionNumber($admissionNumber)->first();

        if (! $student) {
            return view('results.lookup', [
                'notFound' => true,
                'input'    => $request->only(['admission_number', 'academic_year', 'semester']),
            ]);
        }

        // Retrieve results for this student only, filtered by year + semester
        $results = $student->results()
            ->with('course')
            ->where('academic_year', $academicYear)
            ->where('semester', $semester)
            ->get();

        $summary = $this->buildSummary($results);

        return view('results.show', compact('student', 'results', 'summary', 'academicYear', 'semester'));
    }

    /**
     * Build summary statistics from a results collection.
     */
    private function buildSummary(Collection $results): array
    {
        $count        = $results->count();
        $totalMarks   = $results->sum('marks');
        $totalCredits = $results->sum(fn ($r) => $r->course->credit_hours ?? 0);

        return [
            'total_courses'      => $count,
            'total_credit_hours' => $totalCredits,
            'average_mark'       => $count > 0 ? round($totalMarks / $count, 2) : 0,
        ];
    }
}
