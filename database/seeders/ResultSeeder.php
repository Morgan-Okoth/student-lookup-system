<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Result;
use App\Models\Student;
use Illuminate\Database\Seeder;

class ResultSeeder extends Seeder
{
    public function run(): void
    {
        $amelia  = Student::where('admission_number', 'IN16/00088/24')->first();
        $brian   = Student::where('admission_number', 'CS18/00145/24')->first();
        $cath    = Student::where('admission_number', 'ENG20/00312/24')->first();
        $david   = Student::where('admission_number', 'LAW19/00077/24')->first();
        $esther  = Student::where('admission_number', 'MED21/00201/24')->first();
        $felix   = Student::where('admission_number', 'IT17/00099/24')->first();

        $cs101  = Course::where('course_code', 'CS101')->first();
        $cs201  = Course::where('course_code', 'CS201')->first();
        $cs301  = Course::where('course_code', 'CS301')->first();
        $cs401  = Course::where('course_code', 'CS401')->first();
        $mat101 = Course::where('course_code', 'MAT101')->first();
        $mat201 = Course::where('course_code', 'MAT201')->first();
        $eng101 = Course::where('course_code', 'ENG101')->first();
        $phy101 = Course::where('course_code', 'PHY101')->first();
        $sta201 = Course::where('course_code', 'STA201')->first();
        $net301 = Course::where('course_code', 'NET301')->first();
        $os401  = Course::where('course_code', 'OS401')->first();
        $web201 = Course::where('course_code', 'WEB201')->first();

        $results = [
            // Amelia — Year 1, Semester 1
            [$amelia, $cs101,  'Year 1', 'Semester 1', 82],
            [$amelia, $mat101, 'Year 1', 'Semester 1', 75],
            [$amelia, $eng101, 'Year 1', 'Semester 1', 91],
            [$amelia, $phy101, 'Year 1', 'Semester 1', 38], // F

            // Amelia — Year 1, Semester 2
            [$amelia, $cs201,  'Year 1', 'Semester 2', 67], // B
            [$amelia, $mat201, 'Year 1', 'Semester 2', 54], // C
            [$amelia, $sta201, 'Year 1', 'Semester 2', 48], // D

            // Amelia — Year 2, Semester 1
            [$amelia, $cs301,  'Year 2', 'Semester 1', 78], // A
            [$amelia, $net301, 'Year 2', 'Semester 1', 62], // B
            [$amelia, $web201, 'Year 2', 'Semester 1', 55], // C

            // Brian — Year 1, Semester 1
            [$brian, $cs101,  'Year 1', 'Semester 1', 45], // D
            [$brian, $mat101, 'Year 1', 'Semester 1', 30], // F
            [$brian, $eng101, 'Year 1', 'Semester 1', 70], // A

            // Brian — Year 1, Semester 2
            [$brian, $cs201,  'Year 1', 'Semester 2', 60], // B
            [$brian, $sta201, 'Year 1', 'Semester 2', 53], // C

            // Catherine — Year 3, Semester 1
            [$cath, $cs401,  'Year 3', 'Semester 1', 88], // A
            [$cath, $net301, 'Year 3', 'Semester 1', 72], // A
            [$cath, $os401,  'Year 3', 'Semester 1', 65], // B
            [$cath, $web201, 'Year 3', 'Semester 1', 49], // D

            // Catherine — Year 3, Semester 2
            [$cath, $cs301,  'Year 3', 'Semester 2', 77], // A
            [$cath, $sta201, 'Year 3', 'Semester 2', 35], // F

            // David — Year 4, Semester 1
            [$david, $cs401,  'Year 4', 'Semester 1', 95], // A
            [$david, $os401,  'Year 4', 'Semester 1', 68], // B
            [$david, $eng101, 'Year 4', 'Semester 1', 51], // C

            // David — Year 4, Semester 2
            [$david, $net301, 'Year 4', 'Semester 2', 43], // D
            [$david, $web201, 'Year 4', 'Semester 2', 85], // A

            // Esther — Year 1, Semester 1
            [$esther, $cs101,  'Year 1', 'Semester 1', 61], // B
            [$esther, $mat101, 'Year 1', 'Semester 1', 74], // A
            [$esther, $phy101, 'Year 1', 'Semester 1', 29], // F

            // Felix — Year 3, Semester 1
            [$felix, $cs301,  'Year 3', 'Semester 1', 80], // A
            [$felix, $net301, 'Year 3', 'Semester 1', 66], // B
            [$felix, $os401,  'Year 3', 'Semester 1', 58], // C
            [$felix, $web201, 'Year 3', 'Semester 1', 41], // D

            // Felix — Year 3, Semester 2
            [$felix, $cs401,  'Year 3', 'Semester 2', 73], // A
            [$felix, $sta201, 'Year 3', 'Semester 2', 37], // F
        ];

        foreach ($results as [$student, $course, $year, $semester, $marks]) {
            if (!$student || !$course) {
                continue;
            }

            Result::firstOrCreate(
                [
                    'student_id'    => $student->id,
                    'course_id'     => $course->id,
                    'academic_year' => $year,
                    'semester'      => $semester,
                ],
                [
                    'marks' => $marks,
                    'grade' => Result::calculateGrade($marks),
                ]
            );
        }
    }
}
