<?php

namespace App\Http\Controllers;

use App\Models\Organisation\Organisation;
use App\Models\User;
use App\Services\OrganisationService;
use App\Enums\OrganisationRole;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class OrganisationMemberController extends Controller
{
    public function __construct(
        protected OrganisationService $organisationService
    ) {}

    public function store(Request $request, Organisation $organisation): RedirectResponse
    {
        $this->authorize('manageMembers', $organisation);

        $validated = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'role' => ['required', 'string', 'in:' . implode(',', OrganisationRole::values())],
            'permissions' => ['nullable', 'array'],
        ]);

        try {
            $this->organisationService->inviteMember(
                $organisation,
                $validated['email'],
                $validated['role'],
                $validated['permissions'] ?? null,
                auth()->user()
            );

            return redirect()->back()
                ->with('success', 'Member invited successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['email' => $e->getMessage()]);
        }
    }

    public function update(Request $request, Organisation $organisation, User $user): RedirectResponse
    {
        $this->authorize('manageMembers', $organisation);

        $validated = $request->validate([
            'role' => ['required', 'string', 'in:' . implode(',', OrganisationRole::values())],
        ]);

        try {
            $this->organisationService->updateMemberRole(
                $organisation,
                $user,
                $validated['role'],
                auth()->user()
            );

            return redirect()->back()
                ->with('success', 'Member role updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['role' => $e->getMessage()]);
        }
    }

    public function destroy(Organisation $organisation, User $user): RedirectResponse
    {
        $this->authorize('manageMembers', $organisation);

        try {
            $this->organisationService->removeMember($organisation, $user, auth()->user());

            return redirect()->back()
                ->with('success', 'Member removed successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['user' => $e->getMessage()]);
        }
    }
}