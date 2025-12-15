<?php

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Form;
use App\Models\Submission;
use App\Http\Controllers\Form\FormController;
use App\Http\Controllers\Form\SubmissionController;
use App\Http\Controllers\NotificationController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Forms index (main dashboard)
Route::get('/forms', [FormController::class, 'index'])->middleware(['auth', 'verified'])->name('forms.index');

// Keep dashboard as alias for backwards compatibility
Route::get('dashboard', function () {
    return redirect('/forms');
})->middleware(['auth', 'verified'])->name('dashboard');

// Individual form dashboard
Route::get('/forms/{form}', [FormController::class, 'show'])->name('forms.show');
Route::get('/forms/create', [FormController::class, 'create'])->name('forms.create');
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