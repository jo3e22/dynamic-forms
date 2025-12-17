<?php
namespace App\Services;

use App\Models\Form;
use App\Models\FormSettings;

class FormSettingsService
{
    public function store(Form $form, array $data): FormSettings
    {
        return $form->settings()->create($data);
    }

    public function update(Form $form, array $data): FormSettings
    {
        $settings = $form->settings;
        if ($settings) {
            $settings->update($data);
            return $settings;
        }
        return $this->store($form, $data);
    }
}