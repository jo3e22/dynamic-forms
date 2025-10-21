<?php

namespace App\Http\Controllers\Form;

use Auth;
use Inertia\Inertia;
use App\Models\Form;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FormController extends Controller
{
    public function index()
    {
        $forms = Form::select('id', 'title', 'status')->latest()->get();

        return Inertia::render('forms/Index', [
            'forms' => $forms
        ]);
    }

    public function create()
    {
        $current_user = Auth::user();

        $form = new Form();
        $form->generateCode();
        $form->status = Form::STATUS_DRAFT;
        $current_user->forms()->save($form);

        return redirect()->route('forms.edit', $form);
    }

    public function store(request $request)
    {
        $current_user = Auth::user();

        foreach ($request->fields as $field) {
            $form->fields()->create([
                'label' => $field['label'],
                'type' => $field['type'],
                'options' => $field['options'] ?? null,
                'required' => $field['required'] ?? false,
                'uuid' => (string) Str::uuid(),
            ]);
        }
    }

}