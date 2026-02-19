<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class FormApiClient
{
    private Client $client;
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('form-api.form_api.url', 'http://127.0.0.1:8002/api');
        $this->apiKey = config('form-api.form_api.key', '');

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 10,
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $this->apiKey,
            ],
        ]);
    }

    /**
     * Get all forms
     */
    public function getForms(array $params = []): array
    {
        try {
            $response = $this->client->get('/forms', ['query' => $params]);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            Log::error('Form API Error (getForms)', [
                'status' => $e->getResponse()?->getStatusCode(),
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get a single form
     */
    public function getForm(string $formId): array
    {
        try {
            $response = $this->client->get("/forms/{$formId}");

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            Log::error('Form API Error (getForm)', [
                'form_id' => $formId,
                'status' => $e->getResponse()?->getStatusCode(),
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Create a new form
     */
    public function createForm(array $data): array
    {
        try {
            $response = $this->client->post('/forms', [
                'json' => $data,
            ]);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            Log::error('Form API Error (createForm)', [
                'status' => $e->getResponse()?->getStatusCode(),
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Update a form
     */
    public function updateForm(string $formId, array $data): array
    {
        try {
            $response = $this->client->patch("/forms/{$formId}", [
                'json' => $data,
            ]);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            Log::error('Form API Error (updateForm)', [
                'form_id' => $formId,
                'status' => $e->getResponse()?->getStatusCode(),
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Delete a form
     */
    public function deleteForm(string $formId): bool
    {
        try {
            $this->client->delete("/forms/{$formId}");

            return true;
        } catch (RequestException $e) {
            Log::error('Form API Error (deleteForm)', [
                'form_id' => $formId,
                'status' => $e->getResponse()?->getStatusCode(),
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Submit a form
     */
    public function submitForm(string $formId, array $data): array
    {
        try {
            $response = $this->client->post("/forms/{$formId}/submissions", [
                'json' => $data,
            ]);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            Log::error('Form API Error (submitForm)', [
                'form_id' => $formId,
                'status' => $e->getResponse()?->getStatusCode(),
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get form submissions
     */
    public function getSubmissions(string $formId, array $params = []): array
    {
        try {
            $response = $this->client->get("/forms/{$formId}/submissions", [
                'query' => $params,
            ]);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            Log::error('Form API Error (getSubmissions)', [
                'form_id' => $formId,
                'status' => $e->getResponse()?->getStatusCode(),
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
