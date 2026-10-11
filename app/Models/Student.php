<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $fillable = [
        'admission_number',
        'full_name',
        'programme',
        'year_of_study',
    ];

    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }

    /**
     * Scope to find a student by their admission number (case-insensitive exact match).
     */
    public function scopeWhereAdmissionNumber(Builder $query, string $admissionNumber): Builder
    {
        return $query->where('admission_number', $admissionNumber);
    }
}
