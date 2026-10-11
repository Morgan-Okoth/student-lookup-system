<?php

namespace Tests\Feature\Properties;

use App\Models\Course;
use App\Models\Result;
use App\Models\Student;
use Eris\Generator;
use Illuminate\Routing\Middleware\ThrottleRequests;

/**
 * Feature: student-results-lookup, Property 6: Results page displays all required data
 *
 * For any student and any non-empty set of their results for a given academic
 * year and semester, the rendered results page SHALL contain: the student's
 * full name, admission number, programme, academic year, and semester; and a
 * table with the column headers Course Code, Course Name, Credit Hours, Marks,
 * and Grade.
 *
 * Validates: Requirements 4.1, 4.2
 * Minimum iterations: 100
 */
class ResultsPageCompletenessPropertyTest extends PropertyTestCase
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
        $l2      = $letters[($seed + 9) % 26];
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
                $code = sprintf('CMP%03d', $attempt++);
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
     * Create a student with at least $resultCount results in the given
     * academic year and semester, using courses from the provided pool.
     */
    private function createStudentWithResults(
        string $admissionNumber,
        string $fullName,
        string $programme,
        int    $yearOfStudy,
        string $academicYear,
        string $semester,
        int    $resultCount,
        array  $courses
    ): Student {
        $student = Student::create([
            'admission_number' => $admissionNumber,
            'full_name'        => $fullName,
            'programme'        => $programme,
            'year_of_study'    => $yearOfStudy,
        ]);

        $used = [];

        for ($r = 0; $r < $resultCount; $r++) {
            foreach ($courses as $course) {
                $key = $academicYear . '|' . $semester . '|' . $course->id;
                if (!isset($used[$key])) {
                    $used[$key] = true;
                    $marks      = (($r * 13 + 45) % 101);
                    Result::create([
                        'student_id'    => $student->id,
                        'course_id'     => $course->id,
                        'academic_year' => $academicYear,
                        'semester'      => $semester,
                        'marks'         => $marks,
                        'grade'         => Result::calculateGrade($marks),
                    ]);
                    break;
                }
            }
        }

        return $student;
    }

    // -----------------------------------------------------------------------
    // Property 6: Results page displays all required data
    // -----------------------------------------------------------------------

    /**
     * For any student with at least one result in a given academic year and
     * semester, the rendered results page SHALL display:
     *   - student full name
     *   - admission number
     *   - programme
     *   - academic year
     *   - semester
     *   - table column headers: "Course Code", "Course Name", "Credit Hours",
     *     "Marks", "Grade"
     *
     * 100 iterations.
     */
    public function test_results_page_displays_all_required_data(): void
    {
        // Sample programme names to cycle through
        $programmes = [
            'Bachelor of Science in Computer Science',
            'Bachelor of Engineering',
            'Bachelor of Business Administration',
            'Bachelor of Arts',
        ];

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
                // Number of results to create for this student (1–5)
                Generator\choose(1, 5),
                // Programme index (0–3)
                Generator\choose(0, 3)
            )
            ->then(function (
                int $seed,
                int $serial,
                int $check,
                int $yearIdx,
                int $semIdx,
                int $resultCount,
                int $progIdx
            ) use ($programmes): void {
                $courses      = $this->ensureCourses(5);
                $academicYear = $this->academicYears[$yearIdx];
                $semester     = $this->semesters[$semIdx];
                $programme    = $programmes[$progIdx];

                // Derive a unique full name from the seed so collisions are unlikely
                $fullName = 'Student ' . strtoupper(substr(md5((string) $seed), 0, 6));

                // Build the admission number
                $admNumber = $this->makeAdmissionNumber($seed, $serial, $check);

                // Resolve admission-number uniqueness collisions
                $attempt = 0;
                while (Student::where('admission_number', $admNumber)->exists()) {
                    $attempt++;
                    $admNumber = $this->makeAdmissionNumber(
                        $seed + $attempt * 61,
                        $serial + $attempt,
                        $check + $attempt
                    );
                }

                // Create the student and at least one result in the target year/semester
                $student = $this->createStudentWithResults(
                    $admNumber,
                    $fullName,
                    $programme,
                    ($yearIdx % 4) + 1,
                    $academicYear,
                    $semester,
                    $resultCount,
                    $courses
                );

                // Perform the public lookup
                $response = $this->get(
                    '/results'
                    . '?admission_number=' . urlencode($student->admission_number)
                    . '&academic_year=' . urlencode($academicYear)
                    . '&semester=' . urlencode($semester)
                );

                $response->assertStatus(200);

                $html = $response->getContent();

                // --- Requirement 4.1: student header fields ---
                $this->assertStringContainsString(
                    $student->full_name,
                    $html,
                    "Results page must contain the student's full name [{$student->full_name}]."
                );

                $this->assertStringContainsString(
                    $student->admission_number,
                    $html,
                    "Results page must contain the student's admission number [{$student->admission_number}]."
                );

                $this->assertStringContainsString(
                    $student->programme,
                    $html,
                    "Results page must contain the student's programme [{$student->programme}]."
                );

                $this->assertStringContainsString(
                    $academicYear,
                    $html,
                    "Results page must contain the academic year [$academicYear]."
                );

                $this->assertStringContainsString(
                    $semester,
                    $html,
                    "Results page must contain the semester [$semester]."
                );

                // --- Requirement 4.2: table column headers ---
                $this->assertStringContainsString(
                    'Course Code',
                    $html,
                    "Results page must contain the 'Course Code' column header."
                );

                $this->assertStringContainsString(
                    'Course Name',
                    $html,
                    "Results page must contain the 'Course Name' column header."
                );

                $this->assertStringContainsString(
                    'Credit Hours',
                    $html,
                    "Results page must contain the 'Credit Hours' column header."
                );

                $this->assertStringContainsString(
                    'Marks',
                    $html,
                    "Results page must contain the 'Marks' column header."
                );

                $this->assertStringContainsString(
                    'Grade',
                    $html,
                    "Results page must contain the 'Grade' column header."
                );
            });
    }
}
