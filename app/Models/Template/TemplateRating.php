<?php

namespace App\Models\Template;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\Template\Template;
use App\Models\User;

class TemplateRating extends Model
{
    protected $fillable = [
        'template_id',
        'user_id',
        'rating',
        'review',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function boot()
    {
        parent::boot();

        // Update template average rating when rating is saved
        static::saved(function ($rating) {
            $rating->template->updateAverageRating();
        });

        static::deleted(function ($rating) {
            $rating->template->updateAverageRating();
        });
    }
}