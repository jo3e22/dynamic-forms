<?php

namespace App\Models\GDPR;

use Illuminate\Database\Eloquent\Model;

class GDPRDataRetentionPolicy extends Model
{
    protected $table = 'gdpr_data_retention_policies';

    protected $fillable = [
        'name',
        'retention_days',
        'description',
        'is_default',
        'applies_from',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'applies_from' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
