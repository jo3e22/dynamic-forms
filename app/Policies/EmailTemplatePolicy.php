<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Email\EmailTemplate;

class EmailTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_admin;
    }

    public function view(User $user, EmailTemplate $template): bool
    {
        return $user->is_admin || 
               ($template->organisation_id && $user->organisations()->where('id', $template->organisation_id)->exists());
    }

    public function create(User $user): bool
    {
        return $user->is_admin;
    }

    public function update(User $user, EmailTemplate $template): bool
    {
        return $user->is_admin || 
               ($template->organisation_id && $user->organisations()->where('id', $template->organisation_id)->exists());
    }

    public function delete(User $user, EmailTemplate $template): bool
    {
        return $user->is_admin && !$template->is_default;
    }
}