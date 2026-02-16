<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\EmailTemplateController;
use App\Http\Controllers\Organisation\OrganisationController;

// Admin-only routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/organisations', [OrganisationController::class, 'adminIndex'])->name('organisations.index');
    Route::get('/organisations/create', [OrganisationController::class, 'create'])->name('organisations.create');
    Route::post('/organisations', [OrganisationController::class, 'store'])->name('organisations.store');

    Route::get('/email', [EmailTemplateController::class, 'index'])->name('email.index');
    Route::get('/email/{emailTemplate}', [EmailTemplateController::class, 'edit'])->name('email.edit');
    Route::put('/email/{emailTemplate}', [EmailTemplateController::class, 'update'])->name('email.update');
    Route::post('/email/{emailTemplate}/test', [EmailTemplateController::class, 'sendTest'])->name('email.test');
});