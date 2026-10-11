<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $students = [
            [
                'admission_number' => 'IN16/00088/24',
                'full_name'        => 'Amelia Wanjiku Fictitious',
                'programme'        => 'Bachelor of Science in Computer Science',
                'year_of_study'    => 2,
            ],
            [
                'admission_number' => 'CS18/00145/24',
                'full_name'        => 'Brian Otieno Testname',
                'programme'        => 'Bachelor of Commerce',
                'year_of_study'    => 1,
            ],
            [
                'admission_number' => 'ENG20/00312/24',
                'full_name'        => 'Catherine Mwangi Sampledata',
                'programme'        => 'Bachelor of Engineering (Civil)',
                'year_of_study'    => 3,
            ],
            [
                'admission_number' => 'LAW19/00077/24',
                'full_name'        => 'David Kipchoge Placeholder',
                'programme'        => 'Bachelor of Laws',
                'year_of_study'    => 4,
            ],
            [
                'admission_number' => 'MED21/00201/24',
                'full_name'        => 'Esther Achieng Demouser',
                'programme'        => 'Bachelor of Medicine and Surgery',
                'year_of_study'    => 1,
            ],
            [
                'admission_number' => 'IT17/00099/24',
                'full_name'        => 'Felix Kamau Fictional',
                'programme'        => 'Bachelor of Science in Information Technology',
                'year_of_study'    => 3,
            ],
        ];

        foreach ($students as $student) {
            Student::firstOrCreate(
                ['admission_number' => $student['admission_number']],
                $student
            );
        }
    }
}
