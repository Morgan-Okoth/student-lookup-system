<?php

namespace Tests\Feature\Properties;

// Feature: student-results-lookup, Property 11: Cascade delete removes associated results

use App\Models\Course;
use App\Models\Result;
use App\Models\Student;
use App\Models\User;
use Eris\Generator;

/**
 * Property 11: Cascade delete removes associated results
 *
 * For any student record that has one or more associated result records,
 * deleting the student SHALL remove all result records whose `student_id`
 * equals that student's `id`. The `results` table SHALL contain zero rows
 * for the deleted student's former `id` after the operation completes.
 *
 * Validates: Requirements 6.4, 9.5
 * Minimum iterations: 100
 */
class CascadeDeletePropertyTest extends PropertyTestCase
{
    // -----------------------------------------------------------------------
    // Fixtures
    // -----------------------------------------------------------------------

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a single admin user reused across all assertions within an iteration.
        $this->admin = User::create([
            'name'     => 'Admin User',
            'email'    => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    /** Valid academic years */
    private array $academicYears = ['Year 1', 'Year 2', 'Year 3', 'Year 4'];

    /** Valid semesters */
    private array $semesters = ['Semester 1', 'Semester 2'];

    /**
     * Build a valid admission number deterministically from three integers.
     * Format: [A-Z]{2,4}[0-9]{2}/[0-9]{5}/[0-9]{2}
     */
    private function makeAdmissionNumber(int $seed, int $serial, int $check): string
    {
        $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $l1      = $letters[$seed % 26];
        $l2      = $letters[($seed + 3) % 26];
        $year2   = $seed % 100;

        return sprintf('%s%s%02d/%05d/%02d', $l1, $l2, $year2, $serial % 100000, $check % 100);
    }

    /**
     * Generate an admission number guaranteed to be unique in the current DB.
     */
    private function uniqueAdmissionNumber(int $seed, int $serial, int $check): string
    {
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

        return $admNumber;
    }

    /**
     * Ensure at least $count courses exist in the DB, creating new ones as needed.
     * Returns an array of Course models.
     */
    private function ensureCourses(int $count): array
    {
        $existing = Course::all()->all();
        if (count($existing) >= $count) {
            return array_slice($existing, 0, $count);
        }

        $needed   = $count - count($existing);
        $usedCodes = Course::pluck('course_code')->flip()->all();

        for ($i = 0; $i < $needed; $i++) {
            $attempt = count($existing) + $i;
            $code    = null;
            while ($code === null || isset($usedCodes[$code])) {
                $code = sprintf('CDT%03d', $attempt);
                $attempt++;
            }
            $usedCodes[$code] = true;
            $existing[] = Course::create([
                'course_code'  => $code,
                'course_name'  => "Cascade Delete Test Course $code",
                'credit_hours' => (($i % 3) + 1) * 2,
            ]);
        }

        return $existing;
    }

    // -----------------------------------------------------------------------
    // Property 11: Cascade delete removes associated results
    // -----------------------------------------------------------------------

    /**
     * For any student with 1–5 associated results, deleting the student via
     * the admin destroy endpoint SHALL leave zero result rows for that
     * student's former `id`.
     *
     * 100 iterations.
     *
     * Validates: Requirements 6.4, 9.5
     */
    public function test_cascade_delete_removes_associated_results(): void
    {
        $this->limitTo(100)
            ->forAll(
                Generator\choose(0, 9999),   // seed for letter prefix of admission number
                Generator\choose(0, 99999),  // serial (5-digit part)
                Generator\choose(0, 99),     // check (2-digit part)
                Generator\choose(1, 5)       // number of results to create (1–5)
            )
            ->then(function (int $seed, int $serial, int $check, int $resultCount): void {
                // Ensure we have enough courses for the results
                $courses = $this->ensureCourses($resultCount);

                // --- Create a student ---
                $admNumber = $this->uniqueAdmissionNumber($seed, $serial, $check);
                $student   = Student::create([
                    'admission_number' => $admNumber,
                    'full_name'        => 'Cascade Test Student ' . $seed,
                    'programme'        => 'Bachelor of Science',
                    'year_of_study'    => ($seed % 4) + 1,
                ]);

                $studentId = $student->id;

                // --- Create results for the student (1–5) ---
                // Use distinct (course, year, semester) combinations to respect unique constraint
                $used    = [];
                $created = 0;

                foreach ($this->academicYears as $year) {
                    foreach ($this->semesters as $semester) {
                        if ($created >= $resultCount) {
                            break 2;
                        }
                        foreach ($courses as $idx => $course) {
                            if ($created >= $resultCount) {
                                break 2;
                            }
                            $key = $year . '|' . $semester . '|' . $course->id;
                            if (isset($used[$key])) {
                                continue;
                            }
                            $used[$key] = true;

                            $marks = ($seed + $created * 17 + 40) % 101;
                            Result::create([
                                'student_id'    => $studentId,
                                'course_id'     => $course->id,
                                'academic_year' => $year,
                                'semester'      => $semester,
                                'marks'         => $marks,
                                'grade'         => Result::calculateGrade($marks),
                            ]);
                            $created++;
                        }
                    }
                }

                // Pre-condition: the student has the expected number of results
                $beforeCount = Result::where('student_id', $studentId)->count();
                $this->assertSame(
                    $resultCount,
                    $beforeCount,
                    "Pre-condition failed: expected {$resultCount} result(s) for student [{$studentId}], "
                    . "found {$beforeCount}."
                );

                // --- Delete the student via the admin destroy endpoint ---
                $response = $this->actingAs($this->admin)
                    ->delete("/admin/students/{$studentId}");

                // The controller redirects to the index on success
                $response->assertRedirect();

                // --- Assert zero result rows remain for the deleted student's id ---
                $afterCount = Result::where('student_id', $studentId)->count();
                $this->assertSame(
                    0,
                    $afterCount,
                    "Cascade delete failed: {$afterCount} result row(s) remain for deleted student "
                    . "[id={$studentId}, admission_number={$admNumber}]."
                );

                // Also assert the student record itself was removed
                $this->assertNull(
                    Student::find($studentId),
                    "Student record [id={$studentId}] was not deleted."
                );
            });
    }
}
