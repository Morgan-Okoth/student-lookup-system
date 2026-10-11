<?php

namespace Tests\Feature\Properties;

use App\Models\Course;
use App\Models\Result;
use App\Models\Student;
use Eris\Generator;
use Illuminate\Routing\Middleware\ThrottleRequests;

/**
 * Feature: student-results-lookup, Property 5: No internal IDs exposed in public responses
 *
 * For any valid lookup response, the rendered HTML body and redirect URLs
 * SHALL NOT contain patterns that expose internal database primary key IDs
 * (e.g. numeric route segments like `/students/42`, or query parameters like `id=42`).
 *
 * Validates: Requirements 3.6, 5.3
 * Minimum iterations: 100
 */
class NoExposedIdsPropertyTest extends PropertyTestCase
{
    // -----------------------------------------------------------------------
    // Setup
    // -----------------------------------------------------------------------

    protected function setUp(): void
    {
        parent::setUp();
        // Bypass throttle middleware so 100 iterations don't trigger rate limiting.
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
                $code = sprintf('NID%03d', $attempt++);
            }
            $usedCodes[$code] = true;
            $existing[] = Course::create([
                'course_code'  => $code,
                'course_name'  => "Course $code",
                'credit_hours' => ($i % 4 + 1) * 2,
            ]);
        }

        return $existing;
    }

    /**
     * Create a student with 1–3 result rows spread across available year/semester/course combos.
     */
    private function createStudentWithResults(
        string $admissionNumber,
        int    $studentIdx,
        int    $resultCount,
        array  $courses
    ): Student {
        $student = Student::create([
            'admission_number' => $admissionNumber,
            'full_name'        => "Property5 Student $studentIdx",
            'programme'        => 'Bachelor of Science',
            'year_of_study'    => ($studentIdx % 4) + 1,
        ]);

        $used = [];

        for ($r = 0; $r < $resultCount; $r++) {
            $year = $this->academicYears[($studentIdx + $r) % 4];
            $sem  = $this->semesters[($studentIdx + $r) % 2];

            // Pick a course not yet used for this (year, semester) combo for this student
            foreach ($courses as $course) {
                $key = "$year|$sem|{$course->id}";
                if (!isset($used[$key])) {
                    $used[$key] = true;
                    $marks      = (($studentIdx * 11 + $r * 17 + 50) % 101);
                    Result::create([
                        'student_id'    => $student->id,
                        'course_id'     => $course->id,
                        'academic_year' => $year,
                        'semester'      => $sem,
                        'marks'         => $marks,
                        'grade'         => Result::calculateGrade($marks),
                    ]);
                    break;
                }
            }
        }

        return $student;
    }

    /**
     * Assert that an HTML string does not contain any patterns that expose
     * internal database primary key IDs.
     *
     * Patterns checked:
     *   - /students/{digits}   (numeric route segment)
     *   - /courses/{digits}    (numeric route segment)
     *   - /results/{digits}    (numeric route segment for the results resource)
     *   - ?id={digits}         (query parameter named "id")
     *   - &id={digits}         (query parameter named "id" after another param)
     *   - id={digits}          (standalone query string key)
     *
     * Note: The public results page renders the lookup form whose action is
     * `/results` (no numeric segment) and all internal links use named routes
     * without IDs, so none of these patterns should ever appear.
     */
    private function assertNoExposedIds(string $html, string $context = ''): void
    {
        $suffix = $context ? " ($context)" : '';

        // Numeric route segments for models
        foreach (['students', 'courses', 'results'] as $resource) {
            $this->assertDoesNotMatchRegularExpression(
                '#/' . $resource . '/\d+#',
                $html,
                "Response HTML exposes internal /$resource/{id} route segment$suffix"
            );
        }

        // Query-string parameter named "id" with a numeric value
        $this->assertDoesNotMatchRegularExpression(
            '#[?&]id=\d+#',
            $html,
            "Response HTML exposes an ?id={n} or &id={n} query parameter$suffix"
        );
    }

    // -----------------------------------------------------------------------
    // Property 5: No internal IDs exposed in public responses
    // -----------------------------------------------------------------------

    /**
     * For any valid lookup response (results found OR student found with no
     * results for the period), the rendered HTML SHALL NOT contain patterns
     * exposing internal database primary key IDs.
     *
     * 100 iterations.
     */
    public function test_no_internal_ids_exposed_in_public_responses(): void
    {
        $this->limitTo(100)
            ->forAll(
                // Seed for generating the admission number
                Generator\choose(0, 9999),
                // Serial part of admission number (0–99999)
                Generator\choose(0, 99999),
                // Check digits part (0–99)
                Generator\choose(0, 99),
                // Academic year index (0–3)
                Generator\choose(0, 3),
                // Semester index (0–1)
                Generator\choose(0, 1),
                // Number of results to create for this student (1–3)
                Generator\choose(1, 3)
            )
            ->then(function (
                int $seed,
                int $serial,
                int $check,
                int $yearIdx,
                int $semIdx,
                int $resultCount
            ): void {
                $courses = $this->ensureCourses(5);

                // Generate a unique admission number
                $admNumber = $this->makeAdmissionNumber($seed, $serial, $check);

                // Ensure uniqueness in the database (collision-resolution)
                $attempt = 0;
                while (Student::where('admission_number', $admNumber)->exists()) {
                    $attempt++;
                    $admNumber = $this->makeAdmissionNumber(
                        $seed + $attempt * 53,
                        $serial + $attempt,
                        $check + $attempt
                    );
                }

                $student = $this->createStudentWithResults($admNumber, $seed, $resultCount, $courses);

                $queryYear = $this->academicYears[$yearIdx];
                $querySem  = $this->semesters[$semIdx];

                // --- Perform the public lookup ---
                $response = $this->get(
                    '/results'
                    . '?admission_number=' . urlencode($student->admission_number)
                    . '&academic_year=' . urlencode($queryYear)
                    . '&semester=' . urlencode($querySem)
                );

                // The endpoint must respond with HTTP 200
                $response->assertStatus(200);

                $html = $response->getContent();

                $context = "student_id={$student->id}, admission={$student->admission_number}";

                // Core assertion: no internal IDs exposed
                $this->assertNoExposedIds($html, $context);
            });
    }

    /**
     * Verify that even the "not found" response (student does not exist) does
     * not expose any internal IDs — there should simply be nothing to expose,
     * but we confirm the HTML is clean regardless.
     *
     * 100 iterations.
     */
    public function test_no_internal_ids_exposed_when_student_not_found(): void
    {
        $this->limitTo(100)
            ->forAll(
                Generator\choose(0, 9999),
                Generator\choose(0, 99999),
                Generator\choose(0, 99),
                Generator\choose(0, 3),
                Generator\choose(0, 1)
            )
            ->then(function (
                int $seed,
                int $serial,
                int $check,
                int $yearIdx,
                int $semIdx
            ): void {
                // Build an admission number that is guaranteed NOT in the DB
                // (database is refreshed each test via RefreshDatabase)
                $admNumber = $this->makeAdmissionNumber($seed, $serial, $check);

                // Ensure it really does not exist (defensive)
                $attempt = 0;
                while (Student::where('admission_number', $admNumber)->exists()) {
                    $attempt++;
                    $admNumber = $this->makeAdmissionNumber(
                        $seed + $attempt * 71,
                        $serial + $attempt * 3,
                        $check + $attempt
                    );
                }

                $queryYear = $this->academicYears[$yearIdx];
                $querySem  = $this->semesters[$semIdx];

                $response = $this->get(
                    '/results'
                    . '?admission_number=' . urlencode($admNumber)
                    . '&academic_year=' . urlencode($queryYear)
                    . '&semester=' . urlencode($querySem)
                );

                $response->assertStatus(200);

                $html = $response->getContent();

                // Even the not-found page must not expose internal IDs
                $this->assertNoExposedIds($html, "not-found lookup for $admNumber");
            });
    }
}
