<?php

namespace App\Models\Email;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Organisation\Organisation;

class EmailTemplate extends Model
{
    protected $fillable = [
        'key',
        'event',
        'name',
        'subject',
        'body',
        'type',
        'organisation_id',
        'enabled',
        'locale',
        'variables',
        'is_default',
    ];

    protected $casts = [
        'variables' => 'array',
        'enabled' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public static function getForEvent(string $event, ?int $organisationId = null, string $locale = 'en'): ?self
    {
        // Try org-specific override first
        if ($organisationId) {
            $template = self::where('event', $event)
                ->where('organisation_id', $organisationId)
                ->where('locale', $locale)
                ->where('enabled', true)
                ->first();

            if ($template) {
                return $template;
            }
        }

        // Fall back to system default
        return self::where('event', $event)
            ->whereNull('organisation_id')
            ->where('locale', $locale)
            ->where('enabled', true)
            ->first();
    }

    public function hydrateVariables(array $variables): self
    {
        $clone = clone $this;
        
        $clone->subject = $this->replaceVariables($clone->subject, $variables);
        $clone->body = $this->replaceVariables($clone->body, $variables);
        
        return $clone;
    }

    private function replaceVariables(string $content, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $content = str_replace("{{$key}}", (string) $value, $content);
        }
        return $content;
    }
}