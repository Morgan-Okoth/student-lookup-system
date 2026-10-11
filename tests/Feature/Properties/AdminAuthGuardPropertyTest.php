<?php

namespace Tests\Feature\Properties;

// Feature: student-results-lookup, Property 9: Admin routes require authentication

use App\Models\Course;
use App\Models\Result;
use App\Models\Student;
use Eris\Generator;

/**
 * Property 9: Admin routes require authentication
 *
 * For any admin route registered under the /admin prefix, an unauthenticated
 * GET or POST request SHALL receive a redirect response (HTTP 302) pointing
 * to the /login URL. No admin data SHALL be returned to an unauthenticated
 * caller.
 *
 * Validates: Requirements 5.5, 6.5
 * Minimum iterations: 100
 */
class AdminAuthGuardPropertyTest extends PropertyTestCase
{
    // -----------------------------------------------------------------------
    // Admin route definitions
    // -----------------------------------------------------------------------

    /**
     * All admin routes as [method, uri] pairs.
     *
     * Routes that require a model binding ({student}, {result}) are given a
     * placeholder ID of 1 — the auth middleware fires before any model
     * resolution, so the actual record existence does not matter.
     */
    private function adminRoutes(): array
    {
        return [
            // Dashboard
            ['GET',    '/admin/dashboard'],

            // Students
            ['GET',    '/admin/students'],
            ['GET',    '/admin/students/create'],
            ['POST',   '/admin/students'],
            ['DELETE', '/admin/students/1'],

            // Courses
            ['GET',    '/admin/courses'],
            ['GET',    '/admin/courses/create'],
            ['POST',   '/admin/courses'],

            // Results
            ['GET',    '/admin/results'],
            ['GET',    '/admin/results/create'],
            ['POST',   '/admin/results'],
            ['GET',    '/admin/results/1/edit'],
            ['PUT',    '/admin/results/1'],
            ['DELETE', '/admin/results/1'],
        ];
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    /**
     * Build a plausible (but arbitrary) request body for write methods so
     * that the request reaches the middleware layer rather than failing early
     * on missing Content-Type or empty body issues.
     *
     * The auth middleware fires before validation, so the body content itself
     * does not affect the expected redirect outcome.
     */
    private function sampleBody(string $method, string $uri): array
    {
        if ($method === 'GET' || $method === 'DELETE') {
            return [];
        }

        if (str_contains($uri, '/students')) {
            return [
                'admission_number' => 'IN16/00001/24',
                'full_name'        => 'Sample Student',
                'programme'        => 'Information Technology',
                'year_of_study'    => 1,
            ];
        }

        if (str_contains($uri, '/courses')) {
            return [
                'course_code'  => 'CS101',
                'course_name'  => 'Introduction to Computing',
                'credit_hours' => 3,
            ];
        }

        if (str_contains($uri, '/results')) {
            return [
                'student_id'    => 1,
                'course_id'     => 1,
                'academic_year' => 'Year 1',
                'semester'      => 'Semester 1',
                'marks'         => 75,
            ];
        }

        return [];
    }

    /**
     * Dispatch a request using the appropriate TestCase HTTP helper.
     */
    private function dispatchRequest(string $method, string $uri): \Illuminate\Testing\TestResponse
    {
        $body = $this->sampleBody($method, $uri);

        return match ($method) {
            'GET'    => $this->get($uri),
            'POST'   => $this->post($uri, $body),
            'PUT'    => $this->put($uri, $body),
            'DELETE' => $this->delete($uri),
            default  => $this->get($uri),
        };
    }

    // -----------------------------------------------------------------------
    // Property-based test — random selection from the admin route matrix
    // -----------------------------------------------------------------------

    /**
     * For any admin route selected at random from the full set of registered
     * admin endpoints, an unauthenticated request SHALL:
     *   1. Return HTTP status 302.
     *   2. Redirect to /login.
     *   3. Not include any admin page content in the response body.
     *
     * 100 iterations — each iteration randomly picks a route and asserts
     * the auth guard fires correctly.
     *
     * Validates: Requirements 5.5, 6.5
     */
    public function test_unauthenticated_request_to_any_admin_route_redirects_to_login(): void
    {
        $routes = $this->adminRoutes();
        $routeCount = count($routes);

        $this->limitTo(100)
            ->forAll(
                // Pick a random index into the admin routes array
                Generator\choose(0, $routeCount - 1)
            )
            ->then(function (int $routeIndex) use ($routes): void {
                [$method, $uri] = $routes[$routeIndex];

                $response = $this->dispatchRequest($method, $uri);

                // Assert HTTP 302 redirect
                $response->assertStatus(302);

                // Assert the redirect target is /login
                $response->assertRedirect('/login');

                // Assert no admin page content is in the (empty) redirect body
                $body = $response->getContent();
                $this->assertStringNotContainsString(
                    'admin',
                    strtolower($body),
                    "Admin content leaked to unauthenticated response for $method $uri."
                );
            });
    }

    // -----------------------------------------------------------------------
    // Exhaustive test — every admin route must redirect without exception
    // -----------------------------------------------------------------------

    /**
     * Exhaustively verify every admin route — no route in the admin group
     * should ever serve content to an unauthenticated caller.
     *
     * This deterministic loop complements the random property test above,
     * guaranteeing full coverage regardless of Eris's random sampling.
     *
     * Validates: Requirements 5.5, 6.5
     */
    public function test_every_admin_route_redirects_unauthenticated_requests(): void
    {
        foreach ($this->adminRoutes() as [$method, $uri]) {
            $response = $this->dispatchRequest($method, $uri);

            $response->assertStatus(
                302,
                "Expected HTTP 302 for unauthenticated $method $uri, got {$response->getStatusCode()}."
            );

            $response->assertRedirect(
                '/login',
                "Expected redirect to /login for unauthenticated $method $uri."
            );
        }
    }

    // -----------------------------------------------------------------------
    // Combined method × route property test
    // -----------------------------------------------------------------------

    /**
     * Generate random (HTTP method, admin route path) combinations from the
     * full admin route matrix and assert each unauthenticated request returns
     * 302 → /login.
     *
     * Builds a flattened index over all [method, uri] pairs so Eris can
     * uniformly sample the full admin route surface.
     *
     * 100 iterations.
     *
     * Validates: Requirements 5.5, 6.5
     */
    public function test_random_method_and_route_combinations_require_auth(): void
    {
        $routes = $this->adminRoutes();
        $count  = count($routes);

        $this->limitTo(100)
            ->forAll(
                Generator\choose(0, $count - 1),
                // Second generator introduces variation — allows Eris shrinking
                // to find the minimal failing route/method combination.
                Generator\choose(0, $count - 1)
            )
            ->then(function (int $idx1, int $idx2) use ($routes, $count): void {
                // Use idx1 as the primary route selector; idx2 varies the
                // selection slightly to improve sampling diversity.
                $primaryRoute  = $routes[$idx1 % $count];
                $secondaryRoute = $routes[$idx2 % $count];

                foreach ([$primaryRoute, $secondaryRoute] as [$method, $uri]) {
                    $response = $this->dispatchRequest($method, $uri);

                    $response->assertStatus(302);
                    $response->assertRedirect('/login');
                }
            });
    }
}
