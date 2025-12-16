<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

use App\Models\Template\Template;

class TemplateCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'icon',
        'sort_order',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(TemplateCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(TemplateCategory::class, 'parent_id')->orderBy('sort_order');
    }

    public function templates(): BelongsToMany
    {
        return $this->belongsToMany(Template::class, 'template_category_template');
    }
}