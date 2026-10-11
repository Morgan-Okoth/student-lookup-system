<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Result;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature: student-results-lookup
 * Tests that the public lookup page and results page contain no edit/delete/update controls.
 * Requirements: 5.4
 */
class ReadOnlyLookupPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_lookup_page_has_no_edit_controls(): void
    {
        $response = $this->get('/results');
        $response->assertDontSee('method="DELETE"', false);
        $response->assertDontSee('method="PUT"', false);
        $response->assertDontSee('Edit', false);
        $response->assertDontSee('Delete', false);
        $response->assertDontSee('Update', false);
    }

    public function test_results_show_page_has_no_edit_or_delete_controls(): void
    {
        $student = Student::create([
            'admission_number' => 'CS20/00001/22',
            'full_name'        => 'Read Only Student',
            'programme'        => 'Computer Science',
            'year_of_study'    => 1,
        ]);

        $course = Course::create([
            'course_code'  => 'CS101',
            'course_name'  => 'Computing Fundamentals',
            'credit_hours' => 3,
        ]);

        Result::create([
            'student_id'    => $student->id,
            'course_id'     => $course->id,
            'academic_year' => 'Year 1',
            'semester'      => 'Semester 1',
            'marks'         => 65,
            'grade'         => 'B',
        ]);

        $response = $this->get('/results?' . http_build_query([
            'admission_number' => 'CS20/00001/22',
            'academic_year'    => 'Year 1',
            'semester'         => 'Semester 1',
        ]));

        $response->assertStatus(200);
        // No destructive or mutating controls present
        $response->assertDontSee('name="_method"', false);
        $response->assertDontSee('Edit');
        $response->assertDontSee('Delete');
        $response->assertDontSee('Update');
    }
}
