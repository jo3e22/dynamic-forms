<?php

namespace App\Http\Controllers\Form;

use Auth;
use App\Models\Form\Form;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FormController extends Controller
{
    public function store(Request $request)
    {
        $current_user = Auth::user();
        $form = new Form([
            'title' => ucfirst($request->title),
            'description' => ucfirst($request->description),
            'status' => Form::STATUS_DRAFT
        ]);
        $form->generateCode();
        $current_user->forms()->save($form);
        return redirect()->route('forms.show', $form->code);
    }
}