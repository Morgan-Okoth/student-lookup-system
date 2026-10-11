<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->restrictOnDelete();
            $table->string('academic_year', 10);
            $table->string('semester', 15);
            $table->unsignedTinyInteger('marks');
            $table->char('grade', 1);
            $table->timestamps();

            // Prevent duplicate result for same student/course/year/semester
            $table->unique(['student_id', 'course_id', 'academic_year', 'semester'], 'results_unique_combination');

            // Optimise the public lookup query
            $table->index(['student_id', 'academic_year', 'semester'], 'results_lookup_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};
