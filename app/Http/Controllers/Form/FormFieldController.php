<?php

namespace App\Http\Controllers\Form;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Form;
use App\Models\FormSection;
use App\Models\FormField;
use App\Models\Submission;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Form\FormController;
use App\Http\Controllers\Form\SubmissionController;
use App\Http\Controllers\Form\SubmissionFieldController;

class FormFieldController extends Controller
{
    public function create(Form $form, FormSection $form_section)
    {
        $form_field = new FormField();
        $form_field->label = 'New Question';
        $form_field->type = 'text';
        $form_field->form()->associate($form);
        $form_section->fields()->save($form_field);

        return $form_field;
    }

}