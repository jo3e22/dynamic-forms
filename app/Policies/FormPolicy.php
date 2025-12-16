<?php

namespace App\Policies;

use App\Models\Form;
use App\Models\User;
use App\Services\OrganisationService;

class FormPolicy
{
    public function __construct(
        protected OrganisationService $organisationService
    ) {}

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Form $form): bool
    {
        // Personal form
        if (!$form->isOwnedByOrganisation()) {
            return $form->isOwnedBy($user);
        }

        // Organisation form
        return $form->organisation->hasUser($user);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Form $form): bool
    {
        // Personal form
        if (!$form->isOwnedByOrganisation()) {
            return $form->isOwnedBy($user);
        }

        // Organisation form
        return $this->organisationService->userHasPermission(
            $user,
            $form->organisation,
            'forms.edit'
        );
    }

    public function delete(User $user, Form $form): bool
    {
        // Personal form
        if (!$form->isOwnedByOrganisation()) {
            return $form->isOwnedBy($user);
        }

        // Organisation form
        return $this->organisationService->userHasPermission(
            $user,
            $form->organisation,
            'forms.delete'
        );
    }

    public function publish(User $user, Form $form): bool
    {
        // Personal form
        if (!$form->isOwnedByOrganisation()) {
            return $form->isOwnedBy($user);
        }

        // Organisation form
        return $this->organisationService->userHasPermission(
            $user,
            $form->organisation,
            'forms.publish'
        );
    }

    public function viewSubmissions(User $user, Form $form): bool
    {
        // Personal form
        if (!$form->isOwnedByOrganisation()) {
            return $form->isOwnedBy($user);
        }

        // Organisation form
        return $this->organisationService->userHasPermission(
            $user,
            $form->organisation,
            'submissions.view'
        );
    }

    public function exportSubmissions(User $user, Form $form): bool
    {
        // Personal form
        if (!$form->isOwnedByOrganisation()) {
            return $form->isOwnedBy($user);
        }

        // Organisation form
        return $this->organisationService->userHasPermission(
            $form->organisation,
            $user,
            'submissions.export'
        );
    }
}