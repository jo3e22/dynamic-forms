<?php

namespace App\Models\GDPR;

use Illuminate\Database\Eloquent\Model;

class GDPRConsentLog extends Model
{
    protected $table = 'gdpr_consent_logs';

    protected $fillable = [
        'user_id',
        'consent_type',
        'given',
        'version',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'given' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
