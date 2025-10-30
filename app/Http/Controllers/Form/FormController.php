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
        $current_user->forms()->save($form);

        app(FormSectionController::class)->create($form);

        return redirect()->route('forms.edit', $form);
    }

    public function edit(Form $form)
    {
        return Inertia::render('forms/FormBuilder', [
            'form' => $form,
            'sections' => $form->sections,
            'fields' => $form->fields
        ]);
    }

    public function viewform(Form $form)
    {
        // if $form->requires_user is true, ensure user and check for previous submission.
        $current_user = Auth::user();

        // Check if user has any submissions for this form
        $submission = $current_user->submissions()->where('form_id', $form->id)->first();

        if (!$submission) {
            $submission_controller = new SubmissionController();
            $submission = $submission_controller->create($form);
            \Log::info('submission created', $submission->toArray());
        }
        else {
            \Log::info('submission fetched', $submission->toArray());
        }

        foreach ($form->fields as $field) {
            $submissionField = $submission->submissionFields()->where('form_field_id', $field->id)->first();
            if (!$submissionField) {
                $submission_field_controller = new SubmissionFieldController();
                $submissionField = $submission_field_controller->create($field, $submission);
                \Log::info('submission field created', $submissionField->toArray());
            }
            else {
                \Log::info('submission field fetched', $submissionField->toArray());
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

    public function update(Request $request, Form $form): Response
    {
        \Log::info('Update method hit', $request->all());

        $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'fields' => 'present|array'
        ]);

        \Log::info('Update validate passed', $request->all());
    
        $form->update([
            'title' => $request->title,
            'description' => $request->description
        ]);

        \Log::info('Update passed', $request->all());
    
        $existingFieldIds = [];
    
        foreach ($request->fields as $fieldData) {
            if (isset($fieldData['id'])) {
                // Update existing field
                $field = $form->fields()->find($fieldData['id']);
                if ($field) {
                    $field->update([
                        'label' => $fieldData['label'],
                        'type' => $fieldData['type'] ?? 'text',
                        'options' => $fieldData['options'] ?? null,
                        'required' => $fieldData['required'] ?? false,
                        'uuid' => $fieldData['uuid'] ?? $field->uuid,
                    ]);
                    $existingFieldIds[] = $field->id;
                }
            } else {
                // Create new field
                $newField = $form->fields()->create([
                    'label' => $fieldData['label'],
                    'type' => $fieldData['type'] ?? 'text',
                    'options' => $fieldData['options'] ?? null,
                    'required' => $fieldData['required'] ?? false,
                    'uuid' => $fieldData['uuid'] ?? (string) Str::uuid(),
                ]);
                $existingFieldIds[] = $newField->id;
            }
        }

        \Log::info('Update fields passed', $request->all());
    
        // Optionally delete fields that were removed in the frontend
        $form->fields()->whereNotIn('id', $existingFieldIds)->delete();

        \Log::info('Update deletefields passed', $request->all());
    
        //return back()->with('success', 'Form updated successfully.');
        //return back()->with('success', __('A reset link will be sent if the account exists.'));
        //return redirect()->route('forms.edit', $form->code)->with('success', 'Form updated successfully.');
        return Inertia::render('forms/FormBuilder', [
            'form' => $form->fresh(),
            'flash' => [
                'success' => 'Form updated successfully.',
            ],
        ]);

    }
    













    public function store(request $request)
    {
        $current_user = Auth::user();

        foreach ($request->fields as $field) {
            $form->fields()->create([
                'label' => $field['label'],
                'type' => $field['type'],
                'options' => $field['options'] ?? null,
                'required' => $field['required'] ?? false,
                'uuid' => (string) Str::uuid(),
            ]);
        }
    }

}