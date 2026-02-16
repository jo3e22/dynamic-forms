<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Mail;

use App\Mail\ShareFormLinkMail;
use App\Mail\FormCollaborationMail;
use App\Models\User;
use App\Models\FormSection;
use App\Models\Organisation\Organisation;
use App\Models\Template\Template;
use App\Enums\FormSharingType;

class Form extends Model
{
    use HasFactory, SoftDeletes;

    // These are now "effective" statuses, computed from settings
    const STATUS_DRAFT = 'draft';
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_OPEN = 'open';
    const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'status',
        'user_id',
        'organisation_id',
        'template_id',
        'primary_color',
        'secondary_color',
    ];

    public function getRouteKeyName()
    {
        return 'code';
    }

    // ── Relationships ──────────────────────────────────

    public function user() { return $this->belongsTo(User::class); }
    public function sections() { return $this->hasMany(FormSection::class); }
    public function fields() { return $this->hasMany(FormField::class); }
    public function submissions() { return $this->hasMany(Submission::class); }
    public function settings() { return $this->hasOne(FormSettings::class); }

    public function collaborationUsers()
    {
        return $this->belongsToMany(User::class, 'form_collaborators', 'form_id', 'user_id')
            ->withTimestamps();
    }

    public function organisation(): BelongsTo { return $this->belongsTo(Organisation::class); }
    public function template(): BelongsTo { return $this->belongsTo(Template::class); }

    // ── Accessors ──────────────────────────────────────

    public function getTitleAttribute($value)
    {
        $first = $this->relationLoaded('sections')
            ? $this->sections->sortBy('section_order')->first()
            : $this->sections()->orderBy('section_order')->first();

        return $first?->title ?? $value;
    }

    // ── Settings ───────────────────────────────────────

    public function getOrCreateSettings(): FormSettings
    {
        if (!$this->relationLoaded('settings') || !$this->settings) {
            $this->load('settings');
        }

        if (!$this->settings) {
            $this->settings()->create(FormSettings::defaults());
            $this->load('settings');
        }

        return $this->settings;
    }

    // ── Computed Status ────────────────────────────────

    /**
     * Compute the effective status from settings.
     * This is THE source of truth for whether a form is open.
     *
     * Returns: 'draft' | 'scheduled' | 'open' | 'closed'
     */
    public function computeStatus(): string
    {
        $settings = $this->getOrCreateSettings();
        $now = now();

        // Manual mode
        if ($settings->publish_mode === 'manual') {
            if (!$settings->is_published) {
                return self::STATUS_DRAFT;
            }

            // Published, but check max submissions
            if ($settings->max_submissions !== null
                && $this->submissions()->count() >= $settings->max_submissions) {
                return self::STATUS_CLOSED;
            }

            return self::STATUS_OPEN;
        }

        // Scheduled mode
        if ($settings->publish_mode === 'scheduled') {
            // Not yet started
            if ($settings->open_at && $now->lt($settings->open_at)) {
                return self::STATUS_SCHEDULED;
            }

            // Past close time
            if ($settings->close_at && $now->gt($settings->close_at)) {
                return self::STATUS_CLOSED;
            }

            // Within window (or no dates set = treat as draft)
            if (!$settings->open_at && !$settings->close_at) {
                return self::STATUS_DRAFT;
            }

            // Check max submissions
            if ($settings->max_submissions !== null
                && $this->submissions()->count() >= $settings->max_submissions) {
                return self::STATUS_CLOSED;
            }

            return self::STATUS_OPEN;
        }

        return self::STATUS_DRAFT;
    }

    /**
     * Is the form currently accepting responses?
     */
    public function isAcceptingResponses(): bool
    {
        return $this->computeStatus() === self::STATUS_OPEN;
    }

    /**
     * Get the reason the form is not accepting responses (for display).
     */
    public function closedReason(): string
    {
        $settings = $this->getOrCreateSettings();
        $status = $this->computeStatus();

        return match ($status) {
            self::STATUS_DRAFT => 'This form has not been published yet.',
            self::STATUS_SCHEDULED => 'This form will open on ' . $settings->open_at->format('j M Y, g:ia') . '.',
            self::STATUS_CLOSED => $this->getClosedReason($settings),
            default => 'This form is not accepting responses.',
        };
    }

    private function getClosedReason(FormSettings $settings): string
    {
        if ($settings->max_submissions !== null
            && $this->submissions()->count() >= $settings->max_submissions) {
            return 'This form has reached the maximum number of submissions.';
        }

        if ($settings->close_at && now()->gt($settings->close_at)) {
            return 'This form closed on ' . $settings->close_at->format('j M Y, g:ia') . '.';
        }

        if ($settings->publish_mode === 'manual' && !$settings->is_published) {
            return 'This form has been closed by the owner.';
        }

        return 'This form is no longer accepting responses.';
    }

    // ── Permission Helpers ─────────────────────────────

    public function requiresAuthentication(): bool
    {
        return $this->getOrCreateSettings()->sharing_type === FormSharingType::AUTHENTICATED_ONLY;
    }

    public function isGuestAllowed(): bool
    {
        return in_array($this->getOrCreateSettings()->sharing_type, [
            FormSharingType::GUEST_ALLOWED,
            FormSharingType::GUEST_EMAIL_REQUIRED,
        ]);
    }

    public function isEmailRequired(): bool
    {
        return $this->getOrCreateSettings()->sharing_type === FormSharingType::GUEST_EMAIL_REQUIRED;
    }

    // ── Ownership ──────────────────────────────────────

    public function isOwnedByOrganisation(): bool { return $this->organisation_id !== null; }
    public function isOwnedBy(User $user): bool { return $this->user_id === $user->id; }

    // ── Utility ────────────────────────────────────────

    public function generateCode()
    {
        do {
            $code = (string) Str::uuid();
        } while (self::where('code', $code)->exists());
        $this->code = $code;
    }

    public static function getStatusSymbols()
    {
        return [
            static::STATUS_DRAFT => ['label' => 'Draft', 'color' => 'slate'],
            static::STATUS_SCHEDULED => ['label' => 'Scheduled', 'color' => 'primary'],
            static::STATUS_OPEN => ['label' => 'Open', 'color' => 'success'],
            static::STATUS_CLOSED => ['label' => 'Closed', 'color' => 'pink'],
        ];
    }

    public function shareFormViaMail($email, $data)
    {
        Mail::to($email)->send(new ShareFormLinkMail($this, $data));
    }

    public function addCollaboratorAndSendEmail(User $user, $email_message = '', $is_user_new = false)
    {
        $this->collaborationUsers()->save($user);
        Mail::to($user->email)->send(new FormCollaborationMail($this, $user, $email_message, $is_user_new));
    }
}