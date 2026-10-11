<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature: student-results-lookup
 * Tests: A valid lookup for an unknown admission number shows the not-found message.
 * Requirements: 3.2
 */
class StudentNotFoundTest extends TestCase
{
    use RefreshDatabase;

    public function test_unknown_admission_number_shows_not_found_message(): void
    {
        $response = $this->get('/results?admission_number=XX99%2F99999%2F99&academic_year=Year+1&semester=Semester+1');

        $response->assertStatus(200);
        $response->assertSee('No records found');
    }

    public function test_not_found_response_still_shows_the_lookup_form(): void
    {
        $response = $this->get('/results?admission_number=XX99%2F99999%2F99&academic_year=Year+1&semester=Semester+1');

        $response->assertStatus(200);
        // Form should still be visible for the user to retry
        $response->assertSee('name="admission_number"', false);
    }

    public function test_not_found_response_echoes_the_queried_admission_number(): void
    {
        $response = $this->get('/results?admission_number=XX99%2F99999%2F99&academic_year=Year+1&semester=Semester+1');

        // The view shows the queried admission number in the alert
        $response->assertSee('XX99/99999/99');
    }
}
