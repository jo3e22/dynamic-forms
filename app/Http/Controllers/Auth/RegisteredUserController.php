<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\GDPRService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function __construct(
        protected GDPRService $gdprService
    ) {}

    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'gdpr_consent' => 'required|boolean',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'consent_gdpr_at' => now(),
        ]);

        // If marketing consent given
        if ($request->boolean('marketing_consent')) {
            $user->update(['consent_marketing_at' => now()]);
            
            // Record marketing consent
            $this->gdprService->recordConsent(
                $user,
                'marketing_email',
                true,
                '1.0'
            );
        }

        // Record GDPR consent
        $this->gdprService->recordConsent(
            $user,
            'gdpr',
            true,
            '1.0'
        );

        // Audit log
        $this->gdprService->auditLog(
            'user_created',
            'User',
            $user->id,
            $user->id,
            'user',
            'New user registration'
        );

        event(new Registered($user));

        Auth::login($user);

        $request->session()->regenerate();

        return to_route('dashboard');
    }
}

