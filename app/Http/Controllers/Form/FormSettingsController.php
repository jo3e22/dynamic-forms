<?php
namespace App\Http\Controllers\Form;

use App\Http\Controllers\Controller;

use App\Models\Form;
use App\Models\FormSettings;
use App\Services\FormSettingsService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FormSettingsController extends Controller
{
    protected FormSettingsService $service;

    public function __construct(FormSettingsService $service)
    {
        $this->service = $service;
    }

    public function show(Form $form)
    {
        $form->load('settings');
        if (!$form->settings) {
            // Option 1: Create default settings
            $form->settings()->create([
                'sharing_type' => 'authenticated_only',
                'allow_duplicate_responses' => true,
                'confirmation_email' => 'linked_copy_of_responses',
                // ...other defaults...
            ]);
            $form->refresh();
        }

        return Inertia::render('forms/FormSettingsPage', [
            'form' => $form,
            'settings' => $form->settings,
        ]);
    }

    // Store settings for a form
    public function store(Request $request, Form $form)
    {
        $validated = $this->validateSettings($request);
        $settings = $this->service->store($form, $validated);
        
        return redirect()->back()->with('success', 'Settings updated!');
    }

    public function update(Request $request, Form $form)
    {
        $this->authorize('update', $form); // Only allow if user can update the form

        $validated = $this->validateSettings($request);
        $settings = $this->service->update($form, $validated);
        return response()->json($settings);
    }

    protected function validateSettings(Request $request): array
    {
        return $request->validate([
            'sharing_type' => 'required|string|in:authenticated_only,guest_allowed,guest_email_required',
            'allow_duplicate_responses' => 'boolean',
            'confirmation_email' => 'required|string|in:none,confirmation_only,linked_copy_of_responses,detailed_copy_of_responses',
            'open_at' => 'nullable|date',
            'close_at' => 'nullable|date|after_or_equal:open_at',
            'max_submissions' => 'nullable|integer|min:1',
            'allow_response_editing' => 'boolean',
            'confirmation_message' => 'nullable|string',
        ]);
    }
}