<?php

namespace Tests\Unit;

use App\Http\Requests\LookupRequest;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * Feature: student-results-lookup
 * Tests: LookupRequest::prepareForValidation() trims whitespace and uppercases
 *        the admission_number before validation rules are applied.
 * Requirements: 2.4
 */
class AdmissionNumberNormalisationTest extends TestCase
{
    // -----------------------------------------------------------------------
    // Helper: create a LookupRequest with a given raw admission_number and
    // invoke prepareForValidation() via reflection (it is protected), then
    // return the merged value.
    // -----------------------------------------------------------------------

    private function normalise(string $raw): string
    {
        $baseRequest = Request::create('/results', 'GET', ['admission_number' => $raw]);

        $lookupRequest = LookupRequest::createFrom($baseRequest);
        $lookupRequest->setContainer(app());

        // Invoke the protected hook via reflection
        $method = new \ReflectionMethod(LookupRequest::class, 'prepareForValidation');
        $method->setAccessible(true);
        $method->invoke($lookupRequest);

        return $lookupRequest->input('admission_number');
    }

    // -----------------------------------------------------------------------
    // All-lowercase input
    // -----------------------------------------------------------------------

    public function test_all_lowercase_is_uppercased(): void
    {
        $this->assertSame('IN16/00088/24', $this->normalise('in16/00088/24'));
    }

    // -----------------------------------------------------------------------
    // Mixed-case input
    // -----------------------------------------------------------------------

    public function test_mixed_case_is_uppercased(): void
    {
        $this->assertSame('CS21/00123/23', $this->normalise('Cs21/00123/23'));
    }

    public function test_mixed_case_letters_only_uppercased(): void
    {
        $this->assertSame('BUS20/00456/22', $this->normalise('BuS20/00456/22'));
    }

    // -----------------------------------------------------------------------
    // Leading whitespace
    // -----------------------------------------------------------------------

    public function test_leading_spaces_are_trimmed(): void
    {
        $this->assertSame('IN16/00088/24', $this->normalise('   IN16/00088/24'));
    }

    public function test_leading_tab_is_trimmed(): void
    {
        $this->assertSame('IN16/00088/24', $this->normalise("\tIN16/00088/24"));
    }

    // -----------------------------------------------------------------------
    // Trailing whitespace
    // -----------------------------------------------------------------------

    public function test_trailing_spaces_are_trimmed(): void
    {
        $this->assertSame('IN16/00088/24', $this->normalise('IN16/00088/24   '));
    }

    public function test_trailing_newline_is_trimmed(): void
    {
        $this->assertSame('IN16/00088/24', $this->normalise("IN16/00088/24\n"));
    }

    // -----------------------------------------------------------------------
    // Combined: spaces + lowercase
    // -----------------------------------------------------------------------

    public function test_leading_and_trailing_spaces_with_lowercase_are_normalised(): void
    {
        $this->assertSame('IN16/00088/24', $this->normalise('  in16/00088/24  '));
    }

    public function test_spaces_and_mixed_case_are_normalised_together(): void
    {
        $this->assertSame('MAT21/00999/24', $this->normalise('  mat21/00999/24  '));
    }

    // -----------------------------------------------------------------------
    // Already-normalised input is unchanged
    // -----------------------------------------------------------------------

    public function test_already_normalised_value_is_unchanged(): void
    {
        $this->assertSame('ENG16/00001/20', $this->normalise('ENG16/00001/20'));
    }

    // -----------------------------------------------------------------------
    // Normalised value equals strtoupper(trim($input)) for arbitrary strings
    // -----------------------------------------------------------------------

    public function test_normalisation_is_equivalent_to_strtoupper_trim(): void
    {
        $inputs = [
            '  abc01/00001/01  ',
            "\nabc01/00001/01\t",
            'ABC01/00001/01',
            '  AbC01/00001/01',
        ];

        foreach ($inputs as $input) {
            $expected = strtoupper(trim($input));
            $this->assertSame($expected, $this->normalise($input), "Failed for input: '{$input}'");
        }
    }
}
