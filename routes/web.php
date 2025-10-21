<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Http\Controllers\Form\FormController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');



Route::get('/forms', [FormController::class, 'index'])->name('forms.index');
Route::get('/forms/create', [FormController::class, 'create'])->name('forms.create');
Route::get('/forms/{form}/edit', function (Form $form) {
    return Inertia::render('forms/FormBuilder', [
        'formId' => $form->id,
        'questions' => $form->fields,
        'mode' => 'edit'
    ]);
})->name('forms.edit');


Route::post('/form/{id}/submit', function (Request $request, $id) {
    $request->validate([
        'answers' => 'required|array'
    ]);

    FormSubmission::create([
        'form_id' => $id,
        'answers' => $request->input('answers')
    ]);

    return redirect('/thank-you');
});





Route::get('/forms/{form}', function (Form $form) {
    return Inertia::render('DynamicForm', [
        'formId' => $form->id,
        'questions' => $form->fields,
        'mode' => 'fill'
    ]);
})->name('forms.show');



require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
