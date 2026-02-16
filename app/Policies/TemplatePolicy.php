<?php

namespace App\Policies;

use App\Models\Template\Template;
use App\Models\User;
use App\Models\Organisation\Organisation;

class TemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Template $template): bool
    {
        return $template->canBeUsedBy($user);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Template $template): bool
    {
        // User owns template
        if ($template->isOwnedBy($user)) {
            return true;
        }

        // Organisation template - check permissions
        if ($template->owner_type === Organisation::class) {
            return app(\App\Services\OrganisationService::class)
                ->userHasPermission($user, $template->owner, 'templates.edit');
        }

        return false;
    }

    public function delete(User $user, Template $template): bool
    {
        // User owns template
        if ($template->isOwnedBy($user)) {
            return true;
        }

        // Organisation template - check permissions
        if ($template->owner_type === Organisation::class) {
            return app(\App\Services\OrganisationService::class)
                ->userHasPermission($user, $template->owner, 'templates.delete');
        }

        return false;
    }

    public function use(User $user, Template $template): bool
    {
        return $template->canBeUsedBy($user);
    }

    public function rate(User $user, Template $template): bool
    {
        // Can rate if they can view it and haven't created it
        return $template->canBeUsedBy($user) && !$template->isOwnedBy($user);
    }
}