<?php

namespace App\Http\Controllers\Form;

use Auth;
use Inertia\Inertia;
use App\Models\Form;
use App\Models\FormField;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use Inertia\Response;

class FormController extends Controller
{
    public function index()
    {
        $forms = Form::select('id', 'title', 'status')->latest()->get();

        return Inertia::render('forms/Index', [
            'forms' => $forms
        ]);
    }

    public function create()
    {
        $current_user = Auth::user();

        $form = new Form();
        $form->generateCode();
        $form->status = Form::STATUS_DRAFT;
        $current_user->forms()->save($form);

        return redirect()->route('forms.edit', $form);
    }

    public function edit(Form $form)
    {
        return Inertia::render('forms/FormBuilder', [
            'form' => $form,
            'fields' => $form->fields
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