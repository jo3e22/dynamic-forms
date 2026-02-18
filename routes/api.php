<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FormApiController;
use App\Http\Controllers\Api\SubmissionApiController;

// ── Form API Routes (Public + Authenticated) ──────────────────────────────
// Note: API routes don't use implicit binding - {form} is the form ID

// Create form from JSON schema (requires auth)
Route::post('/forms', [FormApiController::class, 'store'])->middleware('auth:sanctum');

// Get form schema (public or with API key)
Route::get('/forms/{formId}', [FormApiController::class, 'show']);

// Update form schema (requires API key or owner)
Route::patch('/forms/{formId}', [FormApiController::class, 'update']);

// Get submissions for a form (requires API key or owner)
Route::get('/forms/{formId}/submissions', [FormApiController::class, 'getSubmissions']);

// Submit form via API (public)
Route::post('/forms/{formId}/submissions', [FormApiController::class, 'submitForm']);

// Delete form (requires API key or owner)
Route::delete('/forms/{formId}', [FormApiController::class, 'destroy']);

// ── GDPR Routes ────────────────────────────────────────────────────────
// Note: GDPR infrastructure will be added in a future phase
// Route::post('/gdpr/consent', [GDPRController::class, 'recordConsent']);
// Route::get('/profile/gdpr/data', [GDPRController::class, 'getUserData'])->middleware('auth:sanctum');
// Route::get('/profile/gdpr/export', [GDPRController::class, 'exportData'])->middleware('auth:sanctum');
// Route::delete('/profile/gdpr/delete', [GDPRController::class, 'requestDeletion'])->middleware('auth:sanctum');

// Admin GDPR routes
// Route::middleware(['auth:sanctum', 'is_admin'])->prefix('admin/gdpr')->group(function () {
//     Route::post('/dsar', [GDPRController::class, 'createDSAR']);
//     Route::get('/dsar/pending', [GDPRController::class, 'getPendingDSARs']);
//     Route::post('/dsar/{dsar}/complete', [GDPRController::class, 'completeDSAR']);
//     Route::get('/compliance-report', [GDPRController::class, 'getComplianceReport']);
// });

// Public DSAR download
// Route::get('/gdpr/dsar/{dsar}/download', [GDPRController::class, 'downloadDSAR']);
