# Frontend GDPR Integration Guide

This document explains how the GDPR compliance system integrates with your Vue.js frontend.

## Components

### 1. **GDPRConsentForm** (`resources/js/components/GDPR/GDPRConsentForm.vue`)

Standalone consent form component for registration and explicit consent flows.

**Usage in Registration:**
```vue
<template>
    <GDPRConsentForm 
        @consent="handleConsent"
    />
</template>
```

**Props:**
- `standalone` (boolean) - Show as dialog vs embedded
- `onConsent` (function) - Callback when consent given

**What it does:**
- Displays GDPR Article 5-7 information
- Allows user to give/reject consent
- Shows data rights information
- Submits consent to `/api/gdpr/consent` endpoint

### 2. **GDPRPrivacyPanel** (`resources/js/components/GDPR/GDPRPrivacyPanel.vue`)

Account privacy and data management panel for authenticated users.

**Usage in Settings/Profile:**
```vue
<template>
    <GDPRPrivacyPanel />
</template>
```

**Features:**
- View account information
- Export personal data (GDPR Article 15 - Right of Access)
- View consent history
- Request account deletion (GDPR Article 17 - Right to be Forgotten)

## Updated Pages

### Registration (`resources/js/pages/auth/Register.vue`)

**Changes:**
- Added GDPR consent checkbox (required)
- Added marketing consent checkbox (optional)
- Consent is validated on form submission
- Hidden inputs track consent state

**Flow:**
1. User enters credentials
2. User must check GDPR consent to enable submit button
3. User optionally checks marketing consent
4. Form posts to `/register.store` with consent flags
5. Backend records consent and creates user

### Profile Settings (`resources/js/pages/profile/Settings.vue`)

**Tab Structure:**
- **Privacy & Security** - GDPRPrivacyPanel (data export, deletion, consent history)
- **Profile** - User info editing
- **Notifications** - Consent preferences

## API Endpoints

All endpoints require CSRF token in `X-CSRF-TOKEN` header.

### Public Endpoints

#### Record Consent
```
POST /api/gdpr/consent
Content-Type: application/json

{
    "gdpr_consent": true,
    "marketing_consent": true
}

Response:
{ "message": "Consent recorded successfully" }
```

### Authenticated User Endpoints

#### Get User Data
```
GET /api/profile/gdpr/data
Authorization: Bearer {token}

Response:
{
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "created_at": "2026-02-18T..."
    },
    "forms": [...],
    "gdpr": {
        "consent_gdpr_at": "2026-02-18T...",
        "consent_marketing_at": "2026-02-18T...",
        "preferences": {...}
    }
}
```

#### Export Data (DSAR - Article 15)
```
GET /api/profile/gdpr/export
Authorization: Bearer {token}

Response: JSON file download with full user data
```

#### Request Deletion (DSAR - Article 17)
```
DELETE /api/profile/gdpr/delete
Authorization: Bearer {token}
Content-Type: application/json

{
    "reason": "user_requested"
}

Response:
{
    "message": "Your account has been marked for deletion...",
    "redirect": "/"
}

Notes:
- User is logged out immediately
- Data deleted after 30-day retention period
- User cannot undo after 30 days
```

### Admin Endpoints

#### List Pending DSARs
```
GET /api/admin/gdpr/dsar/pending
Authorization: Bearer {token}

Response:
{
    "total": 5,
    "urgent": 2,
    "dsars": [...]
}
```

#### Complete DSAR (generate download link)
```
POST /api/admin/gdpr/dsar/{id}/complete
Authorization: Bearer {token}

Response:
{
    "message": "DSAR completed",
    "download_token": "secure_random_token",
    "download_url": "/api/gdpr/dsar/1/download?token=...",
    "expires_at": "2026-02-25T..."
}
```

#### Get Compliance Report
```
GET /api/admin/gdpr/compliance-report?from=2026-02-01&to=2026-02-28
Authorization: Bearer {token}

Response:
{
    "period": {...},
    "audit_logs": {...},
    "consent_records": {...},
    "dsars": {...},
    "data_retention": {...}
}
```

## User Flows

### New User Registration

```
1. User clicks "Sign up"
   ↓
2. Sees Register page with:
   - Name, Email, Password fields
   - GDPR consent checkbox (required)
   - Marketing consent checkbox (optional)
   ↓
3. Must check GDPR to enable submit
   ↓
4. Submits form to /register.store
   ↓
5. Backend validates & creates user
   ↓
6. GDPRService::recordConsent() called
   ↓
7. Consent logged in gdpr_consent_logs table
   ↓
8. User logged in and redirected to /forms
```

### User Exports Personal Data

```
1. User goes to /profile/settings
   ↓
2. Clicks "Privacy & Security" tab
   ↓
3. Clicks "Download My Data"
   ↓
4. Browser fetches /api/profile/gdpr/export
   ↓
5. Backend calls GDPRService::exportUserData()
   ↓
6. JSON file downloaded with:
   - User details
   - All forms
   - All submissions + answers
   - Consent history
   - Activity logs
   ↓
7. GDPRService::auditLog() records access
```

### User Requests Account Deletion

```
1. User checks "I understand..." checkbox
   ↓
2. Clicks "Delete My Account"
   ↓
3. Confirmation dialog
   ↓
4. Submits DELETE /api/profile/gdpr/delete
   ↓
5. Backend marks user with requested_deletion_at
   ↓
6. GDPRAuditLog records deletion request
   ↓
7. User logged out and redirected to /
   ↓
8. After 30 days, cron job runs:
   php artisan gdpr:apply-retention-policies
   ↓
9. All data permanently deleted
```

### Admin Reviews DSAR

```
1. Admin sees /admin/gdpr/dsar
   ↓
2. Sees list of pending requests with deadlines
   ↓
3. Clicks "Complete Request"
   ↓
4. POST /api/admin/gdpr/dsar/{id}/complete
   ↓
5. Backend generates secure download token
   ↓
6. Token valid for 7 days
   ↓
7. Admin sends download link to user via secure channel
   ↓
8. User downloads via /api/gdpr/dsar/1/download?token=...
   ↓
9. Token validated before serving data
```

## State Management

Data is managed through:
- **Inertia props** for initial page state
- **Fetch API** for dynamic GDPR endpoints
- **Vue refs** for form state

Example:
```typescript
const gdprConsent = ref(false);
const marketingConsent = ref(false);

// Submit with hidden inputs
<input type="hidden" name="gdpr_consent" :value="gdprConsent ? '1' : '0'" />
```

## Error Handling

### Consent Not Given
- Submit button disabled until GDPR checked
- Visual feedback with red border/text
- Cannot proceed without consent

### Data Export Failure
- Shows error message
- User can retry
- Logs error server-side

### Deletion Prevention
- Double confirmation dialogs
- User must understand consequences
- 30-day grace period to contact support

## Styling

Components use:
- **Shadcn Vue** UI components (Card, Button, Checkbox, Alert)
- **Tailwind CSS** for styling
- **Lucide Vue** icons

Color scheme:
- **Blue** - Information/GDPR required
- **Purple** - Optional/Marketing
- **Red** - Danger/Deletion
- **Green** - Success/Confirmed

## Testing

### Unit Tests
```typescript
// Test consent checkbox behavior
test('gdpr consent required', () => {
    const consent = ref(false);
    const button = wrapper.find('button[type="submit"]');
    
    expect(button.attributes('disabled')).toBeDefined();
    
    consent.value = true;
    expect(button.attributes('disabled')).toBeUndefined();
});
```

### Integration Tests
```typescript
test('user can export data', async () => {
    const response = await fetch('/api/profile/gdpr/export', {
        headers: { 'Authorization': 'Bearer token' }
    });
    
    expect(response.ok).toBe(true);
    const blob = await response.blob();
    expect(blob.type).toBe('application/json');
});
```

## Troubleshooting

### Issue: Consent not being recorded
**Solution:** 
- Check CSRF token in header
- Verify form submission includes consent flags
- Check database for gdpr_consent_logs entries

### Issue: Data export returns 401
**Solution:**
- Ensure user is authenticated
- Check Bearer token in Authorization header
- Verify token hasn't expired

### Issue: Deletion not working
**Solution:**
- Check that requested_deletion_at is set
- Verify scheduler running: `php artisan schedule:work`
- Check that retention policy is set
- Review gdpr_audit_logs for errors

## Privacy Policy Links

Reference privacy policy sections in:
- Registration form: `/privacy-policy#gdpr-rights`
- Consent form: `/privacy-policy#data-processing`
- Deletion flow: `/privacy-policy#deletion`

Create corresponding sections in your privacy policy document.

## Next Steps

1. Create `/privacy-policy` page with GDPR sections
2. Add email templates for DSAR responses
3. Set up scheduled task for retention policies
4. Create admin dashboard for DSAR management
5. Add audit log viewer for admins
6. Test all flows thoroughly
7. Get legal review of privacy policy
8. Deploy with careful monitoring

