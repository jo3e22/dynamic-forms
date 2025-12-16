<?php

namespace App\Http\Controllers\Organisation;

use App\Http\Controllers\Controller;

use App\Models\Organisation\Organisation;
use App\Services\OrganisationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class OrganisationController extends Controller
{
    public function __construct(
        protected OrganisationService $organisationService
    ) {}

    public function index(): Response
    {
        $organisations = $this->organisationService->getUserOrganisations(auth()->user());

        return Inertia::render('organisations/Index', [
            'organisations' => $organisations,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Organisation::class);

        return Inertia::render('organisations/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Organisation::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:organisations,slug'],
            'short_name' => ['nullable', 'string', 'max:100'],
            'type' => ['required', 'string'],
            'visibility' => ['required', 'string'],
            'description' => ['nullable', 'string'],
        ]);

        $organisation = $this->organisationService->createOrganisation($validated, auth()->user());

        return redirect()->route('organisations.show', $organisation)
            ->with('success', 'Organisation created successfully');
    }

    public function show(Organisation $organisation): Response
    {
        $this->authorize('view', $organisation);

        $organisation->load(['owner', 'branding', 'details', 'parent']);
        
        return Inertia::render('organisations/Show', [
            'organisation' => $organisation,
            'userRole' => $this->organisationService->getUserRole(auth()->user(), $organisation),
            'forms' => $this->organisationService->getOrganisationForms($organisation),
            'members' => $this->organisationService->getOrganisationMembers($organisation),
        ]);
    }

    public function edit(Organisation $organisation): Response
    {
        $this->authorize('update', $organisation);

        return Inertia::render('organisations/Edit', [
            'organisation' => $organisation->load(['branding', 'details']),
        ]);
    }

    public function update(Request $request, Organisation $organisation): RedirectResponse
    {
        $this->authorize('update', $organisation);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:organisations,slug,' . $organisation->id],
            'short_name' => ['nullable', 'string', 'max:100'],
            'type' => ['required', 'string'],
            'visibility' => ['required', 'string'],
            'branding' => ['nullable', 'array'],
            'details' => ['nullable', 'array'],
        ]);

        $this->organisationService->updateOrganisation($organisation, $validated);

        return redirect()->route('organisations.show', $organisation)
            ->with('success', 'Organisation updated successfully');
    }

    public function destroy(Organisation $organisation): RedirectResponse
    {
        $this->authorize('delete', $organisation);

        $organisation->delete();

        return redirect()->route('organisations.index')
            ->with('success', 'Organisation deleted successfully');
    }

    public function switchCurrent(Request $request, Organisation $organisation): RedirectResponse
    {
        $this->authorize('view', $organisation);

        session(['current_organisation_id' => $organisation->id]);

        return redirect()->back()
            ->with('success', "Switched to {$organisation->name}");
    }
}