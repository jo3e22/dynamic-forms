<?php

namespace App\Services;

use App\Models\Form;
use App\Models\FormSection;
use App\Models\User;

class FormService
{
    /**
     * Create a new form with default section
     */
    public function createForm(User $user, ?int $organisationId = null): Form
    {
        $form = new Form();
        $form->generateCode();
        $form->status = Form::STATUS_DRAFT;
        $form->primary_color = '#3B82F6';
        $form->secondary_color = '#EFF6FF';
        $form->organisation_id = $organisationId;
        $user->forms()->save($form);

        // Create first section
        $this->createDefaultSection($form);

        // Create default settings
        $form->settings()->create(\App\Models\FormSettings::defaults());

        return $form;
    }

    /**
     * Create default section for new form
     */
    protected function createDefaultSection(Form $form): FormSection
    {
        $section = new FormSection();
        $section->form_id = $form->id;
        $section->section_order = 0;
        $section->title = 'Untitled Form';
        $section->description = null;
        $section->save();

        return $section;
    }

    /**
     * Update form structure (sections and fields)
     */
    public function updateFormStructure(Form $form, array $data, ?array $colors = null): Form
    {
        // Update colors if provided
        if ($colors) {
            $form->update([
                'primary_color' => $colors['primary'] ?? $form->primary_color,
                'secondary_color' => $colors['secondary'] ?? $form->secondary_color,
            ]);
        }

        $sections = collect($data);
        $existingSectionIds = [];
        $existingFieldIds = [];

        foreach ($sections as $sectionData) {
            // Create or update section
            if (isset($sectionData['id'])) {
                $section = $form->sections()->find($sectionData['id']);
                if ($section) {
                    $section->update([
                        'title' => $sectionData['title'],
                        'description' => $sectionData['description'],
                        'section_order' => $sectionData['section_order'],
                    ]);
                    $existingSectionIds[] = $section->id;
                }
            } else {
                $section = $form->sections()->create([
                    'title' => $sectionData['title'],
                    'description' => $sectionData['description'],
                    'section_order' => $sectionData['section_order'],
                ]);
                $existingSectionIds[] = $section->id;
            }

            // Process fields for this section
            foreach ($sectionData['fields'] as $fieldData) {
                if (isset($fieldData['id'])) {
                    $field = $form->fields()->find($fieldData['id']);
                    if ($field) {
                        $field->update([
                            'section' => $section->id,
                            'label' => $fieldData['label'],
                            'type' => $fieldData['type'],
                            'options' => $fieldData['options'],
                            'required' => $fieldData['required'],
                            'field_order' => $fieldData['field_order'],
                        ]);
                        $existingFieldIds[] = $field->id;
                    }
                } else {
                    $field = $form->fields()->create([
                        'section' => $section->id,
                        'label' => $fieldData['label'],
                        'type' => $fieldData['type'],
                        'options' => $fieldData['options'],
                        'required' => $fieldData['required'],
                        'field_order' => $fieldData['field_order'],
                    ]);
                    $existingFieldIds[] = $field->id;
                }
            }
        }

        // Delete removed sections and fields
        $form->sections()->whereNotIn('id', $existingSectionIds)->delete();
        $form->fields()->whereNotIn('id', $existingFieldIds)->delete();

        return $form->fresh(['sections', 'fields']);
    }

    /**
     * Build form data structure for frontend
     */
    public function buildFormData(Form $form): array
    {
        $form->load([
            'sections' => fn($q) => $q->orderBy('section_order'),
            'fields' => fn($q) => $q->orderBy('field_order'),
        ]);

        $data = [];

        foreach ($form->sections as $section) {
            $sectionFields = $form->fields
                ->where('section', $section->id)
                ->sortBy('field_order')
                ->values();

            $data[] = [
                'id' => $section->id,
                'section_order' => $section->section_order,
                'title' => $section->title,
                'description' => $section->description,
                'fields' => $sectionFields->map(fn($f) => [
                    'id' => $f->id,
                    'label' => $f->label,
                    'type' => $f->type,
                    'options' => $f->options,
                    'required' => (bool) $f->required,
                    'field_order' => $f->field_order,
                ])->all(),
            ];
        }

        return $data;
    }

    /**
     * Get form title from first section
     */
    public function getFormTitle(Form $form): string
    {
        $form->load(['sections' => fn($q) => $q->orderBy('section_order')]);
        return $form->sections->first()?->title ?? 'Form';
    }
}