<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->id === $this->route('form')->user_id;
    }

    public function rules(): array
    {
        return [
            'data' => ['required', 'array', 'min:1'],
            'data.*.id' => ['nullable', 'integer', 'exists:form_sections,id'],
            'data.*.title' => ['nullable', 'string', 'max:500'],
            'data.*.description' => ['nullable', 'string', 'max:2000'],
            'data.*.section_order' => ['required', 'integer', 'min:0'],
            'data.*.fields' => ['required', 'array'],
            'data.*.fields.*.id' => ['nullable', 'integer', 'exists:form_fields,id'],
            'data.*.fields.*.label' => ['required', 'string', 'max:255'],
            'data.*.fields.*.type' => ['required', 'string', 'in:short-answer,email,long-answer,checkbox,multiple-choice,textarea'],
            'data.*.fields.*.options' => ['nullable'],
            'data.*.fields.*.required' => ['required', 'boolean'],
            'data.*.fields.*.field_order' => ['required', 'integer', 'min:0'],
            'colors.primary' => ['nullable', 'string', 'regex:/^#[0-9A-F]{6}$/i'],
            'colors.secondary' => ['nullable', 'string', 'regex:/^#[0-9A-F]{6}$/i'],
        ];
    }
}