<?php

namespace Tests\Feature\Properties;

use Eris\Generator;
use Illuminate\Routing\Middleware\ThrottleRequests;

/**
 * Feature: student-results-lookup, Property 3: Admission number format validation
 *
 * For any string that does NOT match [A-Z]{2,4}[0-9]{2}/[0-9]{5}/[0-9]{2},
 * the validator SHALL reject it with a format error.
 * Conversely, for any string that DOES match the pattern, the validator SHALL
 * accept it (no format error).
 *
 * Validates: Requirements 2.5
 * Minimum iterations: 200
 */
class AdmissionNumberFormatPropertyTest extends PropertyTestCase
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
    // Regex constant (mirrors LookupRequest rules)
    // -----------------------------------------------------------------------

    private const FORMAT_REGEX = '/^[A-Z]{2,4}[0-9]{2}\/[0-9]{5}\/[0-9]{2}$/';

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    /**
     * Send a GET lookup request and return the response.
     * academic_year and semester are always valid so any validation
     * failure is attributable solely to admission_number.
     *
     * We use withSession([]) to flush any leftover session state from
     * previous iterations (important when many requests run in one test).
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
     * Return true when the given response indicates an admission_number
     * validation error.
     *
     * When LookupRequest fails, Laravel redirects (302) and flashes errors
     * to the session.  We check both the status code and the session payload.
     */
    private function responseHasAdmissionNumberError(\Illuminate\Testing\TestResponse $response): bool
    {
        if ($response->getStatusCode() !== 302) {
            return false;
        }

        $errors = $response->getSession()->get('errors');

        return $errors !== null && $errors->has('admission_number');
    }

    /**
     * Return true when the response does NOT contain an admission_number error
     * (validation passed for that field).
     *
     * A 200 response means the controller reached the student-lookup stage
     * (validation passed).  A 302 without an admission_number key means
     * another field failed, not admission_number.
     */
    private function responseHasNoAdmissionNumberError(\Illuminate\Testing\TestResponse $response): bool
    {
        if ($response->getStatusCode() === 200) {
            return true;
        }

        // 302 redirect — check that admission_number is not in the errors bag
        $errors = $response->getSession()->get('errors');

        return $errors === null || ! $errors->has('admission_number');
    }

    // -----------------------------------------------------------------------
    // Property 3a: Valid-format strings are accepted
    // -----------------------------------------------------------------------

    /**
     * Strings that DO match ^[A-Z]{2,4}[0-9]{2}/[0-9]{5}/[0-9]{2}$
     * must NOT trigger an admission_number validation error.
     *
     * 200 iterations.
     */
    public function test_valid_format_admission_numbers_pass_validation(): void
    {
        $letters = range('A', 'Z');

        $this->limitTo(200)
            ->forAll(
                // prefix length: 2, 3, or 4
                Generator\elements(2, 3, 4),
                // first letter
                Generator\elements(...$letters),
                // second letter
                Generator\elements(...$letters),
                // third letter or '' (used only when prefix >= 3)
                Generator\elements(...array_merge($letters, [''])),
                // fourth letter or '' (used only when prefix == 4)
                Generator\elements(...array_merge($letters, [''])),
                // two-digit year part (00–99)
                Generator\choose(0, 99),
                // five-digit serial (00000–99999)
                Generator\choose(0, 99999),
                // two-digit check (00–99)
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
                // Build prefix of exactly $prefixLen uppercase letters.
                // Fill any '' slots with 'X' to ensure we always have 4 letters to slice.
                $slots = [$l1, $l2, ($l3 === '' ? 'X' : $l3), ($l4 === '' ? 'X' : $l4)];
                $prefix = implode('', array_slice($slots, 0, $prefixLen));

                $admissionNumber = sprintf('%s%02d/%05d/%02d', $prefix, $yearPart, $serial, $check);

                // Generator sanity-check: must produce a matching string
                $this->assertMatchesRegularExpression(
                    self::FORMAT_REGEX,
                    $admissionNumber,
                    "Generator produced non-matching string: '$admissionNumber'"
                );

                $response = $this->lookup($admissionNumber);

                $this->assertTrue(
                    $this->responseHasNoAdmissionNumberError($response),
                    "Expected '$admissionNumber' to pass format validation, but it triggered an error."
                );
            });
    }

    // -----------------------------------------------------------------------
    // Property 3b: Invalid-format strings are rejected
    // -----------------------------------------------------------------------

    /**
     * Strings that structurally violate the format must trigger an
     * admission_number validation error.
     *
     * Each closure in $violationFactories produces a structurally-broken
     * string from three seed integers ($a, $b, $c).  The guard inside the
     * closure skips the assertion if — after normalisation — the string
     * coincidentally matches the pattern (this can happen for the lowercase
     * generator which strtoupper(trim()) turns valid).
     *
     * 200 iterations.
     */
    public function test_invalid_format_admission_numbers_fail_validation(): void
    {
        /**
         * Each factory receives ($a, $b, $c) and returns either:
         *  - a string that is structurally invalid (the common case), or
         *  - null to signal "skip this iteration" (when the factory cannot
         *    guarantee invalidity for these particular seed values).
         *
         * All arithmetic is deterministic and bounded to avoid overflow.
         */
        $violationFactories = [
            // 0. Prefix too short (1 letter only)
            fn($a, $b, $c) => sprintf(
                '%s%02d/%05d/%02d',
                chr(65 + ($a % 26)),
                $b % 100,
                $c % 100000,
                $a % 100
            ),

            // 1. Prefix too long (5 uppercase letters)
            fn($a, $b, $c) => sprintf(
                'ABCDE%02d/%05d/%02d',
                $b % 100,
                $c % 100000,
                $a % 100
            ),

            // 2. First slash missing → XX0000000/00
            fn($a, $b, $c) => sprintf(
                '%s%02d%05d/%02d',
                chr(65 + ($a % 26)) . chr(65 + ($b % 26)),
                $a % 100,
                $c % 100000,
                $b % 100
            ),

            // 3. Second slash missing → XX00/0000000
            fn($a, $b, $c) => sprintf(
                '%s%02d/%05d%02d',
                chr(65 + ($a % 26)) . chr(65 + ($b % 26)),
                $a % 100,
                $c % 100000,
                $b % 100
            ),

            // 4. Serial segment only 4 digits → XX00/0000/00
            fn($a, $b, $c) => sprintf(
                '%s%02d/%04d/%02d',
                chr(65 + ($a % 26)) . chr(65 + ($b % 26)),
                $a % 100,
                $c % 10000,
                $b % 100
            ),

            // 5. Serial segment 6 digits → XX00/000000/00
            fn($a, $b, $c) => sprintf(
                '%s%02d/%06d/%02d',
                chr(65 + ($a % 26)) . chr(65 + ($b % 26)),
                $a % 100,
                $c % 1000000,
                $b % 100
            ),

            // 6. Year segment only 1 digit → XX0/00000/00
            // Use $a % 10 to always get a single-digit value (0–9).
            fn($a, $b, $c) => sprintf(
                '%s%01d/%05d/%02d',
                chr(65 + ($a % 26)) . chr(65 + ($b % 26)),
                $a % 10,
                $c % 100000,
                $b % 100
            ),

            // 7. Check segment only 1 digit → XX00/00000/0
            // Use $b % 10 to always get a single-digit value (0–9).
            fn($a, $b, $c) => sprintf(
                '%s%02d/%05d/%01d',
                chr(65 + ($a % 26)) . chr(65 + ($b % 26)),
                $a % 100,
                $c % 100000,
                $b % 10
            ),

            // 8. Digit in prefix position → 1A00/00000/00
            fn($a, $b, $c) => sprintf(
                '1%s%02d/%05d/%02d',
                chr(65 + ($a % 26)),
                $a % 100,
                $c % 100000,
                $b % 100
            ),

            // 9. Hyphen separators instead of slashes → XX00-00000-00
            fn($a, $b, $c) => sprintf(
                '%s%02d-%05d-%02d',
                chr(65 + ($a % 26)) . chr(65 + ($b % 26)),
                $a % 100,
                $c % 100000,
                $b % 100
            ),

            // 10. Extra trailing character → XX00/00000/00X
            fn($a, $b, $c) => sprintf(
                '%s%02d/%05d/%02dX',
                chr(65 + ($a % 26)) . chr(65 + ($b % 26)),
                $a % 100,
                $c % 100000,
                $b % 100
            ),

            // 11. Empty string
            fn($a, $b, $c) => '',

            // 12. Whitespace only
            fn($a, $b, $c) => '   ',

            // 13. Short noise (1–4 chars) — never long enough to match pattern
            fn($a, $b, $c) => substr('ABCDE', 0, max(1, $a % 4)),

            // 14. Check segment 3 digits → XX00/00000/000
            fn($a, $b, $c) => sprintf(
                '%s%02d/%05d/%03d',
                chr(65 + ($a % 26)) . chr(65 + ($b % 26)),
                $a % 100,
                $c % 100000,
                $b % 100
            ),

            // 15. Lowercase prefix — SKIP via null when strtoupper would make it valid
            // (these are correctly handled by prepareForValidation; see Property 2)
            fn($a, $b, $c) => null,
        ];

        $factoryCount = count($violationFactories);

        $this->limitTo(200)
            ->forAll(
                Generator\choose(0, $factoryCount - 1),
                Generator\choose(0, 9999),
                Generator\choose(0, 9999),
                Generator\choose(0, 9999)
            )
            ->then(function (int $idx, int $a, int $b, int $c) use ($violationFactories): void {
                $raw = $violationFactories[$idx]($a, $b, $c);

                // Skip: factory explicitly opted out for this combination
                if ($raw === null) {
                    return;
                }

                // Skip: after prepareForValidation normalisation the string is valid
                // (this is correct behaviour — normalisation is tested by Property 2)
                $normalised = strtoupper(trim($raw));
                if (preg_match(self::FORMAT_REGEX, $normalised)) {
                    return;
                }

                $response = $this->lookup($raw);

                $this->assertTrue(
                    $this->responseHasAdmissionNumberError($response),
                    sprintf(
                        "Expected format validation error for factory[%d] a=%d b=%d c=%d raw='%s' normalised='%s'",
                        $idx, $a, $b, $c, $raw, $normalised
                    )
                );
            });
    }

    // -----------------------------------------------------------------------
    // Deterministic spot-checks: known-valid admission numbers
    // -----------------------------------------------------------------------

    /**
     * A curated list of strings that match the pattern and must be accepted.
     * These are representative rather than exhaustive — the property test above
     * provides broader coverage.
     */
    public function test_known_valid_admission_numbers_pass_validation(): void
    {
        $validNumbers = [
            'IN16/00088/24',     // spec example
            'AB00/00000/00',     // minimum prefix (2 letters), all zeros
            'ABCD99/99999/99',   // maximum prefix (4 letters), all nines
            'CS20/12345/23',
            'MAT21/00001/22',
            'ENG19/54321/21',
            'IT22/00100/24',
            'BUS18/09876/20',
            // Leading/trailing whitespace — normalised to valid by prepareForValidation
            ' IN16/00088/24',
            'IN16/00088/24 ',
            // Lowercase prefix — normalised to uppercase by prepareForValidation
            'in16/00088/24',
        ];

        foreach ($validNumbers as $raw) {
            $response = $this->lookup($raw);
            $this->assertTrue(
                $this->responseHasNoAdmissionNumberError($response),
                "Expected '$raw' to pass format validation (after normalisation), but it triggered an error."
            );
        }
    }

    // -----------------------------------------------------------------------
    // Deterministic spot-checks: known-invalid admission numbers
    // -----------------------------------------------------------------------

    /**
     * A curated list of strings that remain invalid even after normalisation,
     * and must be rejected with an admission_number error.
     */
    public function test_known_invalid_admission_numbers_fail_validation(): void
    {
        $invalidNumbers = [
            '',                  // empty string
            'IN/00088/24',       // year segment missing (no digits before first slash)
            'IN16/0088/24',      // serial only 4 digits
            'IN16/000088/24',    // serial 6 digits
            'IN1600088/24',      // first slash missing
            'IN16/0008824',      // second slash missing
            'IN1600088024',      // both slashes missing
            '1N16/00088/24',     // digit in prefix position
            'ABCDE16/00088/24',  // prefix 5 letters (too long)
            'A16/00088/24',      // prefix 1 letter (too short)
            'IN16/00088/2',      // check segment only 1 digit
            'IN16/00088/244',    // check segment 3 digits
            'IN16-00088-24',     // hyphens instead of slashes
        ];

        foreach ($invalidNumbers as $raw) {
            $response = $this->lookup($raw);
            $this->assertTrue(
                $this->responseHasAdmissionNumberError($response),
                "Expected '$raw' to fail format validation, but it was accepted."
            );
        }
    }
}
