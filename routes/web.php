<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Form;
use App\Models\FormSubmission;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');



Route::get('/form/{id}', function ($id) {
    $form = Form::findOrFail($id);

    return Inertia::render('DynamicForm', [
        'formId' => $form->id,
        'questions' => $form->schema,
        'mode' => 'fill'
    ]);
});

Route::get('/form-edit', function () {
    $formSchema = [
        ['type' => 'text', 'label' => 'Your name', 'required' => true],
        ['type' => 'select', 'label' => 'Favourite colour', 'options' => ['Red', 'Blue', 'Green']]
    ];

    return Inertia::render('DynamicForm', [
        'questions' => $formSchema,
        'mode' => 'edit'
    ]);
});

Route::get('/form-preview', function () {
    $formSchema = [
        ['type' => 'text', 'label' => 'Your name', 'required' => true],
        ['type' => 'select', 'label' => 'Favourite colour', 'options' => ['Red', 'Blue', 'Green']]
    ];

    return Inertia::render('DynamicForm', [
        'questions' => $formSchema,
        'mode' => 'preview'
    ]);
});

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

Route::get('/forms/create', function () {
    return Inertia::render('FormBuilder');
});

Route::post('/forms', [FormController::class, 'store'])->name('forms.store');

    $form=Form::create([
        'title' => $request->title,
        'schema' => $request->schema
    ]);

    return redirect("/form/{$form->id}");
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
