<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use App\Models\Email\EmailLog;

class TemplatedMail extends Mailable
{
    public function __construct(
        public string $recipientEmail,
        public string $body,
        public string $type = 'html',
        public ?EmailLog $emailLog = null,
        public string $customSubject = ''
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->customSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.templated',
            with: [
                'body' => $this->body,
                'type' => $this->type,
            ],
        );
    }

    public function failed(?\Throwable $exception = null): void
    {
        if ($this->emailLog) {
            $this->emailLog->markFailed(
                $exception?->getMessage() ?? 'Unknown error'
            );
        }
    }
}