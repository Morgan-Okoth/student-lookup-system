<?php

namespace Tests\Feature;

use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature: student-results-lookup
 * Tests that a valid lookup for a student with no results in the selected period
 * shows the "no results available" message and does NOT render a results table.
 * Requirements: 3.4, 4.6
 */
class NoResultsForPeriodTest extends TestCase
{
    use RefreshDatabase;

    private Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->student = Student::create([
            'admission_number' => 'IN16/00088/24',
            'full_name'        => 'Alice Mwangi',
            'programme'        => 'Information Technology',
            'year_of_study'    => 1,
        ]);
    }

    private function doLookup(string $year = 'Year 1', string $semester = 'Semester 1'): \Illuminate\Testing\TestResponse
    {
        return $this->get('/results?' . http_build_query([
            'admission_number' => 'IN16/00088/24',
            'academic_year'    => $year,
            'semester'         => $semester,
        ]));
    }

    public function test_response_returns_200_when_student_has_no_results(): void
    {
        $this->doLookup()->assertStatus(200);
    }

    public function test_no_results_available_message_is_shown(): void
    {
        $response = $this->doLookup();

        $response->assertSee('No results available.');
    }

    public function test_no_results_table_is_rendered(): void
    {
        $response = $this->doLookup();

        // The results table must NOT be present when there are no results
        $response->assertDontSee('<table', false);
    }

    public function test_message_includes_student_name(): void
    {
        $response = $this->doLookup();

        $response->assertSee('Alice Mwangi');
    }

    public function test_message_includes_selected_period(): void
    {
        $response = $this->doLookup('Year 2', 'Semester 2');

        $response->assertSee('Year 2');
        $response->assertSee('Semester 2');
    }

    public function test_no_results_for_different_period_combinations(): void
    {
        foreach (['Year 1', 'Year 2', 'Year 3', 'Year 4'] as $year) {
            foreach (['Semester 1', 'Semester 2'] as $semester) {
                $response = $this->doLookup($year, $semester);
                $response->assertStatus(200);
                $response->assertSee('No results available.');
                $response->assertDontSee('<table', false);
            }
        }
    }
}
