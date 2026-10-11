<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Result;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature: student-results-lookup
 * Tests that all admin routes redirect unauthenticated requests to /login.
 * Requirements: 5.5, 6.5
 */
class AdminAuthGuardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * All admin GET routes that should require auth.
     */
    private function adminGetRoutes(): array
    {
        return [
            '/admin/dashboard',
            '/admin/students',
            '/admin/students/create',
            '/admin/courses',
            '/admin/courses/create',
            '/admin/results',
            '/admin/results/create',
        ];
    }

    public function test_unauthenticated_get_requests_redirect_to_login(): void
    {
        foreach ($this->adminGetRoutes() as $route) {
            $response = $this->get($route);
            $response->assertStatus(302);
            $response->assertRedirect('/login');
        }
    }

    public function test_unauthenticated_post_to_students_store_redirects_to_login(): void
    {
        $response = $this->post('/admin/students', [
            'admission_number' => 'IN16/00001/24',
            'full_name'        => 'Test',
            'programme'        => 'IT',
            'year_of_study'    => 1,
        ]);
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_unauthenticated_post_to_courses_store_redirects_to_login(): void
    {
        $response = $this->post('/admin/courses', [
            'course_code'  => 'CS101',
            'course_name'  => 'Computing',
            'credit_hours' => 3,
        ]);
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_unauthenticated_post_to_results_store_redirects_to_login(): void
    {
        $response = $this->post('/admin/results', [
            'student_id'    => 1,
            'course_id'     => 1,
            'academic_year' => 'Year 1',
            'semester'      => 'Semester 1',
            'marks'         => 70,
        ]);
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_unauthenticated_put_to_results_update_redirects_to_login(): void
    {
        $response = $this->put('/admin/results/1', ['marks' => 80]);
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_unauthenticated_delete_to_student_destroy_redirects_to_login(): void
    {
        $response = $this->delete('/admin/students/1');
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_unauthenticated_delete_to_results_destroy_redirects_to_login(): void
    {
        $response = $this->delete('/admin/results/1');
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_unauthenticated_get_to_result_edit_redirects_to_login(): void
    {
        $response = $this->get('/admin/results/1/edit');
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }
}
