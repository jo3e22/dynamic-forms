<?php

namespace App\Http\Controllers\Form;

use Auth;
use Inertia\Inertia;
use App\Models\Form;
use App\Models\Submission;
use App\Services\FormService;
use App\Services\SubmissionService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Inertia\Response;

class SubmissionController extends Controller
{
    public function __construct(
        protected FormService $formService,
        protected SubmissionService $submissionService,
        protected NotificationService $notificationService
    ) {}

    public function index(Form $form): Response
    {
        $data = $this->formService->buildFormData($form);

        $submissions = $form->submissions()
            ->with(['user', 'submissionFields'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($submission) => $this->submissionService->formatSubmissionData($submission));

        return Inertia::render('forms/SubmissionViewer', [
            'form' => [
                'id' => $form->id,
                'code' => $form->code,
                'status' => $form->status,
                'primary_color' => $form->primary_color,
                'secondary_color' => $form->secondary_color,
            ],
            'data' => $data,
            'submissions' => $submissions,
        ]);
    }

    public function show(Form $form, Submission $submission): Response
    {
        $data = $this->formService->buildFormData($form);

        $submissions = $form->submissions()
            ->with(['user', 'submissionFields'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($sub) => [
                'id' => $sub->id,
                'code' => $sub->code,
                'status' => $sub->status,
                'user_id' => $sub->user_id,
                'email' => $sub->email,
                'created_at' => $sub->created_at,
                'updated_at' => $sub->updated_at,
                'user' => $sub->user ? [
                    'id' => $sub->user->id,
                    'name' => $sub->user->name,
                    'email' => $sub->user->email,
                ] : null,
            ]);

        $selectedSubmission = $this->submissionService->formatSubmissionData($submission);

        return Inertia::render('forms/SubmissionViewer', [
            'form' => [
                'id' => $form->id,
                'code' => $form->code,
                'status' => $form->status,
                'primary_color' => $form->primary_color,
                'secondary_color' => $form->secondary_color,
            ],
            'data' => $data,
            'submissions' => $submissions,
            'selectedSubmission' => $selectedSubmission,
        ]);
    }

    public function submit(Request $request, Form $form, Submission $submission): Response
    {
        $validated = $request->validate([
            'submissionFields' => 'required|array',
            'submissionFields.*.id' => 'nullable|integer|exists:submission_fields,id',
            'submissionFields.*.form_field_id' => 'required|integer|exists:form_fields,id',
            'submissionFields.*.submission_id' => 'required|integer',
            'submissionFields.*.answer' => 'nullable',
        ]);

        $this->submissionService->saveSubmissionFields($submission, $validated['submissionFields']);
        $this->submissionService->markAsCompleted($submission);
        // Notify form owner of new submission
        $this->notificationService->notifyNewSubmission(
            $form->user,
            $form,
            $submission
        );

        $formTitle = $this->formService->getFormTitle($form);

        return Inertia::render('forms/SubmissionSuccess', [
            'form' => [
                'id' => $form->id,
                'code' => $form->code,
                'primary_color' => $form->primary_color,
                'secondary_color' => $form->secondary_color,
            ],
            'formTitle' => $formTitle,
        ]);
    }
}