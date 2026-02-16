<?php
namespace App\Http\Controllers\Form;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormSettings;
use App\Services\FormSettingsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FormSettingsController extends Controller
{
    public function __construct(
        protected FormSettingsService $service
    ) {}

    /**
     * GET /forms/{form}/settings/json
     */
    public function getSettings(Form $form): JsonResponse
    {
        $settings = $form->getOrCreateSettings();

        return response()->json([
            'settings' => $settings,
            'computed_status' => $form->computeStatus(),
            'submissions_count' => $form->submissions()->count(),
        ]);
    }

    /**
     * PUT /forms/{form}/settings
     */
    public function update(Request $request, Form $form)
    {
        $validated = $this->validateSettings($request);
        $this->service->update($form, $validated);

        // Also sync the forms.status column for queries/badges
        $form->refresh();
        $form->update(['status' => $form->computeStatus()]);

        return redirect()->back()->with('success', 'Settings saved!');
    }

    /**
     * POST /forms/{form}/settings
     */
    public function store(Request $request, Form $form)
    {
        $validated = $this->validateSettings($request);
        $this->service->update($form, $validated);

        $form->refresh();
        $form->update(['status' => $form->computeStatus()]);

        return redirect()->back()->with('success', 'Settings saved!');
    }

    protected function validateSettings(Request $request): array
    {
        $validated = $request->validate([
            // Publishing
            'publish_mode' => 'required|string|in:manual,scheduled',
            'is_published' => 'boolean',
            'open_at' => 'nullable|date',
            'close_at' => 'nullable|date|after_or_equal:open_at',
            'max_submissions' => 'nullable|integer|min:1',

            // Sharing
            'sharing_type' => 'required|string|in:authenticated_only,guest_allowed,guest_email_required',

            // Submissions
            'allow_duplicate_responses' => 'boolean',
            'allow_response_editing' => 'boolean',

            // Confirmation
            'confirmation_email' => 'required|string|in:none,confirmation_only,linked_copy_of_responses,detailed_copy_of_responses',
            'confirmation_message' => 'nullable|string',
        ]);

        return array_merge([
            'is_published' => false,
            'allow_duplicate_responses' => false,
            'allow_response_editing' => false,
        ], $validated);
    }
}