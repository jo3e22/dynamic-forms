<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FormApiController;
use App\Http\Controllers\Api\SubmissionApiController;
use App\Http\Controllers\GDPR\GDPRController;

// GDPR & Privacy Routes
Route::post('/gdpr/consent', [GDPRController::class, 'recordConsent']);
Route::get('/profile/gdpr/data', [GDPRController::class, 'getUserData'])->middleware('auth:sanctum');
Route::get('/profile/gdpr/export', [GDPRController::class, 'exportData'])->middleware('auth:sanctum');
Route::delete('/profile/gdpr/delete', [GDPRController::class, 'requestDeletion'])->middleware('auth:sanctum');

// Admin GDPR routes
Route::middleware(['auth:sanctum', 'is_admin'])->prefix('admin/gdpr')->group(function () {
    Route::post('/dsar', [GDPRController::class, 'createDSAR']);
    Route::get('/dsar/pending', [GDPRController::class, 'getPendingDSARs']);
    Route::post('/dsar/{dsar}/complete', [GDPRController::class, 'completeDSAR']);
    Route::get('/compliance-report', [GDPRController::class, 'getComplianceReport']);
});

// Public DSAR download
Route::get('/gdpr/dsar/{dsar}/download', [GDPRController::class, 'downloadDSAR']);

// Route::middleware('auth:sanctum')->group(function () {
//     Route::apiResource('forms', FormApiController::class);
//     Route::get('forms/{form}/submissions', [SubmissionApiController::class, 'index']);
// });