<?php

namespace App\Http\Controllers\Admin;

use App\Models\Email\EmailTemplate;
use App\Models\Email\EmailLog;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;
use Inertia\Response;
use App\Http\Controllers\Controller;

class EmailTemplateController extends Controller
{
    use AuthorizesRequests;
    
    public function __construct(
        protected EmailService $emailService
    ) {
    }

    public function index(): Response
    {
        $this->authorize('viewAny', EmailTemplate::class);

        $templates = EmailTemplate::orderBy('event')->get()->map(fn($t) => [
            'id' => $t->id,
            'key' => $t->key,
            'event' => $t->event,
            'name' => $t->name,
            'organisation_id' => $t->organisation_id,
            'enabled' => $t->enabled,
            'is_default' => $t->is_default,
        ]);

        $logs = EmailLog::with(['form', 'submission'])
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn($l) => [
                'id' => $l->id,
                'recipient_email' => $l->recipient_email,
                'event' => $l->event,
                'status' => $l->status,
                'sent_at' => $l->sent_at,
                'form' => $l->form?->title,
                'error_message' => $l->error_message,
            ]);

        return Inertia::render('admin/email/Index', [
            'templates' => $templates,
            'logs' => $logs,
        ]);
    }

    public function edit(EmailTemplate $emailTemplate): Response
    {
        $this->authorize('update', $emailTemplate);

        return Inertia::render('admin/email/Edit', [
            'template' => [
                'id' => $emailTemplate->id,
                'key' => $emailTemplate->key,
                'event' => $emailTemplate->event,
                'name' => $emailTemplate->name,
                'subject' => $emailTemplate->subject,
                'body' => $emailTemplate->body,
                'type' => $emailTemplate->type,
                'organisation_id' => $emailTemplate->organisation_id,
                'enabled' => $emailTemplate->enabled,
                'is_default' => $emailTemplate->is_default,
                'variables' => $emailTemplate->variables,
            ],
            'availableVariables' => config('email.events.' . $emailTemplate->event . '.variables', []),
        ]);
    }

    public function update(Request $request, EmailTemplate $emailTemplate): RedirectResponse
    {
        $this->authorize('update', $emailTemplate);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string',
            'body' => 'required|string',
            'type' => 'required|in:html,markdown',
            'enabled' => 'boolean',
        ]);

        $emailTemplate->update($validated);

        return redirect()->route('admin.email.index')
            ->with('success', 'Email template updated successfully');
    }

    public function sendTest(Request $request, EmailTemplate $emailTemplate): RedirectResponse
    {
        $this->authorize('view', $emailTemplate);

        $validated = $request->validate([
            'recipient_email' => 'required|email',
        ]);

        $success = $this->emailService->sendTest(
            $emailTemplate,
            $validated['recipient_email'],
            array_fill_keys(
                array_keys($emailTemplate->variables ?? []),
                'Test Value'
            )
        );

        if ($success) {
            return redirect()->back()->with('success', 'Test email sent to ' . $validated['recipient_email']);
        }

        return redirect()->back()->with('error', 'Failed to send test email');
    }
}