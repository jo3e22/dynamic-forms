<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\FormSharingType;
use App\Enums\FormConfirmationEmail;

class FormSettings extends Model
{
    protected $fillable = [
        'form_id',
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
        'allow_duplicate_responses' => 'boolean',
        'allow_response_editing' => 'boolean',
        'max_submissions' => 'integer',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }
}