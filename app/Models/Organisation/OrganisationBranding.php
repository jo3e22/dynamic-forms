<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\Organisation\Organisation;

class OrganisationBranding extends Model
{
    protected $fillable = [
        'organisation_id',
        'primary_color',
        'secondary_color',
        'accent_color',
        'font_family',
        'logo_url',
        'logo_icon_url',
        'favicon_url',
    ];

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }
}