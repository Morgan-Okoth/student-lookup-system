<?php

// Feature: student-results-lookup, Property 2: Admission number normalisation

namespace Tests\Feature\Properties;

use Eris\Generator;
use Illuminate\Routing\Middleware\ThrottleRequests;

/**
 * Property 2: Admission number normalisation
 *
 * For any string with leading/trailing whitespace or mixed-case letters,
 * the normalised value stored in the request SHALL equal strtoupper(trim($input)).
 *
 * Validates: Requirements 2.4
 * Minimum iterations: 200
 */
class AdmissionNumberNormalisationPropertyTest extends PropertyTestCase
{
    // -----------------------------------------------------------------------
    // Setup — bypass throttle so 200+ iterations don't hit rate limiting
    // -----------------------------------------------------------------------

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ThrottleRequests::class);
    }

    // -----------------------------------------------------------------------
    // Property 2: Admission number normalisation
    //
    // The LookupRequest::prepareForValidation() method trims whitespace and
    // converts to uppercase.  We verify this by submitting requests whose
    // admission_number contains leading/trailing spaces or mixed-case letters
    // and checking that the value arriving at the controller equals
    // strtoupper(trim($input)).
    //
    // Strategy: generate a "clean" uppercase admission number that satisfies
    // the format regex, then optionally wrap it in spaces and/or convert some
    // letters to lowercase.  We then submit the modified string and verify
    // that no admission_number error is returned — which can only happen if
    // normalisation produced the valid uppercase+trimmed form.
    // -----------------------------------------------------------------------

    /** Mirrors the format regex from LookupRequest */
    private const FORMAT_REGEX = '/^[A-Z]{2,4}[0-9]{2}\/[0-9]{5}\/[0-9]{2}$/';

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    /**
     * Submit a GET lookup request with the given admission_number.
     * academic_year and semester are always valid so any validation failure
     * is attributable solely to admission_number.
     */
    private function lookup(string $admissionNumber): \Illuminate\Testing\TestResponse
    {
        return $this->withSession([])->get(
            '/results?admission_number=' . urlencode($admissionNumber)
            . '&academic_year=Year+1'
            . '&semester=Semester+1'
        );
    }

    /**
     * Return true when the response does NOT contain an admission_number
     * validation error (i.e. normalisation produced a valid value).
     */
    private function responseHasNoAdmissionNumberError(\Illuminate\Testing\TestResponse $response): bool
    {
        $status = $response->getStatusCode();

        if ($status === 200) {
            return true;
        }

        // Only redirect responses carry a session with validation errors
        if ($status !== 302) {
            return false;
        }

        $errors = $response->getSession()->get('errors');

        return $errors === null || ! $errors->has('admission_number');
    }

    // -----------------------------------------------------------------------
    // Property 2a: Strings with leading/trailing whitespace are normalised
    // -----------------------------------------------------------------------

    /**
     * For any valid admission number wrapped in leading/trailing spaces,
     * the validator SHALL accept it after normalisation (trim is applied).
     *
     * **Validates: Requirements 2.4**
     *
     * 200 iterations.
     */
    public function test_leading_trailing_spaces_are_trimmed_before_validation(): void
    {
        $letters = range('A', 'Z');

        $this->limitTo(200)
            ->forAll(
                // prefix length: 2, 3, or 4
                Generator\elements(2, 3, 4),
                // prefix letters (slots 0–3)
                Generator\elements(...$letters),
                Generator\elements(...$letters),
                Generator\elements(...$letters),
                Generator\elements(...$letters),
                // two-digit year part
                Generator\choose(0, 99),
                // five-digit serial
                Generator\choose(0, 99999),
                // two-digit check
                Generator\choose(0, 99),
                // leading spaces: 1–5
                Generator\choose(1, 5),
                // trailing spaces: 1–5
                Generator\choose(1, 5)
            )
            ->then(function (
                int    $prefixLen,
                string $l1,
                string $l2,
                string $l3,
                string $l4,
                int    $yearPart,
                int    $serial,
                int    $check,
                int    $leading,
                int    $trailing
            ): void {
                // Build a valid uppercase admission number
                $prefix = implode('', array_slice([$l1, $l2, $l3, $l4], 0, $prefixLen));
                $clean  = sprintf('%s%02d/%05d/%02d', $prefix, $yearPart, $serial, $check);

                // Sanity check: generator must produce a matching base string
                $this->assertMatchesRegularExpression(
                    self::FORMAT_REGEX,
                    $clean,
                    "Generator produced non-matching base string: '$clean'"
                );

                // Wrap in spaces
                $withSpaces = str_repeat(' ', $leading) . $clean . str_repeat(' ', $trailing);

                // After normalisation: strtoupper(trim($withSpaces)) === $clean
                $this->assertSame(
                    $clean,
                    strtoupper(trim($withSpaces)),
                    "Normalisation formula does not produce expected clean string."
                );

                $response = $this->lookup($withSpaces);

                $this->assertTrue(
                    $this->responseHasNoAdmissionNumberError($response),
                    "Expected '$withSpaces' to pass validation after trimming, but an error was returned."
                );
            });
    }

    // -----------------------------------------------------------------------
    // Property 2b: Mixed-case strings are uppercased before validation
    // -----------------------------------------------------------------------

    /**
     * For any valid admission number whose prefix letters are submitted in
     * mixed or lowercase form, the validator SHALL accept it after normalisation
     * (strtoupper is applied).
     *
     * **Validates: Requirements 2.4**
     *
     * 200 iterations.
     */
    public function test_mixed_case_admission_numbers_are_uppercased_before_validation(): void
    {
        $lowercaseLetters = range('a', 'z');
        $uppercaseLetters = range('A', 'Z');
        $allLetters       = array_merge($uppercaseLetters, $lowercaseLetters);

        $this->limitTo(200)
            ->forAll(
                // prefix length: 2, 3, or 4
                Generator\elements(2, 3, 4),
                // prefix letter slots — include lowercase variants
                Generator\elements(...$allLetters),
                Generator\elements(...$allLetters),
                Generator\elements(...$allLetters),
                Generator\elements(...$allLetters),
                // two-digit year part
                Generator\choose(0, 99),
                // five-digit serial
                Generator\choose(0, 99999),
                // two-digit check
                Generator\choose(0, 99)
            )
            ->then(function (
                int    $prefixLen,
                string $l1,
                string $l2,
                string $l3,
                string $l4,
                int    $yearPart,
                int    $serial,
                int    $check
            ): void {
                // Build potentially-mixed-case prefix
                $prefix = implode('', array_slice([$l1, $l2, $l3, $l4], 0, $prefixLen));
                $raw    = sprintf('%s%02d/%05d/%02d', $prefix, $yearPart, $serial, $check);

                // The normalised value must equal the expected formula
                $expected = strtoupper(trim($raw));

                $this->assertSame(
                    $expected,
                    strtoupper(trim($raw)),
                    "Normalisation formula check failed."
                );

                // The normalised value must also satisfy the format regex
                $this->assertMatchesRegularExpression(
                    self::FORMAT_REGEX,
                    $expected,
                    "Normalised string '$expected' does not match the format regex."
                );

                $response = $this->lookup($raw);

                $this->assertTrue(
                    $this->responseHasNoAdmissionNumberError($response),
                    "Expected '$raw' to pass validation after uppercasing, but an error was returned."
                );
            });
    }

    // -----------------------------------------------------------------------
    // Property 2c: Combined whitespace and mixed-case are both normalised
    // -----------------------------------------------------------------------

    /**
     * For any valid admission number that has BOTH leading/trailing whitespace
     * AND mixed-case letters, the combined normalisation (trim + strtoupper)
     * SHALL produce the canonical valid form.
     *
     * **Validates: Requirements 2.4**
     *
     * 200 iterations.
     */
    public function test_whitespace_and_mixed_case_combined_are_normalised(): void
    {
        $lowercaseLetters = range('a', 'z');
        $uppercaseLetters = range('A', 'Z');
        $allLetters       = array_merge($uppercaseLetters, $lowercaseLetters);

        $this->limitTo(200)
            ->forAll(
                // prefix length: 2, 3, or 4
                Generator\elements(2, 3, 4),
                Generator\elements(...$allLetters),
                Generator\elements(...$allLetters),
                Generator\elements(...$allLetters),
                Generator\elements(...$allLetters),
                // two-digit year part
                Generator\choose(0, 99),
                // five-digit serial
                Generator\choose(0, 99999),
                // two-digit check
                Generator\choose(0, 99),
                // leading spaces: 1–4
                Generator\choose(1, 4),
                // trailing spaces: 1–4
                Generator\choose(1, 4)
            )
            ->then(function (
                int    $prefixLen,
                string $l1,
                string $l2,
                string $l3,
                string $l4,
                int    $yearPart,
                int    $serial,
                int    $check,
                int    $leading,
                int    $trailing
            ): void {
                // Build mixed-case prefix wrapped in spaces
                $prefix = implode('', array_slice([$l1, $l2, $l3, $l4], 0, $prefixLen));
                $base   = sprintf('%s%02d/%05d/%02d', $prefix, $yearPart, $serial, $check);
                $raw    = str_repeat(' ', $leading) . $base . str_repeat(' ', $trailing);

                // Verify the normalisation formula
                $normalised = strtoupper(trim($raw));

                $this->assertMatchesRegularExpression(
                    self::FORMAT_REGEX,
                    $normalised,
                    "Normalised value '$normalised' does not match the format regex."
                );

                // The normalised value must be the clean uppercase form (no spaces)
                $this->assertSame(
                    strtoupper(trim($raw)),
                    $normalised,
                    "strtoupper(trim()) did not produce the expected value."
                );

                $response = $this->lookup($raw);

                $this->assertTrue(
                    $this->responseHasNoAdmissionNumberError($response),
                    "Expected '$raw' to pass validation after combined normalisation, but an error was returned."
                );
            });
    }
}
