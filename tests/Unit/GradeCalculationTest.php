<?php

namespace Tests\Unit;

use App\Models\Result;
use PHPUnit\Framework\TestCase;

/**
 * Feature: student-results-lookup
 * Tests: Result::calculateGrade() for all five grade bands and all boundary values.
 * Requirements: 8.3, 8.4, 8.5, 8.6, 8.7
 */
class GradeCalculationTest extends TestCase
{
    // -----------------------------------------------------------------------
    // Representative values — one per band
    // -----------------------------------------------------------------------

    public function test_grade_a_representative(): void
    {
        $this->assertSame('A', Result::calculateGrade(75));
    }

    public function test_grade_b_representative(): void
    {
        $this->assertSame('B', Result::calculateGrade(65));
    }

    public function test_grade_c_representative(): void
    {
        $this->assertSame('C', Result::calculateGrade(55));
    }

    public function test_grade_d_representative(): void
    {
        $this->assertSame('D', Result::calculateGrade(45));
    }

    public function test_grade_f_representative(): void
    {
        $this->assertSame('F', Result::calculateGrade(20));
    }

    // -----------------------------------------------------------------------
    // Boundary values — every band transition (39/40, 49/50, 59/60, 69/70)
    // plus the absolute extremes (0 and 100)
    // -----------------------------------------------------------------------

    /** Marks 39 — still in F band */
    public function test_boundary_39_is_grade_f(): void
    {
        $this->assertSame('F', Result::calculateGrade(39));
    }

    /** Marks 40 — first value of D band */
    public function test_boundary_40_is_grade_d(): void
    {
        $this->assertSame('D', Result::calculateGrade(40));
    }

    /** Marks 49 — last value of D band */
    public function test_boundary_49_is_grade_d(): void
    {
        $this->assertSame('D', Result::calculateGrade(49));
    }

    /** Marks 50 — first value of C band */
    public function test_boundary_50_is_grade_c(): void
    {
        $this->assertSame('C', Result::calculateGrade(50));
    }

    /** Marks 59 — last value of C band */
    public function test_boundary_59_is_grade_c(): void
    {
        $this->assertSame('C', Result::calculateGrade(59));
    }

    /** Marks 60 — first value of B band */
    public function test_boundary_60_is_grade_b(): void
    {
        $this->assertSame('B', Result::calculateGrade(60));
    }

    /** Marks 69 — last value of B band */
    public function test_boundary_69_is_grade_b(): void
    {
        $this->assertSame('B', Result::calculateGrade(69));
    }

    /** Marks 70 — first value of A band */
    public function test_boundary_70_is_grade_a(): void
    {
        $this->assertSame('A', Result::calculateGrade(70));
    }

    /** Marks 100 — maximum valid score, A band */
    public function test_boundary_100_is_grade_a(): void
    {
        $this->assertSame('A', Result::calculateGrade(100));
    }

    /** Marks 0 — minimum valid score, F band */
    public function test_boundary_0_is_grade_f(): void
    {
        $this->assertSame('F', Result::calculateGrade(0));
    }
}
