<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $courses = [
            ['course_code' => 'CS101',  'course_name' => 'Introduction to Programming',          'credit_hours' => 3],
            ['course_code' => 'CS201',  'course_name' => 'Data Structures and Algorithms',        'credit_hours' => 4],
            ['course_code' => 'CS301',  'course_name' => 'Database Systems',                      'credit_hours' => 3],
            ['course_code' => 'CS401',  'course_name' => 'Software Engineering',                  'credit_hours' => 3],
            ['course_code' => 'MAT101', 'course_name' => 'Calculus I',                            'credit_hours' => 4],
            ['course_code' => 'MAT201', 'course_name' => 'Linear Algebra',                        'credit_hours' => 3],
            ['course_code' => 'ENG101', 'course_name' => 'Communication Skills',                  'credit_hours' => 2],
            ['course_code' => 'PHY101', 'course_name' => 'Physics for Engineers',                 'credit_hours' => 4],
            ['course_code' => 'STA201', 'course_name' => 'Probability and Statistics',            'credit_hours' => 3],
            ['course_code' => 'NET301', 'course_name' => 'Computer Networks',                     'credit_hours' => 3],
            ['course_code' => 'OS401',  'course_name' => 'Operating Systems',                     'credit_hours' => 3],
            ['course_code' => 'WEB201', 'course_name' => 'Web Application Development',           'credit_hours' => 3],
        ];

        foreach ($courses as $course) {
            Course::firstOrCreate(
                ['course_code' => $course['course_code']],
                $course
            );
        }
    }
}
