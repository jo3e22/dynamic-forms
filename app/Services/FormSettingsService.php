<?php
namespace App\Services;

use App\Models\Form;
use App\Models\FormSettings;

class FormSettingsService
{
    /**
     * Update settings for a form, creating them if they don't exist.
     */
    public function update(Form $form, array $data): FormSettings
    {
        $settings = $form->settings;

        if ($settings) {
            $settings->update($data);
            return $settings->fresh();
        }

        return $form->settings()->create(
            array_merge(FormSettings::defaults(), $data)
        );
    }
}