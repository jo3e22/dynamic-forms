<?php

namespace App\Services;

use App\Models\Form;
use App\Services\FormApiClient;
use Illuminate\Support\Facades\Log;

/**
 * Bridges dynamic-forms database with Form-API
 * 
 * - Local DB: Form model tracks user/org ownership, metadata
 * - Form-API: Handles form structure (sections, elements)
 */
class FormApiIntegrationService
{
    public function __construct(
        protected FormApiClient $apiClient
    ) {}

    /**
     * Create a form in both local DB and Form-API
     */
    public function createForm(Form $localForm, array $structure = []): array
    {
        try {
            // Create in Form-API
            $apiResponse = $this->apiClient->createForm($structure);

            // Link to local form via code
            $localForm->update([
                'code' => $apiResponse['code'] ?? null,
                'api_form_id' => $apiResponse['id'] ?? null,
            ]);

            return $apiResponse;
        } catch (\Exception $e) {
            Log::error('Failed to create form in API', [
                'local_form_id' => $localForm->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get form structure from API
     */
    public function getForm(Form $localForm): array
    {
        try {
            // Get from API using stored code or ID
            $formId = $localForm->api_form_id ?? $localForm->code;

            if (!$formId) {
                return [
                    'id' => $localForm->id,
                    'code' => $localForm->code,
                    'sections' => [],
                    'submission_count' => $localForm->submissions_count,
                ];
            }

            return $this->apiClient->getForm($formId);
        } catch (\Exception $e) {
            Log::error('Failed to get form from API', [
                'local_form_id' => $localForm->id,
                'error' => $e->getMessage(),
            ]);

            // Return local structure as fallback
            return [
                'id' => $localForm->id,
                'code' => $localForm->code,
                'sections' => $localForm->sections,
            ];
        }
    }

    /**
     * Update form structure in API
     */
    public function updateForm(Form $localForm, array $structure): array
    {
        try {
            $formId = $localForm->api_form_id ?? $localForm->code;

            if (!$formId) {
                throw new \Exception('Form not linked to API');
            }

            return $this->apiClient->updateForm($formId, $structure);
        } catch (\Exception $e) {
            Log::error('Failed to update form in API', [
                'local_form_id' => $localForm->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Delete form from both local DB and API
     */
    public function deleteForm(Form $localForm): bool
    {
        try {
            $formId = $localForm->api_form_id ?? $localForm->code;

            if ($formId) {
                $this->apiClient->deleteForm($formId);
            }

            // Local delete handled separately
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete form from API', [
                'local_form_id' => $localForm->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Submit a form response to API
     */
    public function submitForm(Form $localForm, array $data): array
    {
        try {
            $formId = $localForm->api_form_id ?? $localForm->code;

            if (!$formId) {
                throw new \Exception('Form not linked to API');
            }

            return $this->apiClient->submitForm($formId, $data);
        } catch (\Exception $e) {
            Log::error('Failed to submit form to API', [
                'local_form_id' => $localForm->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get submissions for a form
     */
    public function getSubmissions(Form $localForm, array $params = []): array
    {
        try {
            $formId = $localForm->api_form_id ?? $localForm->code;

            if (!$formId) {
                return [
                    'data' => [],
                    'pagination' => ['total' => 0],
                ];
            }

            return $this->apiClient->getSubmissions($formId, $params);
        } catch (\Exception $e) {
            Log::error('Failed to get submissions from API', [
                'local_form_id' => $localForm->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'data' => [],
                'pagination' => ['total' => 0],
            ];
        }
    }
}
