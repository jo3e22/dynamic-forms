<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Or add authorization logic
    }

    public function rules(): array
    {
        return [
            'submissionFields' => ['required', 'array'],
            'submissionFields.*.id' => ['nullable', 'integer', 'exists:submission_fields,id'],
            'submissionFields.*.form_field_id' => ['required', 'integer', 'exists:form_fields,id'],
            'submissionFields.*.submission_id' => ['required', 'integer'],
            'submissionFields.*.answer' => ['nullable'],
        ];
    }
}