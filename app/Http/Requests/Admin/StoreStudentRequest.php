<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'admission_number' => ['required', 'string', 'max:20', 'unique:students,admission_number'],
            'full_name'        => ['required', 'string', 'max:255'],
            'programme'        => ['required', 'string', 'max:255'],
            'year_of_study'    => ['required', 'integer', 'min:1', 'max:4'],
        ];
    }

    public function messages(): array
    {
        return [
            'admission_number.unique' => 'A student with this admission number already exists.',
        ];
    }
}
