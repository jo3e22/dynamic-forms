<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\FormSharingType;
use App\Enums\FormConfirmationEmail;

class FormSettings extends Model
{
    protected $fillable = [
        'form_id',
        'publish_mode',
        'is_published',
        'sharing_type',
        'allow_duplicate_responses',
        'confirmation_email',
        'open_at',
        'close_at',
        'max_submissions',
        'allow_response_editing',
        'confirmation_message',
    ];

    protected $casts = [
        'sharing_type' => FormSharingType::class,
        'confirmation_email' => FormConfirmationEmail::class,
        'open_at' => 'datetime',
        'close_at' => 'datetime',
        'is_published' => 'boolean',
        'allow_duplicate_responses' => 'boolean',
        'allow_response_editing' => 'boolean',
        'max_submissions' => 'integer',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public static function defaults(): array
    {
        return [
            'publish_mode' => 'manual',
            'is_published' => false,
            'sharing_type' => FormSharingType::AUTHENTICATED_ONLY,
            'allow_duplicate_responses' => true,
            'confirmation_email' => FormConfirmationEmail::LINKED_COPY_OF_RESPONSES,
            'allow_response_editing' => true,
            'confirmation_message' => null,
            'open_at' => null,
            'close_at' => null,
            'max_submissions' => null,
        ];
    }
}