<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\Submission;
use App\Models\SubmissionField;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class FormApiController extends Controller
{
    /**
     * Create a new form from JSON schema
     * POST /api/forms
     * 
     * Auth: API key (from organisation)
     */
    public function store(Request $request): JsonResponse
    {
        $org = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'schema' => 'required|array',
            'schema.*.name' => 'required|string',
            'schema.*.type' => 'required|string|in:text,email,number,select,checkbox,textarea,date',
            'schema.*.label' => 'required|string',
            'schema.*.required' => 'nullable|boolean',
            'schema.*.options' => 'nullable|array',
        ]);

        // Create form
        $form = Form::create([
            'user_id' => auth()->id(),
            'status' => Form::STATUS_OPEN,
            'source' => 'api',
            'schema' => $validated['schema'],
            'code' => $this->generateFormCode(),
        ]);

        // Create settings with name/description
        $form->settings()->create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        // Generate API key
        $form->generateApiKey();

        return response()->json([
            'success' => true,
            'form' => [
                'id' => $form->id,
                'code' => $form->code,
                'name' => $validated['name'],
                'api_key' => $form->api_key,
                'embed_url' => url("/forms/{$form->code}/viewform"),
                'api_endpoint' => url("/api/forms/{$form->id}/submissions"),
                'schema' => $form->schema,
                'created_at' => $form->created_at,
            ],
        ], 201);
    }

    /**
     * Get form schema and metadata
     * GET /api/forms/{formId}
     * 
     * Auth: None (public forms) or API key
     */
    public function show($formId): JsonResponse
    {
        $form = Form::findOrFail($formId);
        
        // Check if form is public or API key matches
        if (!$this->canAccessForm($form)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'id' => $form->id,
            'code' => $form->code,
            'name' => $form->settings?->name ?? 'Untitled Form',
            'description' => $form->settings?->description,
            'schema' => $form->schema,
            'status' => $form->status,
            'created_at' => $form->created_at,
            'updated_at' => $form->updated_at,
        ]);
    }

    /**
     * Update form schema
     * PATCH /api/forms/{formId}
     * 
     * Auth: API key
     */
    public function update(Request $request, $formId): JsonResponse
    {
        $form = Form::findOrFail($formId);
        
        if (!$this->canModifyForm($form)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'schema' => 'nullable|array',
            'schema.*.name' => 'required_with:schema|string',
            'schema.*.type' => 'required_with:schema|string|in:text,email,number,select,checkbox,textarea,date',
            'schema.*.label' => 'required_with:schema|string',
        ]);

        // Update form
        if (isset($validated['schema'])) {
            $form->update(['schema' => $validated['schema']]);
        }

        // Update settings
        if (isset($validated['name']) || isset($validated['description'])) {
            $form->settings()->updateOrCreate(
                ['form_id' => $form->id],
                [
                    'name' => $validated['name'] ?? $form->settings?->name,
                    'description' => $validated['description'] ?? $form->settings?->description,
                ]
            );
        }

        return response()->json([
            'success' => true,
            'form' => $form->toApiResource(),
        ]);
    }

    /**
     * List submissions for a form
     * GET /api/forms/{formId}/submissions
     * 
     * Query: ?page=1&limit=50&format=json
     * Auth: API key
     */
    public function getSubmissions(Request $request, $formId): JsonResponse
    {
        $form = Form::findOrFail($formId);
        
        if (!$this->canAccessForm($form)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $limit = min($request->query('limit', 50), 100); // Max 100 per page
        $page = $request->query('page', 1);

        $submissions = $form->getSubmissionsForApi($limit, $page);
        
        // Format submissions for API
        $formattedSubmissions = $submissions->map(function ($submission) {
            return [
                'id' => $submission->id,
                'code' => $submission->code,
                'email' => $submission->email,
                'status' => $submission->status,
                'source' => $submission->source,
                'submitted_at' => $submission->created_at,
                'fields' => $submission->meta ? json_decode($submission->meta, true)['api_data'] ?? [] : [],
            ];
        });

        return response()->json([
            'data' => $formattedSubmissions,
            'pagination' => [
                'total' => $submissions->total(),
                'per_page' => $submissions->perPage(),
                'current_page' => $submissions->currentPage(),
                'last_page' => $submissions->lastPage(),
            ],
        ]);
    }

    /**
     * Submit a form via API
     * POST /api/forms/{formId}/submissions
     * 
     * Body: Form field answers as JSON
     * Auth: None (public) or API key
     */
    public function submitForm(Request $request, $formId): JsonResponse
    {
        $form = Form::findOrFail($formId);
        
        // Check if form is open
        if ($form->status !== Form::STATUS_OPEN) {
            return response()->json(['error' => 'Form is not accepting submissions'], 403);
        }

        // Validate form data against schema
        $rules = $this->buildValidationRules($form->schema);
        $validated = $request->validate($rules);

        try {
            // Create submission
            $submission = new Submission();
            $submission->form_id = $form->id;
            $submission->source = 'api';
            $submission->api_client = $request->header('X-API-Client') ?? 'unknown';
            $submission->status = Submission::STATUS_PENDING;
            $submission->email = $validated[$this->getEmailField($form->schema)] ?? null;
            $submission->generateCode();
            $submission->save();

            // Store field responses in a JSON meta field
            // (For API submissions, we store raw data since there are no form_field records)
            $submission->update([
                'meta' => json_encode(['api_data' => $validated])
            ]);

            return response()->json([
                'success' => true,
                'submission' => [
                    'id' => $submission->id,
                    'code' => $submission->code,
                    'submitted_at' => $submission->created_at,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create submission',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a form
     * DELETE /api/forms/{formId}
     * 
     * Auth: API key
     */
    public function destroy($formId): JsonResponse
    {
        $form = Form::findOrFail($formId);
        
        if (!$this->canModifyForm($form)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $form->delete();

        return response()->json([
            'success' => true,
            'message' => 'Form deleted successfully',
        ]);
    }

    // ── Helper Methods ────────────────────────────────

    /**
     * Build validation rules from form schema
     */
    private function buildValidationRules(array $schema): array
    {
        $rules = [];

        foreach ($schema as $field) {
            $rule = [];

            if ($field['required'] ?? false) {
                $rule[] = 'required';
            } else {
                $rule[] = 'nullable';
            }

            switch ($field['type']) {
                case 'email':
                    $rule[] = 'email';
                    break;
                case 'number':
                    $rule[] = 'numeric';
                    break;
                case 'date':
                    $rule[] = 'date';
                    break;
                case 'select':
                    $options = implode(',', $field['options'] ?? []);
                    $rule[] = "in:{$options}";
                    break;
            }

            $rules[$field['name']] = $rule;
        }

        return $rules;
    }

    /**
     * Find email field from schema
     */
    private function getEmailField(array $schema): ?string
    {
        foreach ($schema as $field) {
            if ($field['type'] === 'email') {
                return $field['name'];
            }
        }
        return null;
    }

    /**
     * Check if current user can access form (for reading)
     */
    private function canAccessForm(Form $form): bool
    {
        // Check API key
        $apiKey = $this->getApiKey();
        if ($apiKey && $form->api_key === $apiKey) {
            return true;
        }

        // Check owner
        if (auth()->check() && $form->user_id === auth()->id()) {
            return true;
        }

        // Check if public (you'll implement visibility logic)
        return true;
    }

    /**
     * Check if current user can modify form
     */
    private function canModifyForm(Form $form): bool
    {
        $apiKey = $this->getApiKey();
        if ($apiKey && $form->api_key === $apiKey) {
            return true;
        }

        if (auth()->check() && $form->user_id === auth()->id()) {
            return true;
        }

        return false;
    }

    /**
     * Extract API key from request
     */
    private function getApiKey(): ?string
    {
        // Check Authorization header
        if ($auth = request()->header('Authorization')) {
            if (str_starts_with($auth, 'Bearer ')) {
                return substr($auth, 7);
            }
        }

        // Check query parameter
        return request()->query('api_key');
    }

    /**
     * Generate a unique form code
     */
    private function generateFormCode(): string
    {
        do {
            $code = \Illuminate\Support\Str::uuid()->toString();
        } while (Form::where('code', $code)->exists());

        return $code;
    }
}
