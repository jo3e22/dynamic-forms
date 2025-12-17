<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use App\Models\Form;
use App\Models\Organisation\Organisation;
use App\Models\Template\Template;
use App\Policies\FormPolicy;
use App\Policies\OrganisationPolicy;
use App\Policies\TemplatePolicy;
use App\Models\Email\EmailTemplate;
use App\Policies\EmailTemplatePolicy;
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
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Register policies
        Gate::policy(Form::class, FormPolicy::class);
        Gate::policy(Organisation::class, OrganisationPolicy::class);
        Gate::policy(Template::class, TemplatePolicy::class);
        Gate::policy(EmailTemplate::class, EmailTemplatePolicy::class);

        Inertia::share([
            'forms' => fn () => Auth::check() 
                ? Auth::user()->forms()
                    ->with('sections:id,form_id,title,section_order')
                    ->latest()
                    ->get()
                    ->map(fn($form) => [
                        'id' => $form->id,
                        'code' => $form->code,
                        'title' => $form->title,
                    ])
                : [],
            'unreadNotificationsCount' => fn () => Auth::check()
                ? Auth::user()->unreadNotifications()->count()
                : 0,
            'organisations' => fn () => Auth::check()
                ? Auth::user()->organisations()
                    ->with('branding')
                    ->withCount('forms')
                    ->get()
                    ->map(fn($org) => [
                        'id' => $org->id,
                        'name' => $org->name,
                        'slug' => $org->slug,
                        'short_name' => $org->short_name,
                        'forms_count' => $org->forms_count,
                        'role' => $org->pivot->role,
                        'branding' => $org->branding ? [
                            'primary_color' => $org->branding->primary_color,
                            'logo_icon_url' => $org->branding->logo_icon_url,
                        ] : null,
                    ])
                : [],
            'currentOrganisation' => fn () => Auth::check() && session('current_organisation_id')
                ? Organisation::with('branding')->find(session('current_organisation_id'))
                : null,
        ]);
    }
}