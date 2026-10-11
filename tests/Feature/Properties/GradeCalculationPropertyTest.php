<?php

namespace Tests\Feature\Properties;

// Feature: student-results-lookup, Property 8: Grade calculation covers all mark bands

use App\Models\Result;
use Eris\Generator;

/**
 * Property 8: Grade calculation covers all mark bands
 *
 * Exhaustively tests every integer in [0, 100] and additionally uses
 * Eris to generate random integers in the same range, verifying that
 * Result::calculateGrade() returns the correct grade for every value.
 *
 * Validates: Requirements 8.3, 8.4, 8.5, 8.6, 8.7
 *
 * Grade bands:
 *   70–100 → A
 *   60–69  → B
 *   50–59  → C
 *   40–49  → D
 *    0–39  → F
 */
class GradeCalculationPropertyTest extends PropertyTestCase
{
    // -----------------------------------------------------------------------
    // Helper
    // -----------------------------------------------------------------------

    /**
     * Compute the expected grade using the same logic as Result::calculateGrade().
     * Kept here as an independent reference implementation for assertion comparison.
     */
    private function expectedGrade(int $marks): string
    {
        return match (true) {
            $marks >= 70 => 'A',
            $marks >= 60 => 'B',
            $marks >= 50 => 'C',
            $marks >= 40 => 'D',
            default      => 'F',
        };
    }

    // -----------------------------------------------------------------------
    // Exhaustive test — all 101 integer values 0–100
    // -----------------------------------------------------------------------

    /**
     * Exhaustively verify Result::calculateGrade() for every mark value 0–100.
     *
     * This loop covers all 101 possible valid mark values so no boundary
     * can be silently wrong.
     */
    public function test_grade_calculation_exhaustive_all_marks_0_to_100(): void
    {
        for ($marks = 0; $marks <= 100; $marks++) {
            $expected = $this->expectedGrade($marks);
            $actual   = Result::calculateGrade($marks);

            $this->assertSame(
                $expected,
                $actual,
                "Result::calculateGrade($marks) should return '$expected' but returned '$actual'."
            );
        }
    }

    // -----------------------------------------------------------------------
    // Property-based test — random integers in [0, 100]
    // -----------------------------------------------------------------------

    /**
     * For any randomly chosen integer in [0, 100], Result::calculateGrade()
     * must return the same grade as the independent expectedGrade() helper.
     *
     * Uses Eris choose(0, 100) generator with 200 iterations to probe the
     * full range including boundaries with repeated random sampling.
     *
     * Validates: Requirements 8.3, 8.4, 8.5, 8.6, 8.7
     */
    public function test_grade_calculation_property_random_marks_in_range(): void
    {
        $this->limitTo(200)
            ->forAll(
                Generator\choose(0, 100)
            )
            ->then(function (int $marks): void {
                $expected = $this->expectedGrade($marks);
                $actual   = Result::calculateGrade($marks);

                $this->assertSame(
                    $expected,
                    $actual,
                    "Result::calculateGrade($marks) should return '$expected' but returned '$actual'."
                );
            });
    }

    // -----------------------------------------------------------------------
    // Boundary spot-checks
    // -----------------------------------------------------------------------

    /**
     * Deterministically verify all eight boundary values to ensure no
     * off-by-one error exists at any grade band transition.
     *
     * Boundaries:
     *   39 → F,  40 → D  (F/D boundary)
     *   49 → D,  50 → C  (D/C boundary)
     *   59 → C,  60 → B  (C/B boundary)
     *   69 → B,  70 → A  (B/A boundary)
     */
    public function test_grade_calculation_boundary_values(): void
    {
        $boundaries = [
            39  => 'F',
            40  => 'D',
            49  => 'D',
            50  => 'C',
            59  => 'C',
            60  => 'B',
            69  => 'B',
            70  => 'A',
            0   => 'F',
            100 => 'A',
        ];

        foreach ($boundaries as $marks => $expected) {
            $actual = Result::calculateGrade($marks);
            $this->assertSame(
                $expected,
                $actual,
                "Boundary: Result::calculateGrade($marks) should return '$expected' but returned '$actual'."
            );
        }
    }
}
