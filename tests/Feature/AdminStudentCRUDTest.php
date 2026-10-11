<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Result;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature: student-results-lookup
 * Tests admin CRUD operations for student management.
 * Requirements: 6.1, 6.2, 6.3, 6.4
 */
class AdminStudentCRUDTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::create([
            'name'     => 'Admin User',
            'email'    => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    public function test_student_list_returns_200_when_authenticated(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/students');
        $response->assertStatus(200);
    }

    public function test_student_list_displays_student_data(): void
    {
        Student::create([
            'admission_number' => 'IN16/00001/24',
            'full_name'        => 'Alice Mwangi',
            'programme'        => 'Information Technology',
            'year_of_study'    => 2,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/students');
        $response->assertSee('Alice Mwangi');
        $response->assertSee('IN16/00001/24');
    }

    public function test_creating_student_with_valid_data_redirects_with_success(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/students', [
            'admission_number' => 'IN16/00002/24',
            'full_name'        => 'Bob Otieno',
            'programme'        => 'Business Administration',
            'year_of_study'    => 1,
        ]);

        $response->assertRedirect('/admin/students');
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('students', ['admission_number' => 'IN16/00002/24']);
    }

    public function test_duplicate_admission_number_returns_validation_error(): void
    {
        Student::create([
            'admission_number' => 'IN16/00002/24',
            'full_name'        => 'Bob Otieno',
            'programme'        => 'Business Administration',
            'year_of_study'    => 1,
        ]);

        $response = $this->actingAs($this->admin)->post('/admin/students', [
            'admission_number' => 'IN16/00002/24',
            'full_name'        => 'Duplicate Student',
            'programme'        => 'Engineering',
            'year_of_study'    => 2,
        ]);

        $response->assertSessionHasErrors('admission_number');
    }

    public function test_deleting_student_removes_record(): void
    {
        $student = Student::create([
            'admission_number' => 'IN16/00003/24',
            'full_name'        => 'Carol Njeri',
            'programme'        => 'Nursing',
            'year_of_study'    => 3,
        ]);

        $response = $this->actingAs($this->admin)->delete("/admin/students/{$student->id}");
        $response->assertRedirect('/admin/students');
        $this->assertDatabaseMissing('students', ['id' => $student->id]);
    }

    public function test_deleting_student_cascades_to_results(): void
    {
        $student = Student::create([
            'admission_number' => 'IN16/00003/24',
            'full_name'        => 'Carol Njeri',
            'programme'        => 'Nursing',
            'year_of_study'    => 3,
        ]);

        $course = Course::create([
            'course_code'  => 'NUR101',
            'course_name'  => 'Nursing Fundamentals',
            'credit_hours' => 3,
        ]);

        Result::create([
            'student_id'    => $student->id,
            'course_id'     => $course->id,
            'academic_year' => 'Year 1',
            'semester'      => 'Semester 1',
            'marks'         => 70,
            'grade'         => 'A',
        ]);

        $this->actingAs($this->admin)->delete("/admin/students/{$student->id}");

        $this->assertDatabaseMissing('results', ['student_id' => $student->id]);
    }
}
