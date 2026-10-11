<?php

namespace Tests\Feature\Properties;

use Eris\Generator;
use Illuminate\Routing\Middleware\ThrottleRequests;

/**
 * Feature: student-results-lookup, Property 1: Required lookup fields validation
 *
 * For any form submission where at least one of admission_number, academic_year,
 * or semester is absent or empty, the validator SHALL reject the request and return
 * validation errors naming every missing field.
 *
 * Validates: Requirements 2.1, 2.2, 2.3
 * Minimum iterations: 100
 */
class RequiredFieldsPropertyTest extends PropertyTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Disable the throttle middleware for all requests in this test so
        // the 100+ iterations do not hit the 10 req/min IP rate limit.
        $this->withoutMiddleware(ThrottleRequests::class);
    }

    /**
     * Omitting any 1 or 2 of the three required fields (while providing the
     * remaining fields) always triggers validation errors for each omitted field.
     *
     * Note: omitting ALL three fields causes ResultsController to return the
     * blank form before LookupRequest runs (the hasAny() guard). We cover that
     * behaviour separately in test_blank_request_returns_empty_form().
     *
     * 6 omission patterns × varied year/semester = 100 iterations.
     */
    public function test_partial_submission_always_triggers_errors_for_omitted_fields(): void
    {
        // All proper subsets where 1 or 2 fields are omitted
        // (omitting all 3 would bypass validation entirely — see next test)
        $omissionSets = [
            ['admission_number'],
            ['academic_year'],
            ['semester'],
            ['admission_number', 'academic_year'],
            ['admission_number', 'semester'],
            ['academic_year', 'semester'],
        ];

        $this->limitTo(100)
            ->forAll(
                Generator\choose(0, count($omissionSets) - 1),
                Generator\elements('Year 1', 'Year 2', 'Year 3', 'Year 4'),
                Generator\elements('Semester 1', 'Semester 2')
            )
            ->then(function (int $idx, string $year, string $semester) use ($omissionSets) {
                $omitted = $omissionSets[$idx];

                $params = [];
                if (!in_array('admission_number', $omitted)) {
                    $params['admission_number'] = 'IN16/00088/24';
                }
                if (!in_array('academic_year', $omitted)) {
                    $params['academic_year'] = $year;
                }
                if (!in_array('semester', $omitted)) {
                    $params['semester'] = $semester;
                }

                // At least one param is always present, so LookupRequest validation runs
                $query    = http_build_query($params);
                $response = $this->get("/results?$query");

                // Every omitted field must produce a session validation error
                foreach ($omitted as $missingField) {
                    $response->assertSessionHasErrors($missingField);
                }
            });
    }

    /**
     * Providing all three required fields with valid values never triggers
     * validation errors.  100 iterations varying year and semester.
     */
    public function test_all_required_fields_present_passes_validation(): void
    {
        $this->limitTo(100)
            ->forAll(
                Generator\elements('Year 1', 'Year 2', 'Year 3', 'Year 4'),
                Generator\elements('Semester 1', 'Semester 2')
            )
            ->then(function (string $year, string $semester) {
                $response = $this->get(
                    '/results?admission_number=IN16%2F00088%2F24'
                    . '&academic_year=' . urlencode($year)
                    . '&semester=' . urlencode($semester)
                );

                $response->assertSessionMissing('errors');
            });
    }

    /**
     * When NO query params are submitted at all, the controller returns the
     * blank lookup form (HTTP 200) without triggering LookupRequest validation.
     * This is by design — students landing on the page cold should see the form.
     */
    public function test_blank_request_returns_lookup_form_without_errors(): void
    {
        $response = $this->get('/results');

        $response->assertStatus(200);
        $response->assertSessionMissing('errors');
        $response->assertSee('name="admission_number"', false);
    }
}
