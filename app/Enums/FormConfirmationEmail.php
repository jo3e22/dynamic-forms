<?php

namespace App\Enums;

enum FormConfirmationEmail: string
{
    case NONE = 'none';
    case CONFIRMATION_ONLY = 'confirmation_only';
    case LINKED_COPY_OF_RESPONSES = 'linked_copy_of_responses';
    case DETAILED_COPY_OF_RESPONSES = 'detailed_copy_of_responses';

    public function label(): string
    {
        return match($this) {
            self::NONE => 'None',
            self::CONFIRMATION_ONLY => 'Confirmation Only',
            self::LINKED_COPY_OF_RESPONSES => 'Linked Copy of Responses',
            self::DETAILED_COPY_OF_RESPONSES => 'Detailed Copy of Responses',
        };
    }
    
    public function description(): string
    {
        return match($this) {
            self::NONE => 'No confirmation email will be sent',
            self::CONFIRMATION_ONLY => 'Send a confirmation email only',
            self::LINKED_COPY_OF_RESPONSES => 'Send a confirmation email with a link to the responses',
            self::DETAILED_COPY_OF_RESPONSES => 'Send a confirmation email with a detailed copy of the responses',
        };
    }

    public static function options(): array
    {
        return [
            self::NONE->value => self::NONE->label(),
            self::CONFIRMATION_ONLY->value => self::CONFIRMATION_ONLY->label(),
            self::LINKED_COPY_OF_RESPONSES->value => self::LINKED_COPY_OF_RESPONSES->label(),
            self::DETAILED_COPY_OF_RESPONSES->value => self::DETAILED_COPY_OF_RESPONSES->label(),
        ];
    }

    public static function default(): self
    {
        return self::LINKED_COPY_OF_RESPONSES;
    }

    public static function fromValue(string $value): ?self
    {
        return match($value) {
            'none' => self::NONE,
            'confirmation_only' => self::CONFIRMATION_ONLY,
            'linked_copy_of_responses' => self::LINKED_COPY_OF_RESPONSES,
            'detailed_copy_of_responses' => self::DETAILED_COPY_OF_RESPONSES,
            default => null,
        };
    }
}