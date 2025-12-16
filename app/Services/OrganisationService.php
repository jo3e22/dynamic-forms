<?php

namespace App\Services;

use App\Models\Organisation\Organisation;
use App\Models\User;
use App\Enums\OrganisationRole;
use Illuminate\Support\Collection;

class OrganisationService
{
    public function createOrganisation(array $data, User $owner): Organisation
    {
        $organisation = Organisation::create([
            'name' => $data['name'],
            'slug' => $data['slug'] ?? null,
            'short_name' => $data['short_name'] ?? null,
            'parent_id' => $data['parent_id'] ?? null,
            'type' => $data['type'] ?? 'other',
            'visibility' => $data['visibility'] ?? 'public',
            'owner_id' => $owner->id,
        ]);

        // Create branding if provided
        if (isset($data['branding'])) {
            $organisation->branding()->create($data['branding']);
        }

        // Create details if provided
        if (isset($data['details'])) {
            $organisation->details()->create($data['details']);
        }

        // Add owner as member with owner role
        $organisation->addUser($owner, OrganisationRole::OWNER->value);
        $organisation->activateUser($owner);

        // Log activity
        $this->logActivity($organisation, 'created', $owner, [
            'attributes' => $organisation->toArray(),
        ]);

        return $organisation->fresh(['branding', 'details']);
    }

    public function updateOrganisation(Organisation $organisation, array $data): Organisation
    {
        $old = $organisation->toArray();

        $organisation->update([
            'name' => $data['name'] ?? $organisation->name,
            'slug' => $data['slug'] ?? $organisation->slug,
            'short_name' => $data['short_name'] ?? $organisation->short_name,
            'type' => $data['type'] ?? $organisation->type,
            'visibility' => $data['visibility'] ?? $organisation->visibility,
            'allow_member_form_creation' => $data['allow_member_form_creation'] ?? $organisation->allow_member_form_creation,
            'require_form_approval' => $data['require_form_approval'] ?? $organisation->require_form_approval,
        ]);

        // Update branding
        if (isset($data['branding'])) {
            $organisation->branding()->updateOrCreate(
                ['organisation_id' => $organisation->id],
                $data['branding']
            );
        }

        // Update details
        if (isset($data['details'])) {
            $organisation->details()->updateOrCreate(
                ['organisation_id' => $organisation->id],
                $data['details']
            );
        }

        // Log activity
        $this->logActivity($organisation, 'updated', auth()->user(), [
            'old' => $old,
            'attributes' => $organisation->fresh()->toArray(),
        ]);

        return $organisation->fresh(['branding', 'details']);
    }

    public function getUserOrganisations(User $user): Collection
    {
        return $user->organisations()
            ->with(['owner', 'branding', 'parent'])
            ->withCount(['forms', 'users'])
            ->get();
    }

    public function userHasPermission(User $user, Organisation $organisation, string $permission): bool
    {
        if (!$organisation->hasUser($user)) {
            return false;
        }

        // Owner always has all permissions
        if ($organisation->isOwner($user)) {
            return true;
        }

        // Check custom permissions first
        $customPermissions = $organisation->getUserPermissions($user);
        if ($customPermissions !== null) {
            return in_array($permission, $customPermissions);
        }

        // Fall back to role-based permissions
        $role = $organisation->getUserRole($user);
        if (!$role) {
            return false;
        }

        $roleEnum = OrganisationRole::from($role);
        return $roleEnum->hasPermission($permission);
    }

    public function getUserRole(User $user, Organisation $organisation): ?OrganisationRole
    {
        $role = $organisation->getUserRole($user);
        return $role ? OrganisationRole::from($role) : null;
    }

    public function inviteMember(
        Organisation $organisation,
        string $email,
        string $role,
        ?array $permissions = null,
        ?User $invitedBy = null
    ): void {
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            throw new \Exception('User not found with email: ' . $email);
        }

        if ($organisation->hasUser($user)) {
            throw new \Exception('User is already a member of this organisation');
        }

        if (!in_array($role, OrganisationRole::values())) {
            throw new \Exception('Invalid role');
        }

        $organisation->addUser($user, $role, $permissions, $invitedBy?->id);

        // Log activity
        $this->logActivity($organisation, 'member_invited', $invitedBy, [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'role' => $role,
        ]);

        // TODO: Send invitation email
    }

    public function removeMember(Organisation $organisation, User $user, ?User $removedBy = null): void
    {
        if ($organisation->isOwner($user)) {
            throw new \Exception('Cannot remove organisation owner');
        }

        $role = $organisation->getUserRole($user);
        $organisation->removeUser($user);

        // Log activity
        $this->logActivity($organisation, 'member_removed', $removedBy, [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'role' => $role,
        ]);
    }

    public function updateMemberRole(
        Organisation $organisation,
        User $user,
        string $role,
        ?User $updatedBy = null
    ): void {
        if ($organisation->isOwner($user)) {
            throw new \Exception('Cannot change owner role');
        }

        if (!in_array($role, OrganisationRole::values())) {
            throw new \Exception('Invalid role');
        }

        $oldRole = $organisation->getUserRole($user);
        $organisation->updateUserRole($user, $role);

        // Log activity
        $this->logActivity($organisation, 'member_role_updated', $updatedBy, [
            'user_id' => $user->id,
            'old_role' => $oldRole,
            'new_role' => $role,
        ]);
    }

    public function updateMemberPermissions(
        Organisation $organisation,
        User $user,
        array $permissions,
        ?User $updatedBy = null
    ): void {
        if ($organisation->isOwner($user)) {
            throw new \Exception('Cannot change owner permissions');
        }

        $oldPermissions = $organisation->getUserPermissions($user);
        $organisation->updateUserPermissions($user, $permissions);

        // Log activity
        $this->logActivity($organisation, 'member_permissions_updated', $updatedBy, [
            'user_id' => $user->id,
            'old_permissions' => $oldPermissions,
            'new_permissions' => $permissions,
        ]);
    }

    public function transferOwnership(
        Organisation $organisation,
        User $newOwner,
        ?User $transferredBy = null
    ): void {
        if (!$organisation->hasUser($newOwner)) {
            throw new \Exception('New owner must be a member of the organisation');
        }

        $oldOwner = $organisation->owner;
        
        // Update organisation owner
        $organisation->update(['owner_id' => $newOwner->id]);

        // Update roles
        $organisation->updateUserRole($newOwner, OrganisationRole::OWNER->value);
        $organisation->updateUserRole($oldOwner, OrganisationRole::ADMIN->value);

        // Log activity
        $this->logActivity($organisation, 'ownership_transferred', $transferredBy, [
            'old_owner_id' => $oldOwner->id,
            'new_owner_id' => $newOwner->id,
        ]);
    }

    public function getOrganisationForms(Organisation $organisation): Collection
    {
        return $organisation->forms()
            ->with(['user', 'sections'])
            ->withCount('submissions')
            ->latest()
            ->get();
    }

    public function getOrganisationMembers(Organisation $organisation): Collection
    {
        return $organisation->users()
            ->withPivot(['role', 'permissions', 'status', 'invited_at', 'joined_at'])
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->pivot->role,
                    'permissions' => $user->pivot->permissions ? json_decode($user->pivot->permissions, true) : null,
                    'status' => $user->pivot->status,
                    'invited_at' => $user->pivot->invited_at,
                    'joined_at' => $user->pivot->joined_at,
                ];
            });
    }

    protected function logActivity(
        Organisation $organisation,
        string $event,
        ?User $causer,
        array $properties = []
    ): void {
        activity()->log(
            description: $event,
            subject: $organisation,
            causer: $causer,
            event: $event,
            properties: $properties,
            logName: 'organisation'
        );
    }
}