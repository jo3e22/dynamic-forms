<?php

namespace App\Http\Controllers\Form;


use Inertia\Inertia;
use App\Models\Form;
use App\Services\FormService;
use App\Services\SubmissionService;
use App\Http\Requests\UpdateFormRequest;
use App\Http\Resources\FormResource;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use Inertia\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class FormController extends Controller
{
    public function __construct(
        protected FormService $formService,
        protected SubmissionService $submissionService
    ) {}

    public function index()
    {
        $forms = Auth::user()->forms()->withCount('submissions')->latest()->get();
        
        return Inertia::render('Dashboard', [
            'forms' => $forms,
        ]);
    }

    public function create()
    {
        $form = $this->formService->createForm(Auth::user());
        return redirect()->route('forms.edit', $form);
    }

    public function edit(Form $form)
    {
        $data = $this->formService->buildFormData($form);

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

    public function update(UpdateFormRequest $request, Form $form): Response
    {
        try {
            $form = $this->formService->updateFormStructure(
                $form,
                $request->validated()['data'],
                $request->validated()['colors'] ?? null
            );

            $data = $this->formService->buildFormData($form);

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
                'flash' => ['success' => 'Form updated successfully.'],
            ]);
        } catch (\Exception $e) {
            \Log::error('Form update failed', [
                'form_id' => $form->id,
                'error' => $e->getMessage()
            ]);
            
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
                'data' => $this->formService->buildFormData($form),
                'errors' => ['error' => 'Failed to update form. Please try again.'],
            ]);
        }
    }

    public function jsonform(Form $form)
    {
        $data = $this->formService->buildFormData($form);
        return response()->json(['data' => $data]);
    }

    public function viewform(Form $form)
    {
        $submission = $this->submissionService->getOrCreateSubmission(Auth::user(), $form);
        $submissionFields = $submission->submissionFields;
        $data = $this->formService->buildFormData($form);

        return Inertia::render('forms/FormViewer', [
            'form' => [
                'id' => $form->id,
                'code' => $form->code,
                'status' => $form->status,
                'primary_color' => $form->primary_color,
                'secondary_color' => $form->secondary_color,
            ],
            'data' => $data,
            'submission' => [
                'id' => $submission->id,
                'code' => $submission->code,
                'status' => $submission->status,
            ],
            'submissionFields' => $submissionFields->map(fn($sf) => [
                'id' => $sf->id,
                'form_field_id' => $sf->form_field_id,
                'answer' => $sf->answer,
            ])->all(),
        ]);
    }

    public function destroy(Form $form): RedirectResponse
    {
        if ($form->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $form->delete();
        return redirect()->route('forms.index')->with('success', 'Form deleted successfully.');
    }

    public function show(Form $form)
    {
        if ($form->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $recentSubmissions = $form->submissions()
            ->with('user')
            ->latest()
            ->limit(10)
            ->get();

        return Inertia::render('forms/FormDashboard', [
            'form' => $form->load('submissions'),
            'recentSubmissions' => $recentSubmissions,
        ]);
    }
}