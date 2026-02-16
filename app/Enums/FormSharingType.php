<?php

namespace App\Enums;

enum FormSharingType: string
{
    case AUTHENTICATED_ONLY = 'authenticated_only';
    case GUEST_ALLOWED = 'guest_allowed';
    case GUEST_EMAIL_REQUIRED = 'guest_email_required';

    public function label(): string
    {
        return match($this) {
            self::AUTHENTICATED_ONLY => 'Authenticated Only',
            self::GUEST_ALLOWED => 'Guest Allowed',
            self::GUEST_EMAIL_REQUIRED => 'Guest Email Required',
        };
    }
    
    public function description(): string
    {
        return match($this) {
            self::AUTHENTICATED_ONLY => 'Must be logged in to respond to the form',
            self::GUEST_ALLOWED => 'Anyone can respond to the form',
            self::GUEST_EMAIL_REQUIRED => 'Guests must provide an email address to respond to the form',
        };
    }

    public static function options(): array
    {
        return [
            self::AUTHENTICATED_ONLY->value => self::AUTHENTICATED_ONLY->label(),
            self::GUEST_ALLOWED->value => self::GUEST_ALLOWED->label(),
            self::GUEST_EMAIL_REQUIRED->value => self::GUEST_EMAIL_REQUIRED->label(),
        ];
    }

    public static function default(): self
    {
        return self::AUTHENTICATED_ONLY;
    }

    public static function fromValue(string $value): ?self
    {
        return match($value) {
            'authenticated_only' => self::AUTHENTICATED_ONLY,
            'guest_allowed' => self::GUEST_ALLOWED,
            'guest_email_required' => self::GUEST_EMAIL_REQUIRED,
            default => null,
        };
    }
}