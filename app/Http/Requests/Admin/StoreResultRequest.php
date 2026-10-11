<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id'    => ['required', 'exists:students,id'],
            'course_id'     => ['required', 'exists:courses,id'],
            'academic_year' => ['required', 'in:Year 1,Year 2,Year 3,Year 4'],
            'semester'      => ['required', 'in:Semester 1,Semester 2'],
            'marks'         => ['required', 'integer', 'min:0', 'max:100'],
            // Unique combination: student + course + year + semester
            'course_id'     => [
                'required',
                'exists:courses,id',
                Rule::unique('results')->where(function ($query) {
                    return $query
                        ->where('student_id', $this->input('student_id'))
                        ->where('academic_year', $this->input('academic_year'))
                        ->where('semester', $this->input('semester'));
                }),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'course_id.unique' => 'A result for this student, course, academic year, and semester already exists.',
        ];
    }
}
