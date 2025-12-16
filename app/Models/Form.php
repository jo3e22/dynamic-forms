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

class Form extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sections()
    {
        return $this->hasMany(FormSection::class);
    }

    public function fields()
    {
        return $this->hasMany(FormField::class);
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    public function getTitleAttribute($value)
    {
        // Use loaded relation if present to avoid extra queries
        $first = $this->relationLoaded('sections')
            ? $this->sections->sortBy('section_order')->first()
            : $this->sections()->orderBy('section_order')->first();

        return $first?->title ?? $value;
    }

    public function collaborationUsers()
    {
        return $this->belongsToMany(User::class, 'form_collaborators', 'form_id', 'user_id')
            ->withTimestamps();
    }

    public function generateCode()
    {
        do {
            $code = (string) Str::uuid();
        } while (self::where('code', $code)->exists());
    
        $this->code = $code;
    }

    public function shareFormViaMail($email, $data)
    {
        $message = new ShareFormLinkMail($this, $data);
        Mail::to($email)->send($message);
    }

    public function addCollaboratorAndSendEmail(User $user, $email_message = '', $is_user_new = false)
    {
        $this->collaborationUsers()->save($user);

        $message = new FormCollaborationMail($this, $user, $email_message, $is_user_new);
        Mail::to($user->email)->send($message);
    }

    public static function getStatusSymbols()
    {
        return [
            static::STATUS_DRAFT => ['label' => 'Draft', 'color' => 'slate'],
            static::STATUS_PENDING => ['label' => 'Ready to Open', 'color' => 'primary'],
            static::STATUS_OPEN => ['label' => 'Open', 'color' => 'success'],
            static::STATUS_CLOSED => ['label' => 'Closed', 'color' => 'pink'],
        ];
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function isOwnedByOrganisation(): bool
    {
        return $this->organisation_id !== null;
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->user_id === $user->id;
    }
}

