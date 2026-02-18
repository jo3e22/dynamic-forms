<?php

namespace App\Models\GDPR;

use Illuminate\Database\Eloquent\Model;

class GDPRDataSubjectAccessRequest extends Model
{
    protected $table = 'gdpr_data_subject_access_requests';

    protected $fillable = [
        'user_id',
        'status',
        'request_type',
        'reason',
        'requested_at',
        'deadline_at',
        'completed_at',
        'response_data',
        'download_token',
        'token_expires_at',
        'rejection_reason',
    ];

    protected $casts = [
        'response_data' => 'array',
        'requested_at' => 'datetime',
        'deadline_at' => 'datetime',
        'completed_at' => 'datetime',
        'token_expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function isExpired(): bool
    {
        return $this->deadline_at && $this->deadline_at->isPast();
    }

    public function isTokenValid(): bool
    {
        return $this->download_token && $this->token_expires_at && $this->token_expires_at->isFuture();
    }
}
