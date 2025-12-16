<?php

namespace App\Services;

use App\Models\User;
use App\Models\Organisation\Organisation;
use App\Models\Template\Template;
use App\Models\Form;
use App\Enums\TemplateVisibility;
use Illuminate\Support\Collection;

class TemplateService
{
    public function createTemplate(array $data, $owner): Template
    {
        $template = Template::create([
            'name' => $data['name'],
            'slug' => $data['slug'] ?? null,
            'description' => $data['description'] ?? null,
            'owner_id' => $owner->id,
            'owner_type' => get_class($owner),
            'visibility' => $data['visibility'] ?? TemplateVisibility::PRIVATE->value,
            'category' => $data['category'] ?? 'form',
            'data' => $data['data'],
            'metadata' => $data['metadata'] ?? null,
            'thumbnail_url' => $data['thumbnail_url'] ?? null,
        ]);

        // Attach categories
        if (isset($data['category_ids'])) {
            $template->categories()->attach($data['category_ids']);
        }

        return $template->fresh(['categories', 'owner']);
    }

    public function updateTemplate(Template $template, array $data): Template
    {
        $template->update([
            'name' => $data['name'] ?? $template->name,
            'slug' => $data['slug'] ?? $template->slug,
            'description' => $data['description'] ?? $template->description,
            'visibility' => $data['visibility'] ?? $template->visibility,
            'category' => $data['category'] ?? $template->category,
            'data' => $data['data'] ?? $template->data,
            'metadata' => $data['metadata'] ?? $template->metadata,
            'thumbnail_url' => $data['thumbnail_url'] ?? $template->thumbnail_url,
            'is_featured' => $data['is_featured'] ?? $template->is_featured,
        ]);

        // Sync categories
        if (isset($data['category_ids'])) {
            $template->categories()->sync($data['category_ids']);
        }

        return $template->fresh(['categories', 'owner']);
    }

    public function getUserTemplates(User $user): Collection
    {
        return Template::where('owner_type', User::class)
            ->where('owner_id', $user->id)
            ->with('categories')
            ->latest()
            ->get();
    }

    public function getOrganisationTemplates(Organisation $organisation): Collection
    {
        return Template::where('owner_type', Organisation::class)
            ->where('owner_id', $organisation->id)
            ->with('categories')
            ->latest()
            ->get();
    }

    public function getPublicTemplates(): Collection
    {
        return Template::where('visibility', TemplateVisibility::PUBLIC)
            ->with(['categories', 'owner', 'ratings'])
            ->orderByDesc('is_featured')
            ->orderByDesc('average_rating')
            ->orderByDesc('use_count')
            ->get();
    }

    public function getAvailableTemplates(User $user): Collection
    {
        // Get public templates
        $publicTemplates = $this->getPublicTemplates();

        // Get user's own templates
        $userTemplates = $this->getUserTemplates($user);

        // Get templates from user's organisations
        $orgTemplates = collect();
        foreach ($user->organisations as $organisation) {
            $templates = Template::where('owner_type', Organisation::class)
                ->where('owner_id', $organisation->id)
                ->whereIn('visibility', [TemplateVisibility::ORGANISATION, TemplateVisibility::PUBLIC])
                ->with('categories')
                ->get();
            
            $orgTemplates = $orgTemplates->merge($templates);
        }

        return $publicTemplates
            ->merge($userTemplates)
            ->merge($orgTemplates)
            ->unique('id')
            ->sortByDesc('is_featured')
            ->sortByDesc('average_rating')
            ->values();
    }

    public function applyTemplateToForm(Template $template, Form $form, User $user): void
    {
        // Track usage
        $template->usages()->create([
            'form_id' => $form->id,
            'user_id' => $user->id,
            'used_at' => now(),
        ]);

        $template->incrementUseCount();

        // Copy template data to form
        $this->copyTemplateDataToForm($template, $form);
    }

    protected function copyTemplateDataToForm(Template $template, Form $form): void
    {
        $data = $template->data;

        // Delete existing sections and fields
        $form->sections()->each(function ($section) {
            $section->fields()->delete();
            $section->delete();
        });

        // Create sections from template
        foreach ($data as $sectionData) {
            $section = $form->sections()->create([
                'title' => $sectionData['title'] ?? 'Untitled Section',
                'description' => $sectionData['description'] ?? null,
                'section_order' => $sectionData['section_order'] ?? 0,
            ]);

            // Create fields from template
            foreach ($sectionData['fields'] ?? [] as $fieldData) {
                $section->fields()->create([
                    'form_id' => $form->id,
                    'label' => $fieldData['label'] ?? 'Untitled Field',
                    'type' => $fieldData['type'] ?? 'text',
                    'options' => $fieldData['options'] ?? null,
                    'required' => $fieldData['required'] ?? false,
                    'field_order' => $fieldData['field_order'] ?? 0,
                ]);
            }
        }
    }

    public function rateTemplate(Template $template, User $user, int $rating, ?string $review = null): void
    {
        $template->ratings()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'rating' => $rating,
                'review' => $review,
            ]
        );
    }

    public function searchTemplates(string $query, ?User $user = null): Collection
    {
        $templates = Template::where('visibility', TemplateVisibility::PUBLIC)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->with(['categories', 'owner'])
            ->get();

        if ($user) {
            // Also search user's private templates
            $userTemplates = Template::where('owner_type', User::class)
                ->where('owner_id', $user->id)
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('description', 'like', "%{$query}%");
                })
                ->with('categories')
                ->get();

            $templates = $templates->merge($userTemplates)->unique('id');
        }

        return $templates;
    }
}