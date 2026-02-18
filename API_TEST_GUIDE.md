# API-First Implementation - Quick Test Guide

This guide helps you test the API implementation immediately.

## Prerequisites

1. Start your Laravel dev server:
```bash
php artisan serve
```

2. Create a user account (register at http://localhost:8000/register)

3. Get an auth token (we'll implement a token endpoint, for now use Sanctum)

## Testing the API Endpoints

### 1. Create a Form

```bash
curl -X POST http://localhost:8000/api/forms \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Test Registration",
    "description": "A test form",
    "schema": [
      {
        "name": "full_name",
        "type": "text",
        "label": "Full Name",
        "required": true
      },
      {
        "name": "email",
        "type": "email",
        "label": "Email Address",
        "required": true
      },
      {
        "name": "age_group",
        "type": "select",
        "label": "Age Group",
        "required": true,
        "options": ["U12", "U14", "U16"]
      }
    ]
  }'
```

**Expected Response (201 Created):**
```json
{
  "success": true,
  "form": {
    "id": 1,
    "code": "abc123def456",
    "name": "Test Registration",
    "api_key": "form_sk_abcdefghijk1234567890",
    "embed_url": "http://localhost:8000/forms/abc123def456/viewform",
    "api_endpoint": "http://localhost:8000/api/forms/1/submissions",
    "schema": [...],
    "created_at": "2026-02-18T11:30:00Z"
  }
}
```

**Save the `api_key` and `id` for the next steps!**

### 2. Get Form Schema

```bash
# Public access (no auth needed)
curl http://localhost:8000/api/forms/1 \
  -H "Accept: application/json"
```

### 3. Submit a Form Response

```bash
curl -X POST http://localhost:8000/api/forms/1/submissions \
  -H "Content-Type: application/json" \
  -H "X-API-Client: test-client" \
  -d '{
    "full_name": "John Doe",
    "email": "john@example.com",
    "age_group": "U14"
  }'
```

**Expected Response (201 Created):**
```json
{
  "success": true,
  "submission": {
    "id": 1,
    "code": "sub-uuid-here",
    "submitted_at": "2026-02-18T11:35:00Z"
  }
}
```

### 4. Get All Submissions

```bash
curl "http://localhost:8000/api/forms/1/submissions?page=1&limit=50" \
  -H "Authorization: Bearer form_sk_xxx" \
  -H "Accept: application/json"
```

**Expected Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "code": "sub-uuid-here",
      "fields": {
        "full_name": "John Doe",
        "email": "john@example.com",
        "age_group": "U14"
      },
      "submitted_at": "2026-02-18T11:35:00Z"
    }
  ],
  "pagination": {
    "total": 1,
    "per_page": 50,
    "current_page": 1,
    "last_page": 1
  }
}
```

### 5. Update Form Schema

```bash
curl -X PATCH http://localhost:8000/api/forms/1 \
  -H "Authorization: Bearer form_sk_xxx" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Updated Form Name",
    "schema": [
      {
        "name": "new_field",
        "type": "text",
        "label": "New Field",
        "required": false
      }
    ]
  }'
```

### 6. Delete Form

```bash
curl -X DELETE http://localhost:8000/api/forms/1 \
  -H "Authorization: Bearer form_sk_xxx" \
  -H "Accept: application/json"
```

---

## Testing with Postman

1. Open Postman
2. Create a new Collection called "Dynamic Forms API"
3. Add requests for each endpoint above
4. Set the base URL to `http://localhost:8000/api`
5. Add your token to the Authorization tab (Bearer token)

---

## Getting a Sanctum Token (Temporary)

Until we set up a dedicated token endpoint, you can:

1. Use Laravel Tinker:
```bash
php artisan tinker
>>> $user = User::first()
>>> $token = $user->createToken('api-token')->plainTextToken
>>> echo $token
```

2. Or check the database directly for existing tokens in the `personal_access_tokens` table

---

## Python Testing Script

Save as `test_api.py`:

```python
import requests
import json

BASE_URL = "http://localhost:8000/api"
TOKEN = "your_sanctum_token_here"

def test_create_form():
    """Create a new form"""
    payload = {
        "name": "Python Test Form",
        "description": "Created via Python",
        "schema": [
            {
                "name": "name",
                "type": "text",
                "label": "Your Name",
                "required": True
            },
            {
                "name": "email",
                "type": "email",
                "label": "Email",
                "required": True
            }
        ]
    }
    
    response = requests.post(
        f"{BASE_URL}/forms",
        json=payload,
        headers={"Authorization": f"Bearer {TOKEN}"}
    )
    
    print("CREATE FORM")
    print(f"Status: {response.status_code}")
    print(json.dumps(response.json(), indent=2))
    
    return response.json()['form']['id'], response.json()['form']['api_key']

def test_submit_form(form_id):
    """Submit a response to the form"""
    payload = {
        "name": "Python User",
        "email": "python@test.com"
    }
    
    response = requests.post(
        f"{BASE_URL}/forms/{form_id}/submissions",
        json=payload,
        headers={"X-API-Client": "python-test"}
    )
    
    print("\nSUBMIT FORM")
    print(f"Status: {response.status_code}")
    print(json.dumps(response.json(), indent=2))

def test_get_submissions(form_id, api_key):
    """Get all submissions for the form"""
    response = requests.get(
        f"{BASE_URL}/forms/{form_id}/submissions",
        headers={"Authorization": f"Bearer {api_key}"}
    )
    
    print("\nGET SUBMISSIONS")
    print(f"Status: {response.status_code}")
    print(json.dumps(response.json(), indent=2))

if __name__ == "__main__":
    form_id, api_key = test_create_form()
    test_submit_form(form_id)
    test_get_submissions(form_id, api_key)
```

Run it:
```bash
pip install requests
python test_api.py
```

---

## What to Test

1. **Form Creation** ✓
   - Form is created in database
   - API key is generated
   - Schema is stored as JSON

2. **Form Retrieval** ✓
   - Can fetch form by ID
   - Schema is returned correctly

3. **Submissions** ✓
   - Can submit data via API
   - Validation works (try invalid email)
   - Submissions stored in database

4. **Get Submissions** ✓
   - Returns paginated results
   - Includes submission data

5. **Form Deletion** ✓
   - Form is soft-deleted
   - Cannot access deleted form

---

## Next Steps

Once basic API is working:

1. Implement `/api/orgs/{orgId}/forms` (org-specific forms)
2. Add authentication middleware for API routes
3. Implement webhooks for submission events
4. Add rate limiting
5. Create token management endpoints
6. Add API documentation page in UI

---

## Debugging

Check the logs:
```bash
tail -f storage/logs/laravel.log
```

Or enable query logging in `.env`:
```
DB_LOG=true
```

Then check `storage/logs/database.log`
