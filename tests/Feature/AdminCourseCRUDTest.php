<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature: student-results-lookup
 * Tests admin CRUD operations for course management.
 * Requirements: 7.1, 7.2, 7.3
 */
class AdminCourseCRUDTest extends TestCase
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

    public function test_course_list_returns_200(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/courses');
        $response->assertStatus(200);
    }

    public function test_course_list_displays_course_data(): void
    {
        Course::create([
            'course_code'  => 'CS101',
            'course_name'  => 'Introduction to Computing',
            'credit_hours' => 3,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/courses');
        $response->assertSee('CS101');
        $response->assertSee('Introduction to Computing');
    }

    public function test_creating_course_with_valid_data_succeeds(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/courses', [
            'course_code'  => 'MAT201',
            'course_name'  => 'Calculus I',
            'credit_hours' => 4,
        ]);

        $response->assertRedirect('/admin/courses');
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('courses', ['course_code' => 'MAT201']);
    }

    public function test_duplicate_course_code_returns_validation_error(): void
    {
        Course::create([
            'course_code'  => 'MAT201',
            'course_name'  => 'Calculus I',
            'credit_hours' => 4,
        ]);

        $response = $this->actingAs($this->admin)->post('/admin/courses', [
            'course_code'  => 'MAT201',
            'course_name'  => 'Duplicate Course',
            'credit_hours' => 3,
        ]);

        $response->assertSessionHasErrors('course_code');
    }
}
