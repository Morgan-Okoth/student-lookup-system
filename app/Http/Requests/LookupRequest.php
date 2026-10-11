<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LookupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalise admission number before validation runs:
     * - trim whitespace
     * - convert to uppercase
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('admission_number')) {
            $this->merge([
                'admission_number' => strtoupper(trim($this->input('admission_number', ''))),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'admission_number' => [
                'required',
                'string',
                'regex:/^[A-Z]{2,4}[0-9]{2}\/[0-9]{5}\/[0-9]{2}$/',
            ],
            'academic_year' => [
                'required',
                'in:Year 1,Year 2,Year 3,Year 4',
            ],
            'semester' => [
                'required',
                'in:Semester 1,Semester 2',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'admission_number.required' => 'The admission number is required.',
            'admission_number.regex'    => 'The admission number format is invalid (e.g. IN16/00088/24).',
            'academic_year.required'    => 'Please select an academic year.',
            'academic_year.in'          => 'Please select a valid academic year.',
            'semester.required'         => 'Please select a semester.',
            'semester.in'               => 'Please select a valid semester.',
        ];
    }
}
