# GDPR Compliance Implementation Guide

Your dynamic-forms project now includes comprehensive GDPR compliance features for EU data processing.

## What's Included

### 1. **Database Tables** (Migration: `2026_02_18_000001_add_gdpr_compliance_fields`)

- **`gdpr_audit_logs`** - Immutable audit trail of all data access/processing actions
- **`gdpr_consent_logs`** - Track user consent (GDPR, marketing, analytics)
- **`gdpr_data_subject_access_requests`** - Track DSAR requests (Article 15)
- **`gdpr_data_retention_policies`** - Define how long data is kept

New fields added to:
- **`users`** - Consent timestamps, deletion requests, GDPR preferences
- **`submissions`** - Retention date, PII flag

### 2. **GDPRService** (`app/Services/GDPRService.php`)

**Key Methods:**

```php
// Record consent
$gdprService->recordConsent($user, 'gdpr', true);

// Export user data (for DSAR or user download)
$data = $gdprService->exportUserData($user);

// Delete user and all data (right to be forgotten)
$gdprService->deleteUserData($user, 'user_requested');

// Set data retention policy
$gdprService->setRetentionPolicy('Default', 365); // 1 year

// Get compliance report
$report = $gdprService->getComplianceReport(
    Carbon::now()->startOfMonth(),
    Carbon::now()->endOfMonth()
);
```

### 3. **Models**

- `GDPRAuditLog`
- `GDPRConsentLog`
- `GDPRDataSubjectAccessRequest`
- `GDPRDataRetentionPolicy`

Updated:
- `User` - Added GDPR relationships and helper methods
- `Submission` - Added retention tracking

### 4. **Console Command**

```bash
# Apply data retention policies and delete expired submissions
php artisan gdpr:apply-retention-policies

# Preview what would be deleted (dry run)
php artisan gdpr:apply-retention-policies --dry-run
```

**Schedule this in your `console.php`:**

```php
$schedule->command('gdpr:apply-retention-policies')
    ->dailyAt('02:00') // Run at 2 AM daily
    ->onOneServer(); // Only one server in cluster
```

## Implementation Steps

### Step 1: Run Migration

```bash
php artisan migrate
```

This creates all GDPR tables and adds fields to `users` and `submissions`.

### Step 2: Configure Default Retention Policy

Create a seeder or add to your app initialization:

```php
// app/Providers/AppServiceProvider.php
use App\Services\GDPRService;

public function boot()
{
    // Set default retention policy on first boot
    if (! GDPRDataRetentionPolicy::count()) {
        app(GDPRService::class)->setRetentionPolicy(
            name: 'Default - 1 Year',
            retentionDays: 365,
            isDefault: true
        );
    }
}
```

### Step 3: Add Consent Recording to Registration

```php
// In your registration controller
use App\Services\GDPRService;

public function store(Request $request)
{
    // ... validation ...
    
    $user = User::create([...]);
    
    // Record GDPR consent
    app(GDPRService::class)->recordConsent(
        $user,
        'gdpr',
        true,
        '1.0'
    );
    
    // Optionally record marketing consent
    if ($request->has('marketing_consent')) {
        app(GDPRService::class)->recordConsent(
            $user,
            'marketing_email',
            true
        );
    }
}
```

### Step 4: Set Retention on Submission Creation

```php
// When creating a submission
use App\Services\GDPRService;

$submission = new Submission($data);
$retention = app(GDPRService::class)->calculateRetentionDate($now, 365);
$submission->retention_until = $retention;
$submission->contains_pii = true; // if it has names, emails, etc
$submission->save();

// Log the creation
app(GDPRService::class)->auditLog(
    'submission_created',
    'Submission',
    $submission->id,
    auth()->id(),
    'user',
    'Form submission received'
);
```

### Step 5: Implement DSAR Handling

```php
// Create a DSAR request
use App\Models\GDPR\GDPRDataSubjectAccessRequest;
use App\Services\GDPRService;

public function requestDataExport(User $user)
{
    $request = app(GDPRService::class)
        ->createDataAccessRequest($user, 'export');
    
    // Send email notification to user about the request
    // ...
}

// Admin reviews and completes the request
public function completeDSAR(GDPRDataSubjectAccessRequest $request)
{
    $downloadToken = app(GDPRService::class)->completeDSAR($request);
    
    // Send secure download link to user
    // URL: /api/gdpr/download/{token}
}
```

## Key GDPR Articles Addressed

| Article | Requirement | Implementation |
|---------|-------------|-----------------|
| **15** | Right of access | `exportUserData()` + DSAR requests |
| **17** | Right to erasure ("right to be forgotten") | `deleteUserData()` |
| **5** | Data minimization & retention | `retention_until` + scheduled deletion |
| **25** | Data protection by design | Audit logging built-in |
| **32** | Security measures | Encrypted fields, audit trails |
| **33** | Breach notification | Log in `gdpr_audit_logs` |

## Data Encryption

Sensitive fields in submissions should be encrypted:

```php
// In SubmissionField model
protected $casts = [
    'answer' => 'encrypted', // Laravel's built-in encryption
];
```

## API Endpoints to Create

```php
// User exports their data
POST /api/profile/gdpr/export

// Admin downloads completed DSAR
GET /api/admin/gdpr/dsar/{id}/download?token={token}

// Compliance report
GET /api/admin/gdpr/compliance-report

// View audit logs
GET /api/admin/gdpr/audit-logs
```

## Privacy Policy Requirements

Your privacy policy should mention:

- ✅ Data retention periods
- ✅ User rights (access, deletion, rectification)
- ✅ Processing purposes
- ✅ Third-party data sharing
- ✅ How to exercise rights (contact form)
- ✅ Data breach notification process

## Monitoring & Compliance

Run these regularly:

```bash
# Check pending DSAR requests nearing deadline
php artisan tinker
>>> App\Models\GDPR\GDPRDataSubjectAccessRequest::where('status', 'pending')->where('deadline_at', '<', now()->addDays(5))->get()

# Check audit logs for suspicious activity
>>> App\Models\GDPR\GDPRAuditLog::where('action', 'data_accessed')->whereBetween('created_at', [now()->subDays(7), now()])->get()

# Generate compliance report
>>> app(App\Services\GDPRService::class)->getComplianceReport(now()->startOfMonth(), now()->endOfMonth())
```

## Additional Security Considerations

1. **HTTPS Only** - Enforce in `.env`:
   ```php
   SESSION_SECURE_COOKIES=true
   // And in config/app.php
   'url' => 'https://yourdomain.com'
   ```

2. **Encryption Key** - Generate strong key:
   ```bash
   php artisan key:generate
   ```

3. **Database Backups** - Encrypt backups and store securely

4. **Data Processor Agreement** - If using cloud services (email, hosting), ensure DPA is signed

5. **Third-party Services** - Only store PIIs when necessary

## Need Help?

Refer to:
- [GDPR Official Text (EU)](https://gdpr-info.eu/)
- [EDPB Guidelines](https://edpb.ec.europa.eu/)
- [ICO Guidance (UK)](https://ico.org.uk/for-organisations/gdpr/)

