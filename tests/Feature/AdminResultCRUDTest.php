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
 * Tests admin CRUD for result management, including grade auto-calculation.
 * Requirements: 8.1, 8.2, 8.8
 */
class AdminResultCRUDTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Student $student;
    private Course $course;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name'     => 'Admin User',
            'email'    => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->student = Student::create([
            'admission_number' => 'CS20/00001/22',
            'full_name'        => 'Test Student',
            'programme'        => 'Computer Science',
            'year_of_study'    => 1,
        ]);

        $this->course = Course::create([
            'course_code'  => 'CS101',
            'course_name'  => 'Introduction to Computing',
            'credit_hours' => 3,
        ]);
    }

    public function test_creating_result_stores_correct_grade_from_marks(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/results', [
            'student_id'    => $this->student->id,
            'course_id'     => $this->course->id,
            'academic_year' => 'Year 1',
            'semester'      => 'Semester 1',
            'marks'         => 75, // should produce grade A
        ]);

        $response->assertRedirect('/admin/results');
        $response->assertSessionHas('success');

        $result = Result::where('student_id', $this->student->id)
            ->where('course_id', $this->course->id)
            ->first();

        $this->assertNotNull($result);
        $this->assertEquals(75, $result->marks);
        $this->assertEquals('A', $result->grade);
    }

    public function test_updating_result_recalculates_grade(): void
    {
        $result = Result::create([
            'student_id'    => $this->student->id,
            'course_id'     => $this->course->id,
            'academic_year' => 'Year 1',
            'semester'      => 'Semester 1',
            'marks'         => 75,
            'grade'         => 'A',
        ]);

        // Update to 55 marks which should produce grade C
        $response = $this->actingAs($this->admin)->put("/admin/results/{$result->id}", [
            'marks' => 55,
        ]);

        $response->assertRedirect('/admin/results');
        $response->assertSessionHas('success');

        $result->refresh();
        $this->assertEquals(55, $result->marks);
        $this->assertEquals('C', $result->grade);
    }

    public function test_deleting_result_removes_it(): void
    {
        $result = Result::create([
            'student_id'    => $this->student->id,
            'course_id'     => $this->course->id,
            'academic_year' => 'Year 1',
            'semester'      => 'Semester 1',
            'marks'         => 65,
            'grade'         => 'B',
        ]);

        $response = $this->actingAs($this->admin)->delete("/admin/results/{$result->id}");
        $response->assertRedirect('/admin/results');
        $this->assertDatabaseMissing('results', ['id' => $result->id]);
    }
}
