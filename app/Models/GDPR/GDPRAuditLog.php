<?php

namespace App\Models\GDPR;

use Illuminate\Database\Eloquent\Model;

class GDPRAuditLog extends Model
{
    const UPDATED_AT = null; // Immutable audit logs

    protected $table = 'gdpr_audit_logs';

    protected $fillable = [
        'action',
        'entity_type',
        'entity_id',
        'user_id',
        'actor_type',
        'reason',
        'data_summary',
        'status',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'data_summary' => 'array',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
