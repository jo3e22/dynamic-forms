<?php

namespace App\Http\Controllers\Form;

use Auth;
use Inertia\Inertia;
use App\Models\Form;
use App\Models\FormSection;
use App\Models\FormField;
use App\Models\Submission;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Form\FormFieldController;
use App\Http\Controllers\Form\FormSectionController;
use App\Http\Controllers\Form\SubmissionController;
use App\Http\Controllers\Form\SubmissionFieldController;
use Inertia\Response;

class FormController extends Controller
{
    public function index()
    {
        $forms = Form::with(['sections' => fn($q) => $q->orderBy('section_order')])
            ->latest()
            ->get(['id','status','code']);

        return Inertia::render('forms/Index', [
            'forms' => $forms->map(fn($f) => [
                'id' => $f->id,
                'code' => $f->code,
                'status' => $f->status,
                'title' => $f->title, // accessor applied
            ]),
        ]);
    }

    public function create()
    {
        $current_user = Auth::user();

        $form = new Form();
        $form->generateCode();
        $form->status = Form::STATUS_DRAFT;
        $form->primary_color = '#3B82F6'; // Default blue
        $form->secondary_color = '#EFF6FF'; // Light blue
        $current_user->forms()->save($form);

        // Create first section with title and description
        $section = new FormSection();
        $section->form_id = $form->id;
        $section->section_order = 0;
        $section->title = 'Untitled Form';
        $section->description = null;
        $section->save();

        return redirect()->route('forms.edit', $form);
    }

    public function edit(Form $form)
    {
        $data = $this->buildFormData($form);

        return Inertia::render('forms/FormBuilder', [
            'form' => [
                'id' => $form->id,
                'code' => $form->code,
                'status' => $form->status,
                'user_id' => $form->user_id,
                'primary_color' => $form->primary_color ?? '#3B82F6',
                'secondary_color' => $form->secondary_color ?? '#EFF6FF',
                'created_at' => $form->created_at,
                'updated_at' => $form->updated_at,
            ],
            'data' => $data
        ]);
    }

    protected function buildFormData(Form $form): array
    {
        $form->load([
            'sections' => fn($q) => $q->orderBy('section_order'),
            'fields' => fn($q) => $q->orderBy('field_order'),
        ]);

        $data = [];

        foreach ($form->sections as $i => $section) {
            // Get fields for this section
            $sectionFields = $form->fields
                ->where('section', $section->id)
                ->sortBy('field_order')
                ->values();

            $data[] = [
                'id' => $section->id,
                'section_order' => $section->section_order,
                'title' => $section->title,
                'description' => $section->description,
                'fields' => $sectionFields->map(fn($f) => [
                    'id' => $f->id,
                    'label' => $f->label,
                    'type' => $f->type,
                    'options' => $f->options, // Already parsed by Laravel's JSON cast
                    'required' => (bool) $f->required,
                    'field_order' => $f->field_order,
                ])->all(),
            ];
        }

        return $data;
    }

    public function update(Request $request, Form $form): Response
    {
        \Log::info('Form update started', ['form_id' => $form->id]);

        $validated = $request->validate([
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
            'colors.primary' => ['nullable', 'string', 'max:7'],
            'colors.secondary' => ['nullable', 'string', 'max:7'],
        ]);

        // Update form colors if provided
        if (isset($validated['colors'])) {
            $form->update([
                'primary_color' => $validated['colors']['primary'] ?? $form->primary_color,
                'secondary_color' => $validated['colors']['secondary'] ?? $form->secondary_color,
            ]);
        }

        $sections = collect($validated['data']);
        $existingSectionIds = [];
        $existingFieldIds = [];

        foreach ($sections as $sectionData) {
            // Create or update section
            if (isset($sectionData['id'])) {
                $section = $form->sections()->find($sectionData['id']);
                if ($section) {
                    $section->update([
                        'title' => $sectionData['title'],
                        'description' => $sectionData['description'],
                        'section_order' => $sectionData['section_order'],
                    ]);
                    $existingSectionIds[] = $section->id;
                }
            } else {
                $section = $form->sections()->create([
                    'title' => $sectionData['title'],
                    'description' => $sectionData['description'],
                    'section_order' => $sectionData['section_order'],
                ]);
                $existingSectionIds[] = $section->id;
            }

            // Process fields for this section
            foreach ($sectionData['fields'] as $fieldData) {
                if (isset($fieldData['id'])) {
                    // Update existing field
                    $field = $form->fields()->find($fieldData['id']);
                    if ($field) {
                        $field->update([
                            'section' => $section->id,
                            'label' => $fieldData['label'],
                            'type' => $fieldData['type'],
                            'options' => $fieldData['options'],
                            'required' => $fieldData['required'],
                            'field_order' => $fieldData['field_order'],
                        ]);
                        $existingFieldIds[] = $field->id;
                    }
                } else {
                    // Create new field
                    $field = $form->fields()->create([
                        'section' => $section->id,
                        'label' => $fieldData['label'],
                        'type' => $fieldData['type'],
                        'options' => $fieldData['options'],
                        'required' => $fieldData['required'],
                        'field_order' => $fieldData['field_order'],
                    ]);
                    $existingFieldIds[] = $field->id;
                }
            }
        }

        // Delete removed sections and fields
        $form->sections()->whereNotIn('id', $existingSectionIds)->delete();
        $form->fields()->whereNotIn('id', $existingFieldIds)->delete();

        \Log::info('Form update completed', ['form_id' => $form->id]);

        // Rebuild data for response
        $data = $this->buildFormData($form->fresh(['sections', 'fields']));

        return Inertia::render('forms/FormBuilder', [
            'form' => [
                'id' => $form->id,
                'code' => $form->code,
                'status' => $form->status,
                'user_id' => $form->user_id,
                'primary_color' => $form->primary_color,
                'secondary_color' => $form->secondary_color,
                'created_at' => $form->created_at,
                'updated_at' => $form->updated_at,
            ],
            'data' => $data,
            'flash' => [
                'success' => 'Form updated successfully.',
            ],
        ]);
    }

    public function jsonform(Form $form)
    {
        $data = $this->buildFormData($form);
        return response()->json(['data' => $data]);
    }

    public function viewform(Form $form)
    {
        $current_user = Auth::user();
        $submission = $current_user->submissions()->where('form_id', $form->id)->first();

        if (!$submission) {
            $submission_controller = new SubmissionController();
            $submission = $submission_controller->create($form);
        }

        foreach ($form->fields as $field) {
            $submissionField = $submission->submissionFields()->where('form_field_id', $field->id)->first();
            if (!$submissionField) {
                $submission_field_controller = new SubmissionFieldController();
                $submissionField = $submission_field_controller->create($field, $submission);
            }
        }

        return Inertia::render('forms/DynamicForm', [
            'form' => $form,
            'fields' => $form->fields,
            'submission' => $submission,
            'submissionFields' => $submission->submissionFields
        ]);
    }

    public function viewformsubmission(Form $form, Submission $submission)
    {
        return Inertia::render('forms/DynamicForm', [
            'form' => $form,
            'fields' => $form->fields,
            'submission' => $submission,
            'submissionFields' => $submission->submissionFields
        ]);
    }

    public function destroy(Form $form): RedirectResponse
    {
        // Check if user owns the form (add authorization if needed)
        if ($form->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $form->delete(); // Soft delete due to SoftDeletes trait

        return redirect()->route('forms.index')->with('success', 'Form deleted successfully.');
    }

}