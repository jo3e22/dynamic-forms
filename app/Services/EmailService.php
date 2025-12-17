<?php

namespace App\Services;

use App\Models\Email\EmailTemplate;
use App\Models\Email\EmailLog;
use App\Models\Form;
use App\Models\Submission;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailService
{
    /**
     * Send templated email
     */
    public function send(
        string $event,
        string $recipientEmail,
        array $variables = [],
        ?Form $form = null,
        ?Submission $submission = null,
        ?int $organisationId = null,
        bool $queue = true
    ): ?EmailLog {
        try {
            // Get template
            $template = EmailTemplate::getForEvent($event, $organisationId);
            
            if (!$template) {
                Log::warning("Email template not found for event: {$event}");
                return null;
            }

            // Hydrate variables
            $hydratedTemplate = $template->hydrateVariables($variables);

            // Log email
            $emailLog = EmailLog::create([
                'recipient_email' => $recipientEmail,
                'event' => $event,
                'form_id' => $form?->id,
                'submission_id' => $submission?->id,
                'subject' => $hydratedTemplate->subject,
                'body' => $hydratedTemplate->body,
                'status' => 'queued',
            ]);

            // Queue email if configured, otherwise send immediately
            if ($queue && config('queue.default') !== 'sync') {
                Mail::queue(new \App\Mail\TemplatedMail(
                    recipientEmail: $recipientEmail,
                    customSubject: $hydratedTemplate->subject,
                    body: $hydratedTemplate->body,
                    type: $template->type,
                    emailLog: $emailLog,
                ));
            } else {
                Mail::send(new \App\Mail\TemplatedMail(
                    recipientEmail: $recipientEmail,
                    customSubject: $hydratedTemplate->subject,
                    body: $hydratedTemplate->body,
                    type: $template->type,
                    emailLog: $emailLog,
                ));
                $emailLog->markSent();
            }

            return $emailLog;
        } catch (\Exception $e) {
            Log::error("Email send failed for event {$event}", [
                'recipient' => $recipientEmail,
                'error' => $e->getMessage(),
            ]);
            
            if (isset($emailLog)) {
                $emailLog->markFailed($e->getMessage());
            }

            return null;
        }
    }

    /**
     * Bulk send to multiple recipients
     */
    public function sendToMultiple(
        string $event,
        array $recipientEmails,
        array $variables = [],
        ?Form $form = null,
        ?Submission $submission = null,
        ?int $organisationId = null,
    ): array {
        $logs = [];
        
        foreach ($recipientEmails as $email) {
            $log = $this->send(
                event: $event,
                recipientEmail: $email,
                variables: $variables,
                form: $form,
                submission: $submission,
                organisationId: $organisationId,
            );
            
            if ($log) {
                $logs[] = $log;
            }
        }

        return $logs;
    }

    /**
     * Test send a template
     */
    public function sendTest(
        EmailTemplate $template,
        string $recipientEmail,
        array $variables = []
    ): bool {
        try {
            $hydratedTemplate = $template->hydrateVariables($variables);

            Mail::send(new \App\Mail\TemplatedMail(
                recipientEmail: $recipientEmail,
                customSubject: $hydratedTemplate->subject,
                body: $hydratedTemplate->body,
                type: $template->type,
                emailLog: null,
            ));

            return true;
        } catch (\Exception $e) {
            Log::error("Test email failed for template {$template->id}", [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}