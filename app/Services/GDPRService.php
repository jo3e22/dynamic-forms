<?php

namespace App\Services;

use App\Models\User;
use App\Models\Submission;
use App\Models\SubmissionField;
use App\Models\GDPR\{
    GDPRAuditLog,
    GDPRDataSubjectAccessRequest,
    GDPRConsentLog,
    GDPRDataRetentionPolicy
};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class GDPRService
{
    /**
     * Log GDPR-related actions for compliance audit trail
     */
    public function auditLog(
        string $action,
        string $entityType,
        int $entityId,
        ?int $userId = null,
        string $actorType = 'user',
        ?string $reason = null,
        array $dataSummary = []
    ): GDPRAuditLog {
        return GDPRAuditLog::create([
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'user_id' => $userId,
            'actor_type' => $actorType,
            'reason' => $reason,
            'data_summary' => $dataSummary,
            'status' => 'completed',
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }

    /**
     * Record user consent
     */
    public function recordConsent(
        User $user,
        string $consentType,
        bool $given,
        string $version = '1.0'
    ): GDPRConsentLog {
        return GDPRConsentLog::create([
            'user_id' => $user->id,
            'consent_type' => $consentType,
            'given' => $given,
            'version' => $version,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }

    /**
     * Create a Data Subject Access Request (DSAR)
     * GDPR Article 15: Right of access
     */
    public function createDataAccessRequest(
        User $user,
        string $requestType,
        ?string $reason = null
    ): GDPRDataSubjectAccessRequest {
        return GDPRDataSubjectAccessRequest::create([
            'user_id' => $user->id,
            'request_type' => $requestType,
            'status' => 'pending',
            'reason' => $reason,
            'requested_at' => now(),
            'deadline_at' => now()->addDays(30), // GDPR requirement
        ]);
    }

    /**
     * Export user's personal data for DSAR or user download
     * Returns all user-related data in structured format
     */
    public function exportUserData(User $user): array
    {
        $data = [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
            'gdpr' => [
                'consent_gdpr_at' => $user->consent_gdpr_at,
                'consent_marketing_at' => $user->consent_marketing_at,
                'preferences' => $user->gdpr_preferences,
            ],
            'forms' => [],
            'submissions' => [],
            'activity_logs' => [],
        ];

        // Get user's forms and submissions
        $submissions = $user->submissions()->with('form')->get();
        
        foreach ($submissions as $submission) {
            $submissionData = [
                'id' => $submission->id,
                'form_id' => $submission->form_id,
                'form_title' => $submission->form->title ?? null,
                'email' => $submission->email,
                'status' => $submission->status,
                'created_at' => $submission->created_at,
                'fields' => [],
            ];

            // Get submission fields
            foreach ($submission->submissionFields as $field) {
                $submissionData['fields'][] = [
                    'field_label' => $field->formField->label ?? null,
                    'answer' => $field->answer, // Include actual data
                    'created_at' => $field->created_at,
                ];
            }

            $data['submissions'][] = $submissionData;
        }

        // Log this data access
        $this->auditLog(
            'data_exported',
            'User',
            $user->id,
            $user->id,
            'user',
            'User requested data export',
            ['record_count' => count($data['submissions'])]
        );

        return $data;
    }

    /**
     * Delete user account and all associated data
     * GDPR Article 17: Right to erasure ("right to be forgotten")
     */
    public function deleteUserData(User $user, string $reason = 'user_requested'): bool
    {
        return DB::transaction(function () use ($user, $reason) {
            // First, log the deletion request
            $this->auditLog(
                'data_deletion_started',
                'User',
                $user->id,
                $user->id,
                'user',
                $reason,
                ['account_created_at' => $user->created_at]
            );

            // Mark submissions for deletion by setting retention_until to now
            Submission::where('user_id', $user->id)
                ->update(['retention_until' => now()]);

            // Delete submission fields
            SubmissionField::whereIn(
                'submission_id',
                Submission::where('user_id', $user->id)->pluck('id')
            )->forceDelete();

            // Delete submissions
            Submission::where('user_id', $user->id)->forceDelete();

            // Delete user
            $user->delete();

            // Log completion
            $this->auditLog(
                'data_deletion_completed',
                'User',
                $user->id,
                null,
                'system',
                $reason
            );

            return true;
        });
    }

    /**
     * Apply data retention policies
     * Should be run as a scheduled command
     */
    public function applyRetentionPolicies(): int
    {
        $deletedCount = 0;

        // Get all submissions with expired retention periods
        $expiredSubmissions = Submission::where(
            'retention_until',
            '<',
            now()
        )->whereNotNull('retention_until')->get();

        foreach ($expiredSubmissions as $submission) {
            // Log deletion
            $this->auditLog(
                'data_deleted_retention_policy',
                'Submission',
                $submission->id,
                null,
                'automated_deletion',
                'Retention policy expired',
                ['retention_until' => $submission->retention_until]
            );

            // Delete submission fields first (cascade)
            $submission->submissionFields()->forceDelete();
            
            // Delete submission
            $submission->forceDelete();
            $deletedCount++;
        }

        return $deletedCount;
    }

    /**
     * Set retention policy for future submissions
     */
    public function setRetentionPolicy(
        string $name,
        int $retentionDays = 365,
        string $description = '',
        bool $isDefault = false
    ): GDPRDataRetentionPolicy {
        if ($isDefault) {
            GDPRDataRetentionPolicy::where('is_default', true)->update(['is_default' => false]);
        }

        return GDPRDataRetentionPolicy::create([
            'name' => $name,
            'retention_days' => $retentionDays,
            'description' => $description,
            'is_default' => $isDefault,
            'applies_from' => now(),
        ]);
    }

    /**
     * Calculate retention date for a submission
     */
    public function calculateRetentionDate(Carbon $createdAt, ?int $days = null): Carbon
    {
        $days = $days ?? $this->getDefaultRetentionDays();
        return $createdAt->addDays($days);
    }

    /**
     * Get default retention policy
     */
    public function getDefaultRetentionDays(): int
    {
        $policy = GDPRDataRetentionPolicy::where('is_default', true)->first();
        return $policy?->retention_days ?? 365; // Default to 1 year
    }

    /**
     * Complete a DSAR and generate download token
     */
    public function completeDSAR(
        GDPRDataSubjectAccessRequest $request
    ): string {
        $downloadToken = Str::random(64);

        $request->update([
            'status' => 'completed',
            'completed_at' => now(),
            'download_token' => $downloadToken,
            'token_expires_at' => now()->addDays(7),
            'response_data' => $this->exportUserData($request->user),
        ]);

        $this->auditLog(
            'dsar_completed',
            'User',
            $request->user_id,
            null,
            'admin',
            'DSAR processed and data exported',
            ['request_type' => $request->request_type]
        );

        return $downloadToken;
    }

    /**
     * Get compliance report
     */
    public function getComplianceReport(Carbon $fromDate, Carbon $toDate): array
    {
        return [
            'period' => [
                'from' => $fromDate,
                'to' => $toDate,
            ],
            'audit_logs' => [
                'total' => GDPRAuditLog::whereBetween('created_at', [$fromDate, $toDate])->count(),
                'by_action' => GDPRAuditLog::whereBetween('created_at', [$fromDate, $toDate])
                    ->groupBy('action')
                    ->selectRaw('action, count(*) as count')
                    ->get()
                    ->pluck('count', 'action'),
            ],
            'consent_records' => [
                'total' => GDPRConsentLog::whereBetween('created_at', [$fromDate, $toDate])->count(),
                'by_type' => GDPRConsentLog::whereBetween('created_at', [$fromDate, $toDate])
                    ->groupBy('consent_type')
                    ->selectRaw('consent_type, count(*) as count')
                    ->get()
                    ->pluck('count', 'consent_type'),
            ],
            'dsars' => [
                'total' => GDPRDataSubjectAccessRequest::whereBetween('created_at', [$fromDate, $toDate])->count(),
                'by_status' => GDPRDataSubjectAccessRequest::whereBetween('created_at', [$fromDate, $toDate])
                    ->groupBy('status')
                    ->selectRaw('status, count(*) as count')
                    ->get()
                    ->pluck('count', 'status'),
                'pending' => GDPRDataSubjectAccessRequest::where('status', 'pending')
                    ->where('deadline_at', '<', now())
                    ->count(),
            ],
            'data_retention' => [
                'policies' => GDPRDataRetentionPolicy::all(),
                'submissions_pending_deletion' => Submission::where('retention_until', '<', now())
                    ->whereNotNull('retention_until')
                    ->count(),
            ],
        ];
    }
}
