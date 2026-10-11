<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature: student-results-lookup
 * Tests: Validation errors are returned and old input is retained on failure.
 * Requirements: 2.1, 2.2, 2.3, 2.5, 2.6
 */
class LookupValidationTest extends TestCase
{
    use RefreshDatabase;

    // -----------------------------------------------------------------------
    // Required field validation
    // -----------------------------------------------------------------------

    public function test_empty_admission_number_returns_validation_error(): void
    {
        $response = $this->get('/results?admission_number=&academic_year=Year+1&semester=Semester+1');

        $response->assertSessionHasErrors('admission_number');
    }

    public function test_missing_admission_number_returns_validation_error(): void
    {
        $response = $this->get('/results?academic_year=Year+1&semester=Semester+1');

        $response->assertSessionHasErrors('admission_number');
    }

    public function test_empty_academic_year_returns_validation_error(): void
    {
        $response = $this->get('/results?admission_number=IN16%2F00088%2F24&semester=Semester+1');

        $response->assertSessionHasErrors('academic_year');
    }

    public function test_empty_semester_returns_validation_error(): void
    {
        $response = $this->get('/results?admission_number=IN16%2F00088%2F24&academic_year=Year+1');

        $response->assertSessionHasErrors('semester');
    }

    // -----------------------------------------------------------------------
    // Format validation
    // -----------------------------------------------------------------------

    public function test_invalid_admission_number_format_returns_format_error(): void
    {
        $response = $this->get('/results?admission_number=BADFORMAT&academic_year=Year+1&semester=Semester+1');

        $response->assertSessionHasErrors('admission_number');
    }

    public function test_admission_number_with_wrong_segment_lengths_is_rejected(): void
    {
        // Too many digits in first segment
        $response = $this->get('/results?admission_number=ABCDE16%2F00088%2F24&academic_year=Year+1&semester=Semester+1');

        $response->assertSessionHasErrors('admission_number');
    }

    public function test_invalid_academic_year_value_returns_error(): void
    {
        $response = $this->get('/results?admission_number=IN16%2F00088%2F24&academic_year=Year+5&semester=Semester+1');

        $response->assertSessionHasErrors('academic_year');
    }

    public function test_invalid_semester_value_returns_error(): void
    {
        $response = $this->get('/results?admission_number=IN16%2F00088%2F24&academic_year=Year+1&semester=Semester+3');

        $response->assertSessionHasErrors('semester');
    }

    // -----------------------------------------------------------------------
    // Valid input passes validation (requires a student that doesn't exist —
    // validation itself should pass and show the not-found state, not errors)
    // -----------------------------------------------------------------------

    public function test_valid_input_passes_validation_and_does_not_return_field_errors(): void
    {
        $response = $this->get('/results?admission_number=IN16%2F00088%2F24&academic_year=Year+1&semester=Semester+1');

        $response->assertSessionMissing('errors');
        // Will show not-found page, not validation errors
        $response->assertStatus(200);
    }

    // -----------------------------------------------------------------------
    // Old input is retained after a validation failure (Requirement 2.6)
    // Laravel redirects back on validation failure; old input is in the
    // session.  Follow the redirect then assert the flashed input is present.
    // -----------------------------------------------------------------------

    public function test_old_input_is_retained_in_session_after_validation_failure(): void
    {
        // Submit with a valid admission number but missing semester
        $response = $this->get('/results?admission_number=CS21%2F00123%2F23&academic_year=Year+2');

        // Validation fails → 302 redirect back
        $response->assertStatus(302);

        // Old input is flashed to the session
        $response->assertSessionHasInput('admission_number', 'CS21/00123/23');
    }

    public function test_old_academic_year_is_retained_in_session_after_validation_failure(): void
    {
        $response = $this->get('/results?admission_number=CS21%2F00123%2F23&academic_year=Year+3');

        $response->assertStatus(302);
        $response->assertSessionHasInput('academic_year', 'Year 3');
    }
}
