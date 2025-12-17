<?php

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/admin.php';

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Form;
use App\Models\Submission;
use App\Http\Controllers\Form\FormController;
use App\Http\Controllers\Form\SubmissionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Organisation\OrganisationController;
use App\Http\Controllers\Organisation\OrganisationMemberController;
use App\Http\Controllers\Form\FormSettingsController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Forms index (main dashboard)
Route::get('/forms', [FormController::class, 'index'])->middleware(['auth', 'verified'])->name('forms.index');

// Keep dashboard as alias for backwards compatibility
Route::get('dashboard', function () {
    return redirect('/forms');
})->middleware(['auth', 'verified'])->name('dashboard');

// IMPORTANT: Specific routes must come BEFORE dynamic routes
Route::get('/forms/create', [FormController::class, 'create'])->name('forms.create');

Route::prefix('forms/{form}/settings')->group(function () {
    Route::get('/', [FormSettingsController::class, 'show']);
    Route::post('/', [FormSettingsController::class, 'store']);
    Route::put('/', [FormSettingsController::class, 'update']);
});

// Individual form routes (dynamic {form} parameter)
Route::get('/forms/{form}', [FormController::class, 'show'])->name('forms.show');
Route::get('/forms/{form}/edit', [FormController::class, 'edit'])->name('forms.edit');
Route::put('/forms/{form}/edit', [FormController::class, 'update'])->name('forms.update');
Route::delete('/forms/{form}', [FormController::class, 'destroy'])->name('forms.destroy');

// Form viewing and submission
Route::get('/forms/{form}/viewform', [FormController::class, 'viewform'])->name('forms.viewform');
Route::put('/forms/{form}/viewform/{submission}', [SubmissionController::class, 'submit'])->name('forms.createsubmission');

// Submission viewing
Route::get('/forms/{form}/submissions', [SubmissionController::class, 'index'])->name('submissions.index');
Route::get('/forms/{form}/submissions/{submission}', [SubmissionController::class, 'show'])->name('submissions.show');

// JSON export (for debugging)
Route::get('/forms/{form}/formjson', [FormController::class, 'jsonform'])->name('forms.json');


// Notifications
Route::get('/notifications', [NotificationController::class, 'index'])
    ->middleware(['auth'])
    ->name('notifications.index');
Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])
    ->middleware(['auth'])
    ->name('notifications.read');
Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])
    ->middleware(['auth'])
    ->name('notifications.markAllRead');



// Regular user routes
Route::middleware(['auth'])->group(function () {
    Route::get('/organisations', [\App\Http\Controllers\Organisation\OrganisationController::class, 'index'])->name('organisations.index');
    
    // These specific routes MUST come before the dynamic {organisation} route
    Route::post('/organisations/clear', [\App\Http\Controllers\Organisation\OrganisationController::class, 'clearCurrent'])->name('organisations.clear');
    Route::post('/organisations/{organisation}/switch', [\App\Http\Controllers\Organisation\OrganisationController::class, 'switchCurrent'])->name('organisations.switch');
    
    Route::get('/organisations/{organisation}', [\App\Http\Controllers\Organisation\OrganisationController::class, 'show'])->name('organisations.show');
    Route::get('/organisations/{organisation}/edit', [\App\Http\Controllers\Organisation\OrganisationController::class, 'edit'])->name('organisations.edit');
    Route::put('/organisations/{organisation}', [\App\Http\Controllers\Organisation\OrganisationController::class, 'update'])->name('organisations.update');
    Route::delete('/organisations/{organisation}', [\App\Http\Controllers\Organisation\OrganisationController::class, 'destroy'])->name('organisations.destroy');
    
    // Member management (org owners/admins)
    Route::post('/organisations/{organisation}/members', [\App\Http\Controllers\Organisation\OrganisationMemberController::class, 'store'])->name('organisations.members.store');
    Route::put('/organisations/{organisation}/members/{user}', [\App\Http\Controllers\Organisation\OrganisationMemberController::class, 'update'])->name('organisations.members.update');
    Route::delete('/organisations/{organisation}/members/{user}', [\App\Http\Controllers\Organisation\OrganisationMemberController::class, 'destroy'])->name('organisations.members.destroy');
});
