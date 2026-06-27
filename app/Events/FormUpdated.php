<?php

namespace App\Events;

use App\Models\Submission;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FormUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(public Submission $submission)
    {
    }




    #the aim of this event is to allow forms to be updated while active and to eail respondants allowing them to resubmit their repsonces tot the now updated form.
}