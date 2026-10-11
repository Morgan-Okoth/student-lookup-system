<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Result extends Model
{
    protected $fillable = [
        'student_id',
        'course_id',
        'academic_year',
        'semester',
        'marks',
        'grade',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Calculate grade from marks.
     * A: 70–100 | B: 60–69 | C: 50–59 | D: 40–49 | F: 0–39
     */
    public static function calculateGrade(int $marks): string
    {
        return match (true) {
            $marks >= 70 => 'A',
            $marks >= 60 => 'B',
            $marks >= 50 => 'C',
            $marks >= 40 => 'D',
            default      => 'F',
        };
    }
}
