<?php

namespace App\Services;

use App\Models\Form;
use App\Models\Submission;
use App\Models\SubmissionField;
use App\Models\User;
use App\Events\FormSubmitted;

class SubmissionService
{
    /**
     * Get or create submission for user and form
     */
    public function getOrCreateSubmission(?User $user, Form $form): Submission
    {
        if ($user) {
            $submission = Submission::where('form_id', $form->id)
                ->where('user_id', $user->id)
                ->first();

            if ($submission) {
                return $submission;
            }
        }

        // Create new submission for guest or authenticated user without existing submission
        $submission = new Submission();
        $submission->form_id = $form->id;
        $submission->user_id = $user?->id;
        $submission->generateCode();
        $submission->status = Submission::STATUS_DRAFT;
        $submission->save();

        // Create submission fields from form fields
        $this->createSubmissionFields($submission);

        return $submission;
    }

    /**
     * Create empty submission fields for all form fields
     */
    protected function createSubmissionFields(Submission $submission): void
    {
        $formFields = $submission->form->fields()->get();

        foreach ($formFields as $field) {
            SubmissionField::create([
                'submission_id' => $submission->id,
                'form_field_id' => $field->id,
                'answer' => null,
            ]);
        }
    }

    /**
     * Save/update submission fields from request data
     */
    protected function saveSubmissionFields(Submission $submission, array $submissionFields): void
    {
        foreach ($submissionFields as $fieldData) {
            // Find or create the submission field
            $submissionField = SubmissionField::where('submission_id', $submission->id)
                ->where('form_field_id', $fieldData['form_field_id'])
                ->first();

            if ($submissionField) {
                // Update existing
                $submissionField->update([
                    'answer' => $fieldData['answer'] ?? null,
                ]);
            } else {
                // Create new
                SubmissionField::create([
                    'submission_id' => $submission->id,
                    'form_field_id' => $fieldData['form_field_id'],
                    'answer' => $fieldData['answer'] ?? null,
                ]);
            }
        }
    }

    /**
     * Process and save submission with guest info
     */
    public function saveSubmissionWithGuestInfo(
        Submission $submission,
        array $submissionFields,
        ?string $email = null,
        ?string $guestName = null
    ): void
    {
        // Validate email if required
        if ($submission->form->isEmailRequired() && !$email) {
            throw new \InvalidArgumentException('Email is required for this form');
        }

        // Check for duplicate responses if not allowed
        if (!$submission->form->getOrCreateSettings()->allow_duplicate_responses && $email) {
            if (Submission::hasDuplicateEmail($submission->form, $email)) {
                throw new \InvalidArgumentException('This email has already submitted a response');
            }
        }

        // Save guest info
        if ($email) {
            $submission->email = $email;
        }
        if ($guestName) {
            $submission->guest_name = $guestName;
        }

        $submission->save();

        // Save fields
        $this->saveSubmissionFields($submission, $submissionFields);
    }

    /**
     * Mark submission as completed
     */
    public function markAsCompleted(Submission $submission): void
    {
        $submission->update(['status' => Submission::STATUS_PENDING]);
        event(new FormSubmitted($submission));
    }

    /**
     * Format submission data for API response
     */
    public function formatSubmissionData(Submission $submission): array
    {
        return [
            'id' => $submission->id,
            'code' => $submission->code,
            'status' => $submission->status,
            'user_id' => $submission->user_id,
            'email' => $submission->email,
            'created_at' => $submission->created_at,
            'updated_at' => $submission->updated_at,
            'user' => $submission->user ? [
                'id' => $submission->user->id,
                'name' => $submission->user->name,
                'email' => $submission->user->email,
            ] : null,
            'submissionFields' => $submission->submissionFields->map(fn($sf) => [
                'id' => $sf->id,
                'form_field_id' => $sf->form_field_id,
                'answer' => $sf->answer,
            ])->all(),
        ];
    }
}