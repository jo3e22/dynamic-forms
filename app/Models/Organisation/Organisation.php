<?php

namespace App\Models\Organisation;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

use App\Enums\OrganisationType;
use App\Models\Form;
use App\Models\Template\Template;
use App\Models\User;
use App\Models\Organisation\OrganisationBranding;
use App\Models\Organisation\OrganisationDetail;

class Organisation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'short_name',
        'owner_id',
        'parent_id',
        'type',
        'status',
        'visibility',
        'allow_member_form_creation',
        'require_form_approval',
        'inherit_parent_settings',
        'inherit_parent_branding',
        'settings',
    ];

    protected $casts = [
        'allow_member_form_creation' => 'boolean',
        'require_form_approval' => 'boolean',
        'inherit_parent_settings' => 'boolean',
        'inherit_parent_branding' => 'boolean',
        'settings' => 'array',
        'type' => OrganisationType::class,
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($organisation) {
            if (empty($organisation->slug)) {
                $organisation->slug = Str::slug($organisation->name);
            }
        });

        // Update hierarchy when parent changes
        static::saved(function ($organisation) {
            $organisation->rebuildHierarchy();
        });
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    // Relationships
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Organisation::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Organisation::class, 'parent_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organisation_user')
            ->withPivot(['role', 'permissions', 'status', 'invited_at', 'joined_at', 'invited_by'])
            ->withTimestamps();
    }

    public function forms(): HasMany
    {
        return $this->hasMany(Form::class);
    }

    public function templates(): HasMany
    {
        return $this->hasMany(Template::class, 'owner_id')
            ->where('owner_type', self::class);
    }

    public function branding(): HasOne
    {
        return $this->hasOne(OrganisationBranding::class);
    }

    public function details(): HasOne
    {
        return $this->hasOne(OrganisationDetail::class);
    }

    // Hierarchy relationships
    public function ancestors(): BelongsToMany
    {
        return $this->belongsToMany(
            Organisation::class,
            'organisation_hierarchies',
            'descendant_id',
            'ancestor_id'
        )->withPivot('depth');
    }

    public function descendants(): BelongsToMany
    {
        return $this->belongsToMany(
            Organisation::class,
            'organisation_hierarchies',
            'ancestor_id',
            'descendant_id'
        )->withPivot('depth');
    }

    // Helper methods
    public function addUser(User $user, string $role = 'viewer', ?array $permissions = null, ?int $invitedBy = null): void
    {
        $this->users()->attach($user->id, [
            'role' => $role,
            'permissions' => $permissions ? json_encode($permissions) : null,
            'status' => 'invited',
            'invited_at' => now(),
            'invited_by' => $invitedBy,
        ]);
    }

    public function removeUser(User $user): void
    {
        $this->users()->detach($user->id);
    }

    public function updateUserRole(User $user, string $role): void
    {
        $this->users()->updateExistingPivot($user->id, [
            'role' => $role,
        ]);
    }

    public function updateUserPermissions(User $user, array $permissions): void
    {
        $this->users()->updateExistingPivot($user->id, [
            'permissions' => json_encode($permissions),
        ]);
    }

    public function activateUser(User $user): void
    {
        $this->users()->updateExistingPivot($user->id, [
            'status' => 'active',
            'joined_at' => now(),
        ]);
    }

    public function getUserRole(User $user): ?string
    {
        $pivot = $this->users()->where('user_id', $user->id)->first()?->pivot;
        return $pivot?->role;
    }

    public function getUserPermissions(User $user): ?array
    {
        $pivot = $this->users()->where('user_id', $user->id)->first()?->pivot;
        return $pivot && $pivot->permissions ? json_decode($pivot->permissions, true) : null;
    }

    public function hasUser(User $user): bool
    {
        return $this->users()->where('user_id', $user->id)->exists();
    }

    public function isOwner(User $user): bool
    {
        return $this->owner_id === $user->id;
    }

    // Hierarchy methods
    public function rebuildHierarchy(): void
    {
        // Delete existing hierarchy for this org
        \DB::table('organisation_hierarchies')
            ->where('descendant_id', $this->id)
            ->delete();

        // Add self-reference
        \DB::table('organisation_hierarchies')->insert([
            'ancestor_id' => $this->id,
            'descendant_id' => $this->id,
            'depth' => 0,
        ]);

        // If has parent, copy parent's ancestors and add parent
        if ($this->parent_id) {
            \DB::statement("
                INSERT INTO organisation_hierarchies (ancestor_id, descendant_id, depth)
                SELECT ancestor_id, ?, depth + 1
                FROM organisation_hierarchies
                WHERE descendant_id = ?
            ", [$this->id, $this->parent_id]);
        }

        // Update all descendants
        foreach ($this->children as $child) {
            $child->rebuildHierarchy();
        }
    }

    public function getAncestors()
    {
        return $this->ancestors()->orderBy('depth')->get();
    }

    public function getDescendants()
    {
        return $this->descendants()->orderBy('depth')->get();
    }

    public function getRoot(): ?Organisation
    {
        return $this->ancestors()->where('depth', '>', 0)->orderByDesc('depth')->first();
    }

    // Branding helpers
    public function getBranding(): array
    {
        $branding = $this->branding;

        if (!$branding && $this->inherit_parent_branding && $this->parent) {
            return $this->parent->getBranding();
        }

        return [
            'primary_color' => $branding?->primary_color ?? '#3B82F6',
            'secondary_color' => $branding?->secondary_color ?? '#EFF6FF',
            'accent_color' => $branding?->accent_color ?? '#1E40AF',
            'font_family' => $branding?->font_family ?? null,
            'logo_url' => $branding?->logo_url ?? null,
            'logo_icon_url' => $branding?->logo_icon_url ?? null,
            'favicon_url' => $branding?->favicon_url ?? null,
        ];
    }
}