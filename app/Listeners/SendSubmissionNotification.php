<?php

namespace App\Listeners;

use App\Events\FormSubmitted;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendSubmissionNotification implements ShouldQueue
{
    public function handle(FormSubmitted $event): void
    {
        // Send notification to form owner
        // Log the submission
        // etc.
    }
}