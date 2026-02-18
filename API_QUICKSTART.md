# Dynamic Forms API - Quick Start Guide

Your API-first form creation system is now live! Create dynamic forms, accept submissions, and integrate with external systems.

## 🚀 What's Working

✅ **Form Creation** - Create forms from JSON schemas via API  
✅ **Form Submissions** - Accept responses to your forms  
✅ **Data Retrieval** - Paginated submission access with API keys  
✅ **CSV Export** - Download all submissions as CSV  
✅ **Unique API Keys** - Auto-generated keys (form_sk_xxx format)  

## 📝 5-Minute Example

```python
import requests

API_BASE = "http://localhost:8001/api"
TOKEN = "your-sanctum-token"  # Get from Tinker

# 1. Create a form
form = requests.post(f"{API_BASE}/forms", 
    json={
        "name": "Event Registration",
        "schema": [
            {"name": "name", "type": "text", "label": "Full Name", "required": True},
            {"name": "email", "type": "email", "label": "Email", "required": True},
            {"name": "attending", "type": "checkbox", "label": "Will attend?"},
        ]
    },
    headers={"Authorization": f"Bearer {TOKEN}"}
)
form_id = form.json()['form']['id']
api_key = form.json()['form']['api_key']
print(f"✓ Form created! ID: {form_id}, Key: {api_key}")

# 2. Submit a response (public - no auth needed)
submission = requests.post(f"{API_BASE}/forms/{form_id}/submissions",
    json={"name": "John Doe", "email": "john@example.com", "attending": True}
)
print(f"✓ Submission received! Code: {submission.json()['submission']['code']}")

# 3. Get all submissions
submissions = requests.get(f"{API_BASE}/forms/{form_id}/submissions",
    headers={"Authorization": f"Bearer {api_key}"}
)
for sub in submissions.json()['data']:
    print(f"  - {sub['fields']['name']} ({sub['email']})")
```

## 📚 Supported Field Types

- **text** - Single line text
- **email** - Email address (validates format)
- **number** - Numeric values
- **date** - Date picker
- **select** - Dropdown with options
- **checkbox** - True/false
- **textarea** - Multi-line text

## 🔑 Authentication

Two ways to authenticate:

**1. Sanctum Tokens** (for creating/managing forms)
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  -X POST http://localhost:8001/api/forms
```

**2. API Keys** (for accessing specific forms)
```bash
curl -H "Authorization: Bearer form_sk_xxxxx" \
  http://localhost:8001/api/forms/123/submissions
```

## 📊 API Endpoints

| Method | Endpoint | Auth | Purpose |
|--------|----------|------|---------|
| POST | `/forms` | Sanctum | Create form |
| GET | `/forms/{id}` | None | Get form schema |
| POST | `/forms/{id}/submissions` | None | Submit response |
| GET | `/forms/{id}/submissions` | API Key | List submissions |
| PATCH | `/forms/{id}` | API Key | Update form |
| DELETE | `/forms/{id}` | API Key | Delete form |

## ✨ Real-World Examples

**Sports Tournament Registration**
```python
python sports_example.py
```

**Quick Test Script**
```python
python test_multi_submissions.py
```

## 🛠️ Integration Patterns

### JavaScript/Frontend
Embed forms in your website using the public submission endpoint - no auth needed!

### Python/Automation
Use API keys to programmatically create forms and retrieve data.

### Webhooks (Coming Soon)
Set up webhooks to receive real-time submission notifications.

## 📈 Next Steps

1. ✅ Test the API endpoints (see examples above)
2. ⏳ Integrate with your frontend (embed submission endpoint)
3. ⏳ Set up organization-level form management
4. ⏳ Add subdomain support for branding
5. ⏳ Configure webhooks for automation

## 🔗 Resources

- `API_FIRST_GUIDE.md` - Complete API documentation
- `SPORTS_ORG_GUIDE.md` - Multi-level registration example
- `API_TEST_GUIDE.md` - Testing and debugging guide

---

**Questions?** Check the implementation in `app/Http/Controllers/Api/FormApiController.php`
