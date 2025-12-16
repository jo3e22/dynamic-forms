<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

use App\Models\Form;
use App\Models\Submissions;
use App\Models\Notification;
use App\Models\Organisation\Organisation;
use App\Models\Template\Template;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'is_admin' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->is_admin;
    }

    public function forms()
    {
        return $this->hasMany(Form::class);
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function unreadNotifications()
    {
        return $this->hasMany(Notification::class)->unread();
    }

    public function organisations(): BelongsToMany
    {
        return $this->belongsToMany(Organisation::class, 'organisation_user')
            ->withPivot(['role', 'permissions', 'status', 'invited_at', 'joined_at', 'invited_by'])
            ->withTimestamps();
    }

    public function ownedOrganisations(): HasMany
    {
        return $this->hasMany(Organisation::class, 'owner_id');
    }

    public function templates(): MorphMany
    {
        return $this->morphMany(Template::class, 'owner');
    }

    public function currentOrganisation(): ?Organisation
    {
        $orgId = session('current_organisation_id');
        return $orgId ? $this->organisations()->find($orgId) : null;
    }

    public function hasPermissionInOrganisation(Organisation $organisation, string $permission): bool
    {
        // Check via OrganisationService
        return app(\App\Services\OrganisationService::class)
            ->userHasPermission($this, $organisation, $permission);
    }
}
