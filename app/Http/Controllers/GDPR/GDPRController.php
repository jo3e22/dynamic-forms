<?php

namespace App\Http\Controllers\GDPR;

use App\Http\Controllers\Controller;
use App\Services\GDPRService;
use App\Models\GDPR\GDPRDataSubjectAccessRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GDPRController extends Controller
{
    public function __construct(
        protected GDPRService $gdprService
    ) {}

    /**
     * Record user consent
     * POST /api/gdpr/consent
     */
    public function recordConsent(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'gdpr_consent' => 'required|boolean',
            'marketing_consent' => 'nullable|boolean',
        ]);

        // Record GDPR consent
        if ($validated['gdpr_consent']) {
            $this->gdprService->recordConsent(
                $user,
                'gdpr',
                true,
                '1.0'
            );
            
            $user->update(['consent_gdpr_at' => now()]);
        }

        // Record marketing consent if given
        if ($validated['marketing_consent'] ?? false) {
            $this->gdprService->recordConsent(
                $user,
                'marketing_email',
                true,
                '1.0'
            );
            
            $user->update(['consent_marketing_at' => now()]);
        }

        return response()->json(['message' => 'Consent recorded successfully']);
    }

    /**
     * Get current user's personal data
     * GET /api/profile/gdpr/data
     */
    public function getUserData(): JsonResponse
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
            ],
            'forms' => $user->forms()->with('submissions')->get()->map(fn($form) => [
                'id' => $form->id,
                'title' => $form->title ?? 'Untitled',
                'submissions_count' => $form->submissions()->count(),
            ]),
            'gdpr' => [
                'consent_gdpr_at' => $user->consent_gdpr_at,
                'consent_marketing_at' => $user->consent_marketing_at,
                'preferences' => $user->gdpr_preferences,
            ],
        ]);
    }

    /**
     * Export user's complete data (DSAR - Article 15)
     * GET /api/profile/gdpr/export
     */
    public function exportData(Request $request)
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $data = $this->gdprService->exportUserData($user);

        return response()->json($data)
            ->header('Content-Disposition', 'attachment; filename="personal-data.json"');
    }

    /**
     * Request account deletion (DSAR - Article 17)
     * DELETE /api/profile/gdpr/delete
     */
    public function requestDeletion(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        // Mark for deletion
        $user->requestDeletion();

        // Log the request
        $this->gdprService->auditLog(
            'deletion_requested',
            'User',
            $user->id,
            $user->id,
            'user',
            $validated['reason'] ?? 'User requested account deletion'
        );

        // Log out the user
        auth()->logout();

        return response()->json([
            'message' => 'Your account has been marked for deletion. You will be logged out.',
            'redirect' => '/'
        ]);
    }

    /**
     * Admin: Create DSAR request
     * POST /api/admin/gdpr/dsar
     */
    public function createDSAR(Request $request): JsonResponse
    {
        $this->authorize('manage_gdpr');

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'request_type' => 'required|in:access,export,deletion,rectification',
            'reason' => 'nullable|string',
        ]);

        $user = \App\Models\User::find($validated['user_id']);

        $dsar = $this->gdprService->createDataAccessRequest(
            $user,
            $validated['request_type'],
            $validated['reason'] ?? null
        );

        return response()->json($dsar, 201);
    }

    /**
     * Admin: Get pending DSARs
     * GET /api/admin/gdpr/dsar/pending
     */
    public function getPendingDSARs(): JsonResponse
    {
        $this->authorize('manage_gdpr');

        $dsars = GDPRDataSubjectAccessRequest::where('status', 'pending')
            ->with('user')
            ->orderBy('deadline_at')
            ->get();

        return response()->json([
            'total' => count($dsars),
            'urgent' => $dsars->filter(fn($d) => $d->deadline_at < now()->addDays(5))->count(),
            'dsars' => $dsars,
        ]);
    }

    /**
     * Admin: Complete DSAR
     * POST /api/admin/gdpr/dsar/{id}/complete
     */
    public function completeDSAR(GDPRDataSubjectAccessRequest $dsar): JsonResponse
    {
        $this->authorize('manage_gdpr');

        $token = $this->gdprService->completeDSAR($dsar);

        return response()->json([
            'message' => 'DSAR completed',
            'download_token' => $token,
            'download_url' => "/api/gdpr/dsar/{$dsar->id}/download?token={$token}",
            'expires_at' => now()->addDays(7),
        ]);
    }

    /**
     * Download DSAR data with token
     * GET /api/gdpr/dsar/{id}/download
     */
    public function downloadDSAR(GDPRDataSubjectAccessRequest $dsar, Request $request)
    {
        $token = $request->query('token');

        if (!$token || $dsar->download_token !== $token) {
            return response()->json(['error' => 'Invalid or missing token'], 401);
        }

        if (!$dsar->isTokenValid()) {
            return response()->json(['error' => 'Token expired'], 401);
        }

        return response()->json($dsar->response_data)
            ->header('Content-Disposition', 'attachment; filename="dsar-data.json"');
    }

    /**
     * Get compliance report
     * GET /api/admin/gdpr/compliance-report
     */
    public function getComplianceReport(Request $request): JsonResponse
    {
        $this->authorize('manage_gdpr');

        $fromDate = $request->query('from') 
            ? \Carbon\Carbon::parse($request->query('from'))
            : now()->startOfMonth();
        
        $toDate = $request->query('to')
            ? \Carbon\Carbon::parse($request->query('to'))
            : now();

        $report = $this->gdprService->getComplianceReport($fromDate, $toDate);

        return response()->json($report);
    }
}
