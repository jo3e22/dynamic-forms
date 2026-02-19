# Dynamic Forms - API-First Setup

**Status:** ✅ Ready to Deploy

This is the main monolithic application that:
- Manages **users** and **organisations**
- Manages **templates** and **settings**
- Displays forms built in Form-API
- Manages submissions locally (synced from Form-API)

---

## Architecture

```
Dynamic-Forms (Laravel 12) - Port 8000
├── Authentication (Users, Orgs)
├── Template Management
├── Forms Integration → Form-API
└── Vue 3 Frontend (Inertia.js)
        ↓
    Form-API (Laravel 12) - Port 8002
    ├── Form Models (Sections, Elements)
    ├── Form Submissions
    └── REST API
```

---

## Setup

### 1. Install Dependencies

```bash
cd /home/james/Projects/Bulsca/dynamic-forms
composer install
npm install
```

### 2. Configure Environment

Update `.env` with Form-API credentials (already configured):

```env
FORM_API_URL=http://127.0.0.1:8002/api
FORM_API_KEY=form_service_9yyNxuC7X4qTCvwf1k4kY0lgsfE94U4t
```

### 3. Run Migrations

```bash
php artisan migrate
```

### 4. Start the Application

```bash
php artisan serve  # Starts on :8000
```

In another terminal, start Form-API:

```bash
cd /home/james/Projects/Bulsca/form-api
php artisan serve --port 8002
```

Then start Vite (for hot reload):

```bash
npm run dev
```

---

## Key Services

### FormApiClient
**File:** `app/Services/FormApiClient.php`

HTTP wrapper for Form-API:
```php
$client = new FormApiClient();
$forms = $client->getForms();
$form = $client->getForm($formId);
$form = $client->createForm($data);
$form = $client->updateForm($formId, $data);
$client->deleteForm($formId);
$submission = $client->submitForm($formId, $data);
$submissions = $client->getSubmissions($formId);
```

### FormApiIntegrationService
**File:** `app/Services/FormApiIntegrationService.php`

Bridges local Form model with Form-API:
```php
$service = new FormApiIntegrationService($apiClient);
$service->createForm($localForm, $structure);
$form = $service->getForm($localForm);
$service->updateForm($localForm, $structure);
$service->deleteForm($localForm);
$service->submitForm($localForm, $data);
$submissions = $service->getSubmissions($localForm);
```

---

## Usage in Controllers

```php
<?php

namespace App\Http\Controllers\Form;

use App\Models\Form;
use App\Services\FormApiIntegrationService;

class FormController extends Controller
{
    public function __construct(
        protected FormApiIntegrationService $integration
    ) {}

    public function show(Form $form)
    {
        // Get form structure from Form-API
        $apiForm = $this->integration->getForm($form);

        return Inertia::render('FormDetail', [
            'form' => $form,
            'structure' => $apiForm,
        ]);
    }

    public function submit(Form $form, Request $request)
    {
        // Submit to Form-API
        $submission = $this->integration->submitForm($form, [
            'data' => $request->validated(),
        ]);

        return response()->json($submission);
    }
}
```

---

## Database Schema

### Local Form Model
```sql
forms
├── id
├── user_id          -- Who created it
├── organisation_id  -- Which org owns it
├── code            -- Unique slug
├── api_form_id     -- Link to Form-API
├── title
├── status
├── primary_color
├── secondary_color
└── timestamps
```

### Form-API (Separate DB)
```
Forms (in form-api database)
├── id
├── code
├── sections[]
│   ├── id
│   ├── title
│   ├── elements[]
│   │   ├── id
│   │   ├── type (field|heading|description|image|divider)
│   │   ├── label
│   │   ├── field_type (text|email|number|date|etc)
│   │   ├── required
│   │   └── validation_rules
└── submissions[]
    ├── id
    ├── data (JSON)
    └── created_at
```

---

## Integration Points

### When User Creates a Form

```
1. FormController@create
   ↓
2. FormService->createForm() [Local DB]
   ↓
3. FormApiIntegrationService->createForm() [API Call]
   ↓
4. Form-API creates structure
   ↓
5. Return api_form_id → save locally
```

### When User Views a Form

```
1. FormController@show
   ↓
2. FormApiIntegrationService->getForm() [API Call]
   ↓
3. Form-API returns sections + elements
   ↓
4. Inertia renders with structure
```

### When User Submits a Form

```
1. FormViewer@submit (frontend)
   ↓
2. SubmissionController@store [Local + API]
   ↓
3. FormApiIntegrationService->submitForm() [API Call]
   ↓
4. Form-API records submission
   ↓
5. Save reference locally
```

---

## API Credentials

**Service Key:** `form_service_9yyNxuC7X4qTCvwf1k4kY0lgsfE94U4t`

Store in `.env`:
```env
FORM_API_KEY=form_service_9yyNxuC7X4qTCvwf1k4kY0lgsfE94U4t
```

---

## What's Different from Before

| Feature | Old (Monolith) | New (API-First) |
|---------|--------|--------|
| Forms stored in | dynamic-forms DB | form-api DB |
| Form structure | Local tables | Form-API |
| Users | Local models | Local models |
| Organisations | Local models | Local models |
| Auth | Fortify (local) | Fortify (local) |
| Submissions | Local + API | Form-API |
| Templates | Local models | Local models |

---

## Troubleshooting

### "Cannot connect to Form-API"
1. Verify Form-API is running: `php artisan serve --port 8002`
2. Check API key in `.env`
3. Verify URL: `http://127.0.0.1:8002/api`

### "Form not found in API"
1. Check `forms.api_form_id` is set
2. Verify form exists in form-api database
3. Check logs: `storage/logs/laravel.log`

### "Submission failed"
1. Verify form structure in Form-API
2. Check submission data format
3. See form-api logs: `/home/james/Projects/Bulsca/form-api/storage/logs/`

---

## Running Everything

**Terminal 1 - Backend:**
```bash
cd /home/james/Projects/Bulsca/dynamic-forms
php artisan serve
```

**Terminal 2 - Form-API:**
```bash
cd /home/james/Projects/Bulsca/form-api
php artisan serve --port 8002
```

**Terminal 3 - Frontend (Vite):**
```bash
cd /home/james/Projects/Bulsca/dynamic-forms
npm run dev
```

Then open: **http://localhost:8000**

---

## Next Steps

1. Update FormController to use FormApiIntegrationService
2. Update form builder UI to call Form-API
3. Update submission handling to sync with Form-API
4. Test end-to-end flow
5. Deploy to staging

---

**Created:** February 19, 2026  
**Status:** ✅ Ready to Integrate
