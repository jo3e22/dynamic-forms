<?php

namespace App\Policies;

use App\Models\Organisation\Organisation;
use App\Models\User;
use App\Services\OrganisationService;

class OrganisationPolicy
{
    public function __construct(
        protected OrganisationService $organisationService
    ) {}

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Organisation $organisation): bool
    {
        return $organisation->hasUser($user);
    }

    public function create(User $user): bool
    {
        return $user->is_admin;
    }

    public function update(User $user, Organisation $organisation): bool
    {
        return $this->organisationService->userHasPermission($user, $organisation, 'organisation.manage');
    }

    public function delete(User $user, Organisation $organisation): bool
    {
        return $this->organisationService->userHasPermission($user, $organisation, 'organisation.delete');
    }

    public function manageMembers(User $user, Organisation $organisation): bool
    {
        return $this->organisationService->userHasPermission($user, $organisation, 'members.manage');
    }

    public function createForms(User $user, Organisation $organisation): bool
    {
        return $this->organisationService->userHasPermission($user, $organisation, 'forms.create');
    }

    public function manageTemplates(User $user, Organisation $organisation): bool
    {
        return $this->organisationService->userHasPermission($user, $organisation, 'templates.create');
    }
}