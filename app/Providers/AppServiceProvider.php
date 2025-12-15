<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Form;
use App\Policies\FormPolicy;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Form::class, FormPolicy::class);

        Inertia::share([
            'forms' => fn () => Auth::check() 
                ? Auth::user()->forms()
                    ->with('sections:id,form_id,title,section_order')
                    ->latest()
                    ->get()
                    ->map(fn($form) => [
                        'id' => $form->id,
                        'code' => $form->code,
                        'title' => $form->title, // Uses the accessor
                    ])
                : [],
        ]);
    }
}
