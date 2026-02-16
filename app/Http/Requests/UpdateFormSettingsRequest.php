<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFormSettingsRequest extends FormRequest
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

/*
    protected function validateSettings(Request $request): array
    {
        $validated = $request->validate([
            'sharing_type' => 'required|string|in:authenticated_only,guest_allowed,guest_email_required',
            'allow_duplicate_responses' => 'boolean',
            'confirmation_email' => 'required|string|in:none,confirmation_only,linked_copy_of_responses,detailed_copy_of_responses',
            'open_at' => 'nullable|date',
            'close_at' => 'nullable|date|after_or_equal:open_at',
            'max_submissions' => 'nullable|integer|min:1',
            'allow_response_editing' => 'boolean',
            'confirmation_message' => 'nullable|string',
        ]);
        // Merge defaults for booleans if missing
        return array_merge([
            'allow_duplicate_responses' => false,
            'allow_response_editing' => false,
        ], $validated);
    }
*/
