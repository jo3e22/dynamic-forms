<?php

use Illuminate\Support\Facades\Route;

// Admin-only routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/organisations', [\App\Http\Controllers\Organisation\OrganisationController::class, 'adminIndex'])->name('organisations.index');
    Route::get('/organisations/create', [\App\Http\Controllers\Organisation\OrganisationController::class, 'create'])->name('organisations.create');
    Route::post('/organisations', [\App\Http\Controllers\Organisation\OrganisationController::class, 'store'])->name('organisations.store');
});