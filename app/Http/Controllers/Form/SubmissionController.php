<?php

namespace App\Http\Controllers\Form;

use Auth;
use Inertia\Inertia;
use App\Models\Form;
use App\Models\Submission;
use App\Services\FormService;
use App\Services\SubmissionService;
use App\Services\NotificationService;
use App\Services\EmailService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Inertia\Response;

class SubmissionController extends Controller
{
    public function __construct(
        protected FormService $formService,
        protected SubmissionService $submissionService,
        protected NotificationService $notificationService,
        protected EmailService $emailService
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
        $rules = [
            'submissionFields' => 'required|array',
            'submissionFields.*.id' => 'nullable|integer|exists:submission_fields,id',
            'submissionFields.*.form_field_id' => 'required|integer|exists:form_fields,id',
            'submissionFields.*.submission_id' => 'required|integer',
            'submissionFields.*.answer' => 'nullable',
        ];

        // Add guest email validation if needed
        if ($form->isEmailRequired()) {
            $rules['guest_email'] = 'required|email';
        } elseif ($form->isGuestAllowed()) {
            $rules['guest_email'] = 'nullable|email';
        }

        if ($form->isGuestAllowed()) {
            $rules['guest_name'] = 'nullable|string|max:255';
        }

        $validated = $request->validate($rules);

        try {
            $this->submissionService->saveSubmissionWithGuestInfo(
                $submission,
                $validated['submissionFields'],
                $validated['guest_email'] ?? null,
                $validated['guest_name'] ?? null
            );
        } catch (\InvalidArgumentException $e) {
            $error = $e->getMessage();
        }

        $this->submissionService->markAsCompleted($submission);

        // Notify form owner
        $this->notificationService->notifyNewSubmission($form->user, $form, $submission);

        // Send emails
        $formTitle = $this->formService->getFormTitle($form);
        $organisationId = $form->organisation_id;

        // Email to respondent (if provided)
        $respondentEmail = $submission->email ?? auth()->user()?->email;
        if ($respondentEmail) {
            $this->emailService->send(
                event: 'submission.received',
                recipientEmail: $respondentEmail,
                variables: [
                    'form_title' => $formTitle,
                    'organisation_name' => $form->organisation?->name ?? 'Our Team',
                    'recipient_name' => $submission->guest_name ?? auth()->user()?->name ?? 'Respondent',
                    'submission_link' => route('submissions.show', [$form->code, $submission->code]),
                    'created_at' => $submission->created_at->format('F d, Y'),
                ],
                form: $form,
                submission: $submission,
                organisationId: $organisationId,
            );
        }

        // Email to managers
        $managerEmails = [$form->user->email];
        if ($organisationId) {
            $managerEmails = $form->organisation->users()
                ->where('pivot_role', '!=', 'member')
                ->pluck('email')
                ->toArray();
        }

        foreach ($managerEmails as $managerEmail) {
            $this->emailService->send(
                event: 'submission.manager_alert',
                recipientEmail: $managerEmail,
                variables: [
                    'form_title' => $formTitle,
                    'organisation_name' => $form->organisation?->name ?? 'Our Team',
                    'recipient_name' => 'Manager',
                    'respondent_email' => $submission->email ?? 'Unknown',
                    'respondent_name' => $submission->guest_name ?? auth()->user()?->name ?? 'Unknown',
                    'submission_link' => route('submissions.show', [$form->code, $submission->code]),
                    'created_at' => $submission->created_at->format('F d, Y'),
                ],
                form: $form,
                submission: $submission,
                organisationId: $organisationId,
            );
        }

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