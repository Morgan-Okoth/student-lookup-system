<?php

namespace Tests\Unit;

use App\Http\Controllers\ResultsController;
use App\Models\Course;
use App\Models\Result;
use Illuminate\Database\Eloquent\Collection;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Feature: student-results-lookup
 * Tests: ResultsController::buildSummary() summary statistics computation.
 * Requirements: 4.3, 4.4
 */
class AverageMarkCalculationTest extends TestCase
{
    // -----------------------------------------------------------------------
    // Helper: build an Eloquent Collection of unsaved Result/Course objects
    // -----------------------------------------------------------------------

    /**
     * Create a lightweight in-memory Result model with an attached Course.
     *
     * @param  int  $marks
     * @param  int  $creditHours
     * @return Result
     */
    private function makeResult(int $marks, int $creditHours): Result
    {
        $course               = new Course();
        $course->credit_hours = $creditHours;

        $result        = new Result();
        $result->marks = $marks;
        $result->setRelation('course', $course);

        return $result;
    }

    /**
     * Invoke the private buildSummary method via Reflection.
     *
     * @param  Collection  $results
     * @return array{total_courses: int, total_credit_hours: int, average_mark: float}
     */
    private function callBuildSummary(Collection $results): array
    {
        $method = new ReflectionMethod(ResultsController::class, 'buildSummary');

        return $method->invoke(new ResultsController(), $results);
    }

    // -----------------------------------------------------------------------
    // Single result
    // -----------------------------------------------------------------------

    public function test_single_result_has_correct_summary(): void
    {
        $results = new Collection([
            $this->makeResult(marks: 80, creditHours: 3),
        ]);

        $summary = $this->callBuildSummary($results);

        $this->assertSame(1, $summary['total_courses']);
        $this->assertSame(3, $summary['total_credit_hours']);
        $this->assertSame(80.0, $summary['average_mark']);
    }

    // -----------------------------------------------------------------------
    // Multiple results — clean average
    // -----------------------------------------------------------------------

    public function test_multiple_results_with_exact_average(): void
    {
        // marks: 60, 70, 80 → sum = 210, count = 3, average = 70.00
        $results = new Collection([
            $this->makeResult(marks: 60, creditHours: 3),
            $this->makeResult(marks: 70, creditHours: 4),
            $this->makeResult(marks: 80, creditHours: 3),
        ]);

        $summary = $this->callBuildSummary($results);

        $this->assertSame(3, $summary['total_courses']);
        $this->assertSame(10, $summary['total_credit_hours']);
        $this->assertSame(70.0, $summary['average_mark']);
    }

    // -----------------------------------------------------------------------
    // Non-trivial average (spec example: marks [72, 55, 38] → 55.00)
    // -----------------------------------------------------------------------

    public function test_average_mark_rounded_to_two_decimal_places(): void
    {
        // marks: 72, 55, 38 → sum = 165, count = 3, average = 55.00
        $results = new Collection([
            $this->makeResult(marks: 72, creditHours: 3),
            $this->makeResult(marks: 55, creditHours: 3),
            $this->makeResult(marks: 38, creditHours: 3),
        ]);

        $summary = $this->callBuildSummary($results);

        $this->assertSame(3, $summary['total_courses']);
        $this->assertSame(9, $summary['total_credit_hours']);
        $this->assertSame(55.0, $summary['average_mark']);
    }

    // -----------------------------------------------------------------------
    // Non-trivial average that truly requires rounding
    // -----------------------------------------------------------------------

    public function test_average_mark_rounds_correctly_for_repeating_decimal(): void
    {
        // marks: 70, 60, 50 → sum = 180, count = 3, average = 60.00
        // marks: 100, 0 → sum = 100, count = 2, average = 50.00
        // marks: 71, 62 → sum = 133, count = 2, average = 66.50
        $results = new Collection([
            $this->makeResult(marks: 71, creditHours: 2),
            $this->makeResult(marks: 62, creditHours: 2),
        ]);

        $summary = $this->callBuildSummary($results);

        $this->assertSame(2, $summary['total_courses']);
        $this->assertSame(4, $summary['total_credit_hours']);
        $this->assertSame(66.5, $summary['average_mark']);
    }

    public function test_average_mark_rounds_third_decimal_up(): void
    {
        // marks: 1, 1, 2 → sum = 4, count = 3 → 1.333... → round to 1.33
        $results = new Collection([
            $this->makeResult(marks: 1, creditHours: 1),
            $this->makeResult(marks: 1, creditHours: 1),
            $this->makeResult(marks: 2, creditHours: 1),
        ]);

        $summary = $this->callBuildSummary($results);

        $this->assertSame(3, $summary['total_courses']);
        $this->assertEqualsWithDelta(1.33, $summary['average_mark'], 0.005);
    }

    // -----------------------------------------------------------------------
    // Empty collection — average should be 0, no division by zero
    // -----------------------------------------------------------------------

    public function test_empty_results_returns_zero_summary(): void
    {
        $results = new Collection([]);

        $summary = $this->callBuildSummary($results);

        $this->assertSame(0, $summary['total_courses']);
        $this->assertSame(0, $summary['total_credit_hours']);
        $this->assertSame(0, $summary['average_mark']);
    }

    // -----------------------------------------------------------------------
    // Credit hours are summed correctly across heterogeneous courses
    // -----------------------------------------------------------------------

    public function test_total_credit_hours_are_summed_correctly(): void
    {
        // credit hours: 3, 4, 2, 3 → total = 12
        $results = new Collection([
            $this->makeResult(marks: 70, creditHours: 3),
            $this->makeResult(marks: 65, creditHours: 4),
            $this->makeResult(marks: 55, creditHours: 2),
            $this->makeResult(marks: 42, creditHours: 3),
        ]);

        $summary = $this->callBuildSummary($results);

        $this->assertSame(4, $summary['total_courses']);
        $this->assertSame(12, $summary['total_credit_hours']);
        // average = (70+65+55+42)/4 = 232/4 = 58.00
        $this->assertSame(58.0, $summary['average_mark']);
    }
}
