<?php

namespace Tests\Feature\Properties;

// Feature: student-results-lookup, Property 10: Unique constraint rejection

use App\Models\Course;
use App\Models\Result;
use App\Models\Student;
use App\Models\User;
use Eris\Generator;

/**
 * Property 10: Unique constraint rejection
 *
 * For any record already persisted in `students` with a given admission_number
 * (or in `courses` with a given course_code, or in `results` with a given
 * (student_id, course_id, academic_year, semester) combination), attempting to
 * create a second record with the same unique key SHALL be rejected with a
 * validation error before any database write occurs.
 *
 * Validates: Requirements 6.3, 7.3, 8.9
 * Minimum iterations: 100
 */
class UniqueConstraintPropertyTest extends PropertyTestCase
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
    // Helpers — admission number
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
     * Generate a course code guaranteed to be unique in the current DB.
     * Format: e.g. "UCT001"
     */
    private function uniqueCourseCode(int $seed): string
    {
        $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $l1      = $letters[$seed % 26];
        $l2      = $letters[($seed + 7) % 26];
        $l3      = $letters[($seed + 13) % 26];
        $num     = $seed % 1000;

        $code    = sprintf('%s%s%s%03d', $l1, $l2, $l3, $num);
        $attempt = 0;

        while (Course::where('course_code', $code)->exists()) {
            $attempt++;
            $code = sprintf('%s%s%s%03d', $l1, $l2, $l3, ($num + $attempt * 7) % 1000);
        }

        return $code;
    }

    // -----------------------------------------------------------------------
    // Property 10a: Duplicate admission number is rejected
    // -----------------------------------------------------------------------

    /**
     * For any admission number that is already persisted in the `students`
     * table, submitting the admin store-student endpoint with the same
     * admission number SHALL:
     *   1. Return a validation error (session errors bag contains 'admission_number').
     *   2. NOT write a second student row (count stays at 1).
     *
     * 100 iterations.
     *
     * Validates: Requirement 6.3
     */
    public function test_duplicate_admission_number_is_rejected(): void
    {
        $this->limitTo(100)
            ->forAll(
                Generator\choose(0, 9999),   // seed for letter prefix
                Generator\choose(0, 99999),  // serial (5-digit)
                Generator\choose(0, 99)      // check (2-digit)
            )
            ->then(function (int $seed, int $serial, int $check): void {
                $admNumber = $this->uniqueAdmissionNumber($seed, $serial, $check);

                // --- Persist the first (valid) student ---
                Student::create([
                    'admission_number' => $admNumber,
                    'full_name'        => 'Original Student',
                    'programme'        => 'Bachelor of Science',
                    'year_of_study'    => ($seed % 4) + 1,
                ]);

                $countBefore = Student::where('admission_number', $admNumber)->count();
                $this->assertSame(1, $countBefore, "Pre-condition: exactly 1 student with this admission number.");

                // --- Attempt to store a duplicate via the admin endpoint ---
                $response = $this->actingAs($this->admin)->post('/admin/students', [
                    'admission_number' => $admNumber,   // ← duplicate
                    'full_name'        => 'Duplicate Student',
                    'programme'        => 'Information Technology',
                    'year_of_study'    => (($seed + 1) % 4) + 1,
                ]);

                // Validation should fail — session must have an error for 'admission_number'
                $response->assertSessionHasErrors('admission_number');

                // No duplicate row must have been written
                $countAfter = Student::where('admission_number', $admNumber)->count();
                $this->assertSame(
                    1,
                    $countAfter,
                    "Duplicate student row written for admission_number [{$admNumber}] — "
                    . "unique constraint not enforced before DB write."
                );
            });
    }

    // -----------------------------------------------------------------------
    // Property 10b: Duplicate course code is rejected
    // -----------------------------------------------------------------------

    /**
     * For any course code that is already persisted in the `courses` table,
     * submitting the admin store-course endpoint with the same course code
     * SHALL:
     *   1. Return a validation error (session errors bag contains 'course_code').
     *   2. NOT write a second course row (count stays at 1).
     *
     * 100 iterations.
     *
     * Validates: Requirement 7.3
     */
    public function test_duplicate_course_code_is_rejected(): void
    {
        $this->limitTo(100)
            ->forAll(
                Generator\choose(0, 9999)   // seed for code generation
            )
            ->then(function (int $seed): void {
                $courseCode = $this->uniqueCourseCode($seed);

                // --- Persist the first (valid) course ---
                Course::create([
                    'course_code'  => $courseCode,
                    'course_name'  => 'Original Course',
                    'credit_hours' => ($seed % 4) + 1,
                ]);

                $countBefore = Course::where('course_code', $courseCode)->count();
                $this->assertSame(1, $countBefore, "Pre-condition: exactly 1 course with this code.");

                // --- Attempt to store a duplicate via the admin endpoint ---
                $response = $this->actingAs($this->admin)->post('/admin/courses', [
                    'course_code'  => $courseCode,   // ← duplicate
                    'course_name'  => 'Duplicate Course',
                    'credit_hours' => (($seed + 1) % 4) + 1,
                ]);

                // Validation should fail — session must have an error for 'course_code'
                $response->assertSessionHasErrors('course_code');

                // No duplicate row must have been written
                $countAfter = Course::where('course_code', $courseCode)->count();
                $this->assertSame(
                    1,
                    $countAfter,
                    "Duplicate course row written for course_code [{$courseCode}] — "
                    . "unique constraint not enforced before DB write."
                );
            });
    }

    // -----------------------------------------------------------------------
    // Property 10c: Duplicate result (student+course+year+semester) is rejected
    // -----------------------------------------------------------------------

    /**
     * For any (student_id, course_id, academic_year, semester) combination
     * already persisted in `results`, attempting to store a second result for
     * the exact same combination via the admin results endpoint SHALL:
     *   1. Return a validation error (session errors bag contains 'course_id',
     *      which carries the composite-unique rule).
     *   2. NOT write a duplicate row (count for that combination stays at 1).
     *
     * 100 iterations.
     *
     * Validates: Requirement 8.9
     */
    public function test_duplicate_result_combination_is_rejected(): void
    {
        $this->limitTo(100)
            ->forAll(
                Generator\choose(0, 9999),  // seed for admission number
                Generator\choose(0, 99999), // serial
                Generator\choose(0, 99),    // check digits
                Generator\choose(0, 3),     // academic year index
                Generator\choose(0, 1),     // semester index
                Generator\choose(0, 100)    // marks for the first (valid) result
            )
            ->then(function (
                int $seed,
                int $serial,
                int $check,
                int $yearIdx,
                int $semIdx,
                int $marks
            ): void {
                $academicYear = $this->academicYears[$yearIdx];
                $semester     = $this->semesters[$semIdx];

                // --- Create a student ---
                $admNumber = $this->uniqueAdmissionNumber($seed, $serial, $check);
                $student = Student::create([
                    'admission_number' => $admNumber,
                    'full_name'        => 'Unique Student ' . $seed,
                    'programme'        => 'Bachelor of Testing',
                    'year_of_study'    => ($yearIdx % 4) + 1,
                ]);

                // --- Create a course ---
                $courseCode = $this->uniqueCourseCode($seed + $serial % 10000);
                $course = Course::create([
                    'course_code'  => $courseCode,
                    'course_name'  => 'Test Course ' . $courseCode,
                    'credit_hours' => ($seed % 4) + 1,
                ]);

                // --- Persist the first (valid) result directly ---
                Result::create([
                    'student_id'    => $student->id,
                    'course_id'     => $course->id,
                    'academic_year' => $academicYear,
                    'semester'      => $semester,
                    'marks'         => $marks,
                    'grade'         => Result::calculateGrade($marks),
                ]);

                $countBefore = Result::where([
                    'student_id'    => $student->id,
                    'course_id'     => $course->id,
                    'academic_year' => $academicYear,
                    'semester'      => $semester,
                ])->count();

                $this->assertSame(1, $countBefore, "Pre-condition: exactly 1 result for this combination.");

                // --- Attempt to store a duplicate via the admin results endpoint ---
                $duplicateMarks = ($marks + 10) % 101; // different marks, same combination

                $response = $this->actingAs($this->admin)->post('/admin/results', [
                    'student_id'    => $student->id,
                    'course_id'     => $course->id,   // ← same combination
                    'academic_year' => $academicYear,
                    'semester'      => $semester,
                    'marks'         => $duplicateMarks,
                ]);

                // Validation should fail — the composite-unique rule is on 'course_id'
                $response->assertSessionHasErrors('course_id');

                // No duplicate row must have been written
                $countAfter = Result::where([
                    'student_id'    => $student->id,
                    'course_id'     => $course->id,
                    'academic_year' => $academicYear,
                    'semester'      => $semester,
                ])->count();

                $this->assertSame(
                    1,
                    $countAfter,
                    "Duplicate result row written for combination "
                    . "[student={$student->id}, course={$course->id}, "
                    . "year={$academicYear}, sem={$semester}] — "
                    . "unique constraint not enforced before DB write."
                );
            });
    }
}
