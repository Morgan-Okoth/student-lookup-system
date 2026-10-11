<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature: student-results-lookup
 * Tests: GET /results renders the lookup page with all required form elements.
 * Requirements: 1.1, 1.3, 1.4, 1.5, 1.6
 */
class LookupPageRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_lookup_page_returns_200(): void
    {
        $response = $this->get('/results');

        $response->assertStatus(200);
    }

    public function test_lookup_page_contains_admission_number_input(): void
    {
        $response = $this->get('/results');

        $response->assertSee('name="admission_number"', false);
    }

    public function test_lookup_page_contains_academic_year_select(): void
    {
        $response = $this->get('/results');

        $response->assertSee('name="academic_year"', false);
    }

    public function test_lookup_page_contains_all_academic_year_options(): void
    {
        $response = $this->get('/results');

        $response->assertSee('Year 1');
        $response->assertSee('Year 2');
        $response->assertSee('Year 3');
        $response->assertSee('Year 4');
    }

    public function test_lookup_page_contains_semester_select(): void
    {
        $response = $this->get('/results');

        $response->assertSee('name="semester"', false);
    }

    public function test_lookup_page_contains_both_semester_options(): void
    {
        $response = $this->get('/results');

        $response->assertSee('Semester 1');
        $response->assertSee('Semester 2');
    }

    public function test_lookup_page_contains_search_button(): void
    {
        $response = $this->get('/results');

        $response->assertSee('type="submit"', false);
        $response->assertSee('Search');
    }

    public function test_lookup_page_does_not_show_not_found_message_by_default(): void
    {
        $response = $this->get('/results');

        $response->assertDontSee('No records found');
    }
}
