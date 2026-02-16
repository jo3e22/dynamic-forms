<?php

namespace App\Models\Template;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

use App\Enums\TemplateVisibility;
use App\Models\Form;
use App\Models\Organisation\Organisation;
use App\Models\User;

class Template extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'owner_id',
        'owner_type',
        'visibility',
        'category',
        'data',
        'metadata',
        'thumbnail_url',
        'is_featured',
        'use_count',
        'average_rating',
    ];

    protected $casts = [
        'data' => 'array',
        'metadata' => 'array',
        'is_featured' => 'boolean',
        'visibility' => TemplateVisibility::class,
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($template) {
            if (empty($template->slug)) {
                $template->slug = Str::slug($template->name);
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    // Relationships
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(TemplateCategory::class, 'template_category_template');
    }

    public function usages(): HasMany
    {
        return $this->hasMany(TemplateUsage::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(TemplateRating::class);
    }

    public function forms(): HasMany
    {
        return $this->hasMany(Form::class);
    }

    // Helper methods
    public function incrementUseCount(): void
    {
        $this->increment('use_count');
    }

    public function updateAverageRating(): void
    {
        $this->average_rating = $this->ratings()->avg('rating') ?? 0;
        $this->save();
    }

    public function canBeUsedBy(User $user): bool
    {
        if ($this->visibility === TemplateVisibility::PUBLIC) {
            return true;
        }

        if ($this->visibility === TemplateVisibility::PRIVATE) {
            return $this->isOwnedBy($user);
        }

        // Organisation visibility
        if ($this->owner_type === Organisation::class) {
            return $this->owner->hasUser($user);
        }

        return false;
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->owner_type === User::class && $this->owner_id === $user->id;
    }
}