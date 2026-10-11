<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Feature: student-results-lookup
 * Tests the rate limiting on the /results endpoint: 10 allowed, 11th is rejected.
 * Requirements: 5.1, 5.2
 */
class RateLimitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Clear rate limiter before each test so counts start fresh
        RateLimiter::clear('throttle:10|1:' . request()->ip());
    }

    public function test_first_ten_requests_are_not_rate_limited(): void
    {
        // Clear all rate limiter buckets
        RateLimiter::resetAttempts('');

        for ($i = 1; $i <= 10; $i++) {
            $response = $this->get('/results');
            $this->assertNotEquals(429, $response->getStatusCode(), "Request $i should not be rate-limited");
        }
    }

    public function test_eleventh_request_returns_429(): void
    {
        // Send 10 requests to exhaust the limit
        for ($i = 0; $i < 10; $i++) {
            $this->get('/results');
        }

        // 11th request should be rate-limited
        $response = $this->get('/results');
        $response->assertStatus(429);
    }
}
