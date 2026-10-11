<?php

namespace Tests\Feature\Properties;

use App\Models\Course;
use App\Models\Result;
use App\Models\Student;
use Eris\Generator;
use Illuminate\Routing\Middleware\ThrottleRequests;

/**
 * Feature: student-results-lookup, Property 7: Summary statistics correctness
 *
 * For any non-empty collection of result records displayed on the results page,
 * the rendered summary SHALL show:
 *   - Total Courses = the count of result rows
 *   - Total Credit Hours = the sum of credit_hours across all displayed courses
 *   - Average Mark = round(sum(marks) / count(marks), 2)
 *
 * Validates: Requirements 4.3, 4.4
 * Minimum iterations: 200
 */
class SummaryStatisticsPropertyTest extends PropertyTestCase
{
    // -----------------------------------------------------------------------
    // Setup
    // -----------------------------------------------------------------------

    protected function setUp(): void
    {
        parent::setUp();
        // Bypass throttle middleware so 200 iterations don't trigger rate limiting.
        $this->withoutMiddleware(ThrottleRequests::class);
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    /** Valid academic years */
    private array $academicYears = ['Year 1', 'Year 2', 'Year 3', 'Year 4'];

    /** Valid semesters */
    private array $semesters = ['Semester 1', 'Semester 2'];

    /**
     * Build a valid admission number deterministically from integer seeds.
     * Format: [A-Z]{2,4}[0-9]{2}/[0-9]{5}/[0-9]{2}
     */
    private function makeAdmissionNumber(int $seed, int $serial, int $check): string
    {
        $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $l1      = $letters[$seed % 26];
        $l2      = $letters[($seed + 7) % 26];
        $year2   = $seed % 100;

        return sprintf('%s%s%02d/%05d/%02d', $l1, $l2, $year2, $serial % 100000, $check % 100);
    }

    /**
     * Ensure at least $count distinct courses exist in the database.
     * Returns an array of Course models.
     */
    private function ensureCourses(int $count): array
    {
        $existing = Course::all()->all();
        if (count($existing) >= $count) {
            return array_slice($existing, 0, $count);
        }

        $needed    = $count - count($existing);
        $usedCodes = Course::pluck('course_code')->flip()->all();

        for ($i = 0; $i < $needed; $i++) {
            $attempt = count($existing) + $i;
            $code    = null;
            while ($code === null || isset($usedCodes[$code])) {
                $code = sprintf('SST%03d', $attempt++);
            }
            $usedCodes[$code] = true;
            // Vary credit hours: 1, 2, 3, or 4 credit hours cycling
            $creditHours = (($i % 4) + 1);
            $existing[]  = Course::create([
                'course_code'  => $code,
                'course_name'  => "Summary Course $code",
                'credit_hours' => $creditHours,
            ]);
        }

        return $existing;
    }

    // -----------------------------------------------------------------------
    // Property 7: Summary statistics correctness
    // -----------------------------------------------------------------------

    /**
     * For any randomly generated set of 1–15 results for a student in a given
     * academic year and semester, the rendered results page summary section
     * SHALL display:
     *   - Total Courses  = count of result rows
     *   - Total Credit Hours = sum of credit_hours of the courses in those rows
     *   - Average Mark = round(sum(marks) / count, 2)  formatted to 2 decimal places
     *
     * 200 iterations.
     */
    public function test_summary_statistics_are_correct(): void
    {
        $this->limitTo(200)
            ->forAll(
                // Seed for generating the admission number prefix/year
                Generator\choose(0, 9999),
                // Serial part of the admission number (0–99999)
                Generator\choose(0, 99999),
                // Check-digits part (0–99)
                Generator\choose(0, 99),
                // Academic year index (0–3 → Year 1–Year 4)
                Generator\choose(0, 3),
                // Semester index (0–1 → Semester 1–Semester 2)
                Generator\choose(0, 1),
                // Number of results to create for this student (1–15)
                Generator\choose(1, 15)
            )
            ->then(function (
                int $seed,
                int $serial,
                int $check,
                int $yearIdx,
                int $semIdx,
                int $resultCount
            ): void {
                $academicYear = $this->academicYears[$yearIdx];
                $semester     = $this->semesters[$semIdx];

                // Ensure we have at least 15 courses (max result count) available
                $courses = $this->ensureCourses(15);

                // Build the admission number, resolving any DB uniqueness collisions
                $admNumber = $this->makeAdmissionNumber($seed, $serial, $check);
                $attempt   = 0;
                while (Student::where('admission_number', $admNumber)->exists()) {
                    $attempt++;
                    $admNumber = $this->makeAdmissionNumber(
                        $seed + $attempt * 53,
                        $serial + $attempt,
                        $check + $attempt
                    );
                }

                // Create the student
                $student = Student::create([
                    'admission_number' => $admNumber,
                    'full_name'        => 'Summary Student ' . strtoupper(substr(md5((string)$seed), 0, 5)),
                    'programme'        => 'Bachelor of Science in Testing',
                    'year_of_study'    => ($yearIdx % 4) + 1,
                ]);

                // Create exactly $resultCount results, each on a distinct course.
                // Track which courses are used to satisfy the unique constraint.
                $usedCourses      = [];
                $expectedMarksSum = 0;
                $expectedCredits  = 0;

                for ($i = 0; $i < $resultCount; $i++) {
                    $course = $courses[$i]; // courses pool has at least 15, so index is always valid
                    $marks  = ($i * 13 + 37) % 101; // deterministic, covers 0–100

                    Result::create([
                        'student_id'    => $student->id,
                        'course_id'     => $course->id,
                        'academic_year' => $academicYear,
                        'semester'      => $semester,
                        'marks'         => $marks,
                        'grade'         => Result::calculateGrade($marks),
                    ]);

                    $usedCourses[]     = $course;
                    $expectedMarksSum += $marks;
                    $expectedCredits  += $course->credit_hours;
                }

                // Compute expected summary values exactly as the controller does
                $expectedTotalCourses     = $resultCount;
                $expectedTotalCreditHours = $expectedCredits;
                $expectedAverageMark      = number_format(
                    round($expectedMarksSum / $resultCount, 2),
                    2
                );

                // Perform the public lookup request
                $response = $this->get(
                    '/results'
                    . '?admission_number=' . urlencode($student->admission_number)
                    . '&academic_year=' . urlencode($academicYear)
                    . '&semester=' . urlencode($semester)
                );

                $response->assertStatus(200);

                $html = $response->getContent();

                // --- Assert Total Courses ---
                $this->assertStringContainsString(
                    (string)$expectedTotalCourses,
                    $html,
                    "Summary 'Total Courses' should be $expectedTotalCourses for student "
                    . "[{$student->admission_number}] with $resultCount results in "
                    . "$academicYear / $semester."
                );

                // --- Assert Total Credit Hours ---
                $this->assertStringContainsString(
                    (string)$expectedTotalCreditHours,
                    $html,
                    "Summary 'Total Credit Hours' should be $expectedTotalCreditHours for student "
                    . "[{$student->admission_number}] with $resultCount results in "
                    . "$academicYear / $semester."
                );

                // --- Assert Average Mark (formatted to 2 decimal places) ---
                $this->assertStringContainsString(
                    $expectedAverageMark,
                    $html,
                    "Summary 'Average Mark' should be $expectedAverageMark for student "
                    . "[{$student->admission_number}] with marks sum $expectedMarksSum "
                    . "across $resultCount results."
                );

                // --- Assert the summary section labels are present ---
                $this->assertStringContainsString(
                    'Total Courses',
                    $html,
                    "Summary section must contain the 'Total Courses' label."
                );

                $this->assertStringContainsString(
                    'Total Credit Hours',
                    $html,
                    "Summary section must contain the 'Total Credit Hours' label."
                );

                $this->assertStringContainsString(
                    'Average Mark',
                    $html,
                    "Summary section must contain the 'Average Mark' label."
                );
            });
    }
}
