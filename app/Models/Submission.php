<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Str;

class Submission extends Model
{
    use HasFactory;

    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_OPEN = 'open';
    const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'code',
        'form_id',
        'user_id',
        'status',
        'email',
        'guest_name',
        'retention_until',
        'contains_pii',
    ];

    protected $casts = [
        'retention_until' => 'datetime',
        'contains_pii' => 'boolean',
    ];

    public function getRouteKeyName()
    {
        return 'code';
    }

    public function generateCode()
    {
        do {
            $code = (string) Str::uuid();
        } while (self::where('code', $code)->exists());
    
        $this->code = $code;
    }

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function submissionFields()
    {
        return $this->hasMany(SubmissionField::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function hasDuplicateEmail(Form $form, string $email): bool
    {
        return self::where('form_id', $form->id)
            ->where('email', $email)
            ->where('status', '!=', self::STATUS_DRAFT)
            ->exists();
    }

    /**
     * Check if this submission's retention period has expired
     */
    public function isRetentionExpired(): bool
    {
        return $this->retention_until && $this->retention_until->isPast();
    }

    /**
     * Get time remaining before deletion (if any)
     */
    public function daysUntilDeletion(): ?int
    {
        if (!$this->retention_until) {
            return null;
        }
        
        $days = now()->diffInDays($this->retention_until);
        return max(0, $days);
    }
}
