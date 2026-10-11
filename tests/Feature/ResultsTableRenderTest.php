<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Result;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature: student-results-lookup
 * Tests that the results table renders correctly with student info, column headers,
 * result rows, and correct summary statistics.
 * Requirements: 4.1, 4.2, 4.3, 4.5
 */
class ResultsTableRenderTest extends TestCase
{
    use RefreshDatabase;

    private Student $student;
    private Course $course;
    private Result $result;

    protected function setUp(): void
    {
        parent::setUp();

        $this->student = Student::create([
            'admission_number' => 'CS20/00001/22',
            'full_name'        => 'Jane Doe',
            'programme'        => 'Computer Science',
            'year_of_study'    => 1,
        ]);

        $this->course = Course::create([
            'course_code'  => 'CS101',
            'course_name'  => 'Introduction to Computing',
            'credit_hours' => 3,
        ]);

        $this->result = Result::create([
            'student_id'    => $this->student->id,
            'course_id'     => $this->course->id,
            'academic_year' => 'Year 1',
            'semester'      => 'Semester 1',
            'marks'         => 75,
            'grade'         => Result::calculateGrade(75),
        ]);
    }

    private function doLookup(): \Illuminate\Testing\TestResponse
    {
        return $this->get('/results?' . http_build_query([
            'admission_number' => 'CS20/00001/22',
            'academic_year'    => 'Year 1',
            'semester'         => 'Semester 1',
        ]));
    }

    public function test_response_returns_200(): void
    {
        $this->doLookup()->assertStatus(200);
    }

    public function test_student_full_name_is_displayed(): void
    {
        $this->doLookup()->assertSee('Jane Doe');
    }

    public function test_student_admission_number_is_displayed(): void
    {
        $this->doLookup()->assertSee('CS20/00001/22');
    }

    public function test_student_programme_is_displayed(): void
    {
        $this->doLookup()->assertSee('Computer Science');
    }

    public function test_table_contains_course_code_column(): void
    {
        $this->doLookup()->assertSee('Course Code');
    }

    public function test_table_contains_course_name_column(): void
    {
        $this->doLookup()->assertSee('Course Name');
    }

    public function test_table_contains_credit_hours_column(): void
    {
        $this->doLookup()->assertSee('Credit Hours');
    }

    public function test_table_contains_marks_column(): void
    {
        $this->doLookup()->assertSee('Marks');
    }

    public function test_table_contains_grade_column(): void
    {
        $this->doLookup()->assertSee('Grade');
    }

    public function test_table_contains_course_data(): void
    {
        $response = $this->doLookup();
        $response->assertSee('CS101');
        $response->assertSee('Introduction to Computing');
    }

    public function test_summary_shows_correct_total_courses(): void
    {
        $this->doLookup()->assertSee('Total Courses');
        $this->doLookup()->assertSee('1');
    }

    public function test_summary_shows_correct_credit_hours(): void
    {
        $response = $this->doLookup();
        $response->assertSee('Total Credit Hours');
        // Credit hours is 3
        $response->assertSee('3');
    }

    public function test_summary_shows_correct_average_mark(): void
    {
        $response = $this->doLookup();
        $response->assertSee('Average Mark');
        // Average of one result with 75 marks = 75.00
        $response->assertSee('75.00');
    }
}
