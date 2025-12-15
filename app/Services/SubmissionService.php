<?php

namespace App\Services;

use App\Models\Form;
use App\Models\Submission;
use App\Models\User;
use App\Events\FormSubmitted;

class SubmissionService
{
    /**
     * Get or create submission for user and form
     */
    public function getOrCreateSubmission(User $user, Form $form): Submission
    {
        $submission = $user->submissions()->where('form_id', $form->id)->first();

        if (!$submission) {
            $submission = new Submission();
            $submission->generateCode();
            $submission->status = Submission::STATUS_DRAFT;
            $submission->form_id = $form->id;
            $submission->user_id = $user->id;
            $submission->save();
        }

        return $submission;
    }

    /**
     * Process and save submission fields
     */
    public function saveSubmissionFields(Submission $submission, array $submissionFieldsData): void
    {
        $existingSubmissionFieldIds = [];

        foreach ($submissionFieldsData as $submissionFieldData) {
            // Parse the answer if it's a JSON string
            $answer = $submissionFieldData['answer'];
            if (is_string($answer) && json_decode($answer) !== null) {
                $answer = json_decode($answer, true);
            }

            if (isset($submissionFieldData['id'])) {
                // Update existing field
                $submissionField = $submission->submissionFields()->find($submissionFieldData['id']);
                if ($submissionField) {
                    $submissionField->update([
                        'form_field_id' => $submissionFieldData['form_field_id'],
                        'answer' => $answer,
                    ]);
                    $existingSubmissionFieldIds[] = $submissionField->id;
                }
            } else {
                // Create new field
                $newSubmissionField = $submission->submissionFields()->create([
                    'form_field_id' => $submissionFieldData['form_field_id'],
                    'answer' => $answer,
                ]);
                $existingSubmissionFieldIds[] = $newSubmissionField->id;
            }
        }

        // Delete fields that were removed
        $submission->submissionFields()->whereNotIn('id', $existingSubmissionFieldIds)->delete();
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