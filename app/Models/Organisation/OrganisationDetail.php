<?php

namespace App\Models\Organisation;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\Organisation\Organisation;

class OrganisationDetail extends Model
{
    protected $fillable = [
        'organisation_id',
        'description',
        'tagline',
        'website',
        'email',
        'phone',
        'address_line1',
        'address_line2',
        'city',
        'county',
        'postcode',
        'country',
        'facebook',
        'twitter',
        'linkedin',
        'instagram',
        'registration_number',
        'established_date',
    ];

    protected $casts = [
        'established_date' => 'date',
    ];

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address_line1,
            $this->address_line2,
            $this->city,
            $this->county,
            $this->postcode,
            $this->country,
        ]);

        return implode(', ', $parts);
    }
}