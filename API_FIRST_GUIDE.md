# API-First Form Creation Guide

Complete documentation for the Dynamic Forms API-First implementation.

## Overview

The API allows organizations to:
- Create forms programmatically from JSON schemas
- Retrieve form submissions as JSON
- Submit responses via API
- Manage form configurations
- Full GDPR compliance with data export/deletion

## Authentication

### API Key Authentication

When you create a form via API, you receive a unique `api_key`:

```
Authorization: Bearer form_sk_xxxxxxxxxxxxxxxxxxxxx
```

Or as a query parameter:
```
GET /api/forms/123?api_key=form_sk_xxxxxxxxxxxxxxxxxxxxx
```

### User Authentication

For creating forms and managing your account:

```
Authorization: Bearer {sanctum_token}
```

Get a Sanctum token by logging in or creating an account.

---

## Endpoints

### 1. Create Form from JSON Schema

**POST** `/api/forms`

Create a new form from a JSON schema definition.

**Required Auth:** Sanctum token (user authenticated)

**Request Body:**
```json
{
  "name": "Competition Registration",
  "description": "Sign up for our U14 competition",
  "schema": [
    {
      "name": "player_name",
      "type": "text",
      "label": "Player Name",
      "required": true
    },
    {
      "name": "player_email",
      "type": "email",
      "label": "Email Address",
      "required": true
    },
    {
      "name": "age_group",
      "type": "select",
      "label": "Age Group",
      "required": true,
      "options": ["U12", "U14", "U16", "U18"]
    },
    {
      "name": "experience",
      "type": "checkbox",
      "label": "I am an experienced player",
      "required": false
    }
  ]
}
```

**Field Types Supported:**
- `text` - Single line text input
- `email` - Email validation required
- `number` - Numeric input only
- `date` - Date picker
- `select` - Dropdown with options
- `checkbox` - Single checkbox (boolean)
- `textarea` - Multi-line text

**Response (201 Created):**
```json
{
  "success": true,
  "form": {
    "id": 123,
    "code": "abc123def456",
    "name": "Competition Registration",
    "api_key": "form_sk_abcdefghijk1234567890",
    "embed_url": "https://dynamicforms.eu/forms/abc123def456/viewform",
    "api_endpoint": "https://api.dynamicforms.eu/api/forms/123/submissions",
    "schema": [...],
    "created_at": "2026-02-18T10:30:00Z"
  }
}
```

**Example cURL:**
```bash
curl -X POST https://api.dynamicforms.eu/api/forms \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Form",
    "schema": [
      {
        "name": "full_name",
        "type": "text",
        "label": "Full Name",
        "required": true
      }
    ]
  }'
```

---

### 2. Get Form Schema

**GET** `/api/forms/{formId}`

Retrieve a form's structure and metadata.

**Auth:** Public (if form is public) or API key

**Query Parameters:**
- `api_key` (optional) - If using key-based auth

**Response (200 OK):**
```json
{
  "id": 123,
  "code": "abc123def456",
  "name": "Competition Registration",
  "description": "Sign up for our U14 competition",
  "schema": [...],
  "status": "open",
  "created_at": "2026-02-18T10:30:00Z",
  "updated_at": "2026-02-18T10:30:00Z"
}
```

**Example:**
```bash
curl https://api.dynamicforms.eu/api/forms/123
```

---

### 3. Update Form Schema

**PATCH** `/api/forms/{formId}`

Update a form's name, description, or schema.

**Required Auth:** API key or form owner

**Request Body:**
```json
{
  "name": "Updated Form Name",
  "description": "Updated description",
  "schema": [
    {
      "name": "new_field",
      "type": "text",
      "label": "New Field",
      "required": false
    }
  ]
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "form": {
    "id": 123,
    "code": "abc123def456",
    "name": "Updated Form Name",
    ...
  }
}
```

**Example:**
```bash
curl -X PATCH https://api.dynamicforms.eu/api/forms/123 \
  -H "Authorization: Bearer form_sk_xxx" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "New Name"
  }'
```

---

### 4. Submit Form Response

**POST** `/api/forms/{formId}/submissions`

Submit a completed form response.

**Auth:** Public (any user)

**Headers:**
```
X-API-Client: your-org-name (optional - identifies submission source)
```

**Request Body:**
```json
{
  "player_name": "John Smith",
  "player_email": "john@club.com",
  "age_group": "U14",
  "experience": true
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "submission": {
    "id": 456,
    "code": "sub-uuid-here",
    "submitted_at": "2026-02-18T10:35:00Z"
  }
}
```

**Validation Errors (422):**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "player_email": ["The player email field must be a valid email address."],
    "age_group": ["The selected age group is invalid."]
  }
}
```

**Example:**
```bash
curl -X POST https://api.dynamicforms.eu/api/forms/123/submissions \
  -H "Content-Type: application/json" \
  -H "X-API-Client: sports-org-name" \
  -d '{
    "player_name": "John Smith",
    "player_email": "john@club.com",
    "age_group": "U14",
    "experience": true
  }'
```

---

### 5. Get Form Submissions

**GET** `/api/forms/{formId}/submissions`

Retrieve all submissions for a form as JSON.

**Required Auth:** API key or form owner

**Query Parameters:**
- `page` (default: 1) - Page number for pagination
- `limit` (default: 50, max: 100) - Results per page

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 456,
      "code": "sub-uuid-here",
      "fields": {
        "player_name": "John Smith",
        "player_email": "john@club.com",
        "age_group": "U14",
        "experience": true
      },
      "submitted_at": "2026-02-18T10:35:00Z"
    },
    {
      "id": 457,
      "code": "sub-uuid-here-2",
      "fields": {
        "player_name": "Jane Doe",
        "player_email": "jane@club.com",
        "age_group": "U14",
        "experience": false
      },
      "submitted_at": "2026-02-18T10:36:00Z"
    }
  ],
  "pagination": {
    "total": 150,
    "per_page": 50,
    "current_page": 1,
    "last_page": 3
  }
}
```

**Example:**
```bash
curl https://api.dynamicforms.eu/api/forms/123/submissions \
  -H "Authorization: Bearer form_sk_xxx" \
  -H "Accept: application/json"

# Get page 2 with custom limit
curl 'https://api.dynamicforms.eu/api/forms/123/submissions?page=2&limit=25' \
  -H "Authorization: Bearer form_sk_xxx"
```

---

### 6. Delete Form

**DELETE** `/api/forms/{formId}`

Permanently delete a form and all its submissions.

**Required Auth:** API key or form owner

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Form deleted successfully"
}
```

**Example:**
```bash
curl -X DELETE https://api.dynamicforms.eu/api/forms/123 \
  -H "Authorization: Bearer form_sk_xxx"
```

---

## Usage Examples

### Example 1: Sports Organization Workflow

```bash
# 1. Create a competition registration form
FORM_RESPONSE=$(curl -X POST https://api.dynamicforms.eu/api/forms \
  -H "Authorization: Bearer your_token" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "U14 Championship Registration",
    "description": "Register your team for the U14 championship",
    "schema": [
      {"name": "club_name", "type": "text", "label": "Club Name", "required": true},
      {"name": "team_name", "type": "text", "label": "Team Name", "required": true},
      {"name": "coach_email", "type": "email", "label": "Coach Email", "required": true},
      {"name": "num_players", "type": "number", "label": "Number of Players", "required": true}
    ]
  }')

# Extract API key from response
API_KEY=$(echo $FORM_RESPONSE | jq -r '.form.api_key')
FORM_ID=$(echo $FORM_RESPONSE | jq -r '.form.id')

# 2. Share the form code/URL with clubs
echo "Form created! Share this URL: https://dynamicforms.eu/forms/$(echo $FORM_RESPONSE | jq -r '.form.code')/viewform"

# 3. Retrieve submissions later
curl https://api.dynamicforms.eu/api/forms/$FORM_ID/submissions \
  -H "Authorization: Bearer $API_KEY" \
  -H "Accept: application/json" | jq .
```

### Example 2: Automated Registration

```python
import requests
import json

API_URL = "https://api.dynamicforms.eu/api"
API_KEY = "form_sk_xxx"

# Submit multiple registrations
registrations = [
    {"name": "Player 1", "email": "p1@club.com", "age_group": "U14"},
    {"name": "Player 2", "email": "p2@club.com", "age_group": "U14"},
]

for registration in registrations:
    response = requests.post(
        f"{API_URL}/forms/123/submissions",
        json=registration,
        headers={"X-API-Client": "my-club"}
    )
    if response.status_code == 201:
        print(f"✓ {registration['name']} registered")
    else:
        print(f"✗ Error: {response.json()}")

# Get all submissions
response = requests.get(
    f"{API_URL}/forms/123/submissions",
    headers={"Authorization": f"Bearer {API_KEY}"}
)

submissions = response.json()['data']
print(f"Total registrations: {len(submissions)}")
```

### Example 3: JavaScript/Node.js

```javascript
const API_URL = "https://api.dynamicforms.eu/api";
const API_KEY = "form_sk_xxx";
const FORM_ID = 123;

// Create form
async function createForm(name, schema) {
  const response = await fetch(`${API_URL}/forms`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${YOUR_TOKEN}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ name, schema })
  });
  
  return await response.json();
}

// Submit response
async function submitResponse(formId, data) {
  const response = await fetch(`${API_URL}/forms/${formId}/submissions`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-API-Client': 'my-app'
    },
    body: JSON.stringify(data)
  });
  
  return await response.json();
}

// Get submissions
async function getSubmissions(formId, apiKey, page = 1) {
  const response = await fetch(
    `${API_URL}/forms/${formId}/submissions?page=${page}&limit=50`,
    {
      headers: {
        'Authorization': `Bearer ${apiKey}`
      }
    }
  );
  
  return await response.json();
}
```

---

## GDPR Compliance

All submissions include GDPR audit trails:

```bash
# Export all your data
curl https://api.dynamicforms.eu/api/profile/gdpr/export \
  -H "Authorization: Bearer your_token"

# Request account deletion
curl -X DELETE https://api.dynamicforms.eu/api/profile/gdpr/delete \
  -H "Authorization: Bearer your_token" \
  -H "Content-Type: application/json" \
  -d '{"reason": "user_requested"}'
```

---

## Error Handling

### Common Error Responses

**Unauthorized (401)**
```json
{
  "error": "Unauthorized"
}
```

**Validation Error (422)**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

**Form Not Found (404)**
```json
{
  "message": "Not found."
}
```

**Rate Limit (429)**
```json
{
  "error": "Too many requests"
}
```

---

## Best Practices

1. **Store API Keys Securely**
   - Never commit keys to version control
   - Use environment variables
   - Rotate keys regularly

2. **Handle Errors Gracefully**
   - Check response status codes
   - Implement retry logic with exponential backoff
   - Log failed submissions

3. **Validate Client-Side**
   - Fetch the form schema and validate before submission
   - Reduces unnecessary API calls
   - Better user experience

4. **Pagination**
   - Use limit=100 for large result sets
   - Implement pagination in your loop
   - Consider caching for performance

5. **Identify Your Client**
   - Always set `X-API-Client` header
   - Helps with debugging and analytics
   - Supports multi-organization deployments

---

## Rate Limiting

- **Free tier:** 100 requests/hour
- **Pro tier:** 10,000 requests/hour
- **Enterprise:** Unlimited

Check rate limit headers:
```
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1645123456
```

---

## Support

For issues or questions:
- Email: support@dynamicforms.eu
- API Status: https://status.dynamicforms.eu
- Documentation: https://docs.dynamicforms.eu
