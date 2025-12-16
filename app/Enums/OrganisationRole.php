<?php

namespace App\Enums;

enum OrganisationRole: string
{
    case OWNER = 'owner';
    case ADMIN = 'admin';
    case EDITOR = 'editor';
    case VIEWER = 'viewer';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match($this) {
            self::OWNER => 'Owner',
            self::ADMIN => 'Administrator',
            self::EDITOR => 'Editor',
            self::VIEWER => 'Viewer',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::OWNER => 'Full access to organisation and all forms',
            self::ADMIN => 'Manage organisation, forms, and members',
            self::EDITOR => 'Create and edit forms',
            self::VIEWER => 'View forms and submissions',
        };
    }

    public function permissions(): array
    {
        return match($this) {
            self::OWNER => [
                'organisation.manage',
                'organisation.delete',
                'members.manage',
                'forms.create',
                'forms.edit',
                'forms.delete',
                'forms.publish',
                'submissions.view',
                'submissions.export',
                'templates.create',
                'templates.edit',
                'templates.delete',
            ],
            self::ADMIN => [
                'organisation.manage',
                'members.manage',
                'forms.create',
                'forms.edit',
                'forms.delete',
                'forms.publish',
                'submissions.view',
                'submissions.export',
                'templates.create',
                'templates.edit',
                'templates.delete',
            ],
            self::EDITOR => [
                'forms.create',
                'forms.edit',
                'forms.publish',
                'submissions.view',
                'templates.create',
                'templates.edit',
            ],
            self::VIEWER => [
                'submissions.view',
            ],
        };
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions());
    }
}