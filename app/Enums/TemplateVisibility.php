<?php

namespace App\Enums;

enum TemplateVisibility: string
{
    case PRIVATE = 'private';
    case ORGANISATION = 'organisation';
    case PUBLIC = 'public';

    public function label(): string
    {
        return match($this) {
            self::PRIVATE => 'Private',
            self::ORGANISATION => 'Organisation',
            self::PUBLIC => 'Public',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::PRIVATE => 'Only visible to you',
            self::ORGANISATION => 'Visible to organisation members',
            self::PUBLIC => 'Visible to everyone',
        };
    }
}