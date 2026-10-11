<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $fillable = [
        'course_code',
        'course_name',
        'credit_hours',
    ];

    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }
}
