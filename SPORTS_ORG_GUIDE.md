# Sports Organization Integration Guide

Quick-start guide for integrating Dynamic Forms API with your sports organization's event management workflow.

## Your Workflow

```
Host Club publishes event info
    ↓
Creates registration form via API
    ↓
Shares form URL with member clubs
    ↓
Member clubs/members register (web or API)
    ↓
Host club retrieves all submissions as JSON
    ↓
Process registrations, create teams, manage volunteers
```

## Quick Start (5 minutes)

### 1. Get Your Auth Token

First, sign up and get your API token from your account settings.

```bash
export TOKEN="your_sanctum_token_here"
```

### 2. Create a Registration Form

```bash
curl -X POST https://api.dynamicforms.eu/api/forms \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "U14 Championship Registration 2026",
    "description": "Register your team for the U14 Championship. Deadline: March 31, 2026",
    "schema": [
      {
        "name": "club_name",
        "type": "text",
        "label": "Club Name",
        "required": true
      },
      {
        "name": "team_name",
        "type": "text",
        "label": "Team Name",
        "required": true
      },
      {
        "name": "coach_email",
        "type": "email",
        "label": "Coach Email Address",
        "required": true
      },
      {
        "name": "num_players",
        "type": "number",
        "label": "Number of Players (max 14)",
        "required": true
      },
      {
        "name": "division",
        "type": "select",
        "label": "Division",
        "required": true,
        "options": ["Div A", "Div B", "Div C"]
      },
      {
        "name": "notes",
        "type": "textarea",
        "label": "Any special notes",
        "required": false
      }
    ]
  }'
```

**Save the response - you'll need the `api_key` and form `code`**

### 3. Share the Form

Share this URL with clubs:
```
https://dynamicforms.eu/forms/{code}/viewform
```

Or embed on your website:
```html
<script src="https://dynamicforms.eu/embed.js"></script>
<div id="event-form"></div>
<script>
  DynamicForms.embed({
    formId: 123,
    orgSubdomain: 'your-org'
  });
</script>
```

### 4. Retrieve Submissions

Once clubs start registering, get all submissions:

```bash
curl https://api.dynamicforms.eu/api/forms/123/submissions \
  -H "Authorization: Bearer form_sk_xxx" \
  -H "Accept: application/json" | jq .
```

Returns:
```json
{
  "data": [
    {
      "id": 456,
      "code": "sub-xxx",
      "fields": {
        "club_name": "City United",
        "team_name": "Youth A",
        "coach_email": "coach@cityunited.com",
        "num_players": 14,
        "division": "Div A",
        "notes": "Looking forward to it!"
      },
      "submitted_at": "2026-02-18T14:30:00Z"
    }
  ],
  "pagination": { ... }
}
```

### 5. Process Registrations

Export to CSV or process programmatically:

```python
import requests
import csv

API_KEY = "form_sk_xxx"
FORM_ID = 123

response = requests.get(
    f"https://api.dynamicforms.eu/api/forms/{FORM_ID}/submissions",
    headers={"Authorization": f"Bearer {API_KEY}"}
)

submissions = response.json()['data']

# Export to CSV
with open('registrations.csv', 'w', newline='') as f:
    writer = csv.DictWriter(f, fieldnames=['club_name', 'team_name', 'coach_email', 'num_players', 'division'])
    writer.writeheader()
    for sub in submissions:
        writer.writerow(sub['fields'])

print(f"Exported {len(submissions)} registrations")
```

---

## Advanced: Multi-Level Registration

### Level 1: Host Creates Event

```bash
# Create main event registration
curl -X POST https://api.dynamicforms.eu/api/forms \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Host Club - Event Setup",
    "schema": [
      {"name": "event_name", "type": "text", "label": "Event Name", "required": true},
      {"name": "date", "type": "date", "label": "Event Date", "required": true},
      {"name": "venue", "type": "text", "label": "Venue", "required": true}
    ]
  }'
```

### Level 2: Member Club Registers Team

```bash
curl -X POST https://api.dynamicforms.eu/api/forms/123/submissions \
  -H "Content-Type: application/json" \
  -H "X-API-Client: member-club-name" \
  -d '{
    "club_name": "City United",
    "team_name": "Youth A",
    "coach_email": "coach@city.com",
    "num_players": 14,
    "division": "Div A"
  }'
```

### Level 3: Member Registers Player

```bash
# Create a form for player registration that member club can share
curl -X POST https://api.dynamicforms.eu/api/forms \
  -H "Authorization: Bearer MEMBER_CLUB_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "City United - Player Registration",
    "schema": [
      {"name": "full_name", "type": "text", "label": "Full Name", "required": true},
      {"name": "email", "type": "email", "label": "Email", "required": true},
      {"name": "dob", "type": "date", "label": "Date of Birth", "required": true},
      {"name": "position", "type": "select", "label": "Position", "required": true, "options": ["Goalkeeper", "Defender", "Midfielder", "Forward"]}
    ]
  }'
```

---

## Sample Use Cases

### Use Case 1: Tournament Registration

```json
{
  "name": "Spring Tournament 2026 - Team Registration",
  "schema": [
    {"name": "club", "type": "text", "label": "Club Name", "required": true},
    {"name": "team", "type": "text", "label": "Team Name", "required": true},
    {"name": "contact_email", "type": "email", "label": "Contact Email", "required": true},
    {"name": "division", "type": "select", "label": "Division", "required": true, "options": ["U12", "U14", "U16", "U18"]},
    {"name": "max_players", "type": "number", "label": "Squad Size", "required": true},
    {"name": "special_needs", "type": "textarea", "label": "Any special requirements", "required": false}
  ]
}
```

### Use Case 2: Volunteer Sign-Up

```json
{
  "name": "Event Volunteers",
  "schema": [
    {"name": "full_name", "type": "text", "label": "Full Name", "required": true},
    {"name": "email", "type": "email", "label": "Email", "required": true},
    {"name": "phone", "type": "text", "label": "Phone Number", "required": true},
    {"name": "role", "type": "select", "label": "Preferred Role", "required": true, "options": ["Referee", "Assistant Referee", "Scorekeeper", "Setup/Breakdown", "First Aid"]},
    {"name": "experience", "type": "checkbox", "label": "I have experience in this role", "required": false}
  ]
}
```

### Use Case 3: Official/Coach Registration

```json
{
  "name": "Coach & Official Registration",
  "schema": [
    {"name": "full_name", "type": "text", "label": "Full Name", "required": true},
    {"name": "email", "type": "email", "label": "Email", "required": true},
    {"name": "role", "type": "select", "label": "Role", "required": true, "options": ["Head Coach", "Assistant Coach", "Referee", "Assessor"]},
    {"name": "club", "type": "text", "label": "Club", "required": true},
    {"name": "cert_number", "type": "text", "label": "Certification Number", "required": false},
    {"name": "cert_valid", "type": "date", "label": "Certification Valid Until", "required": false}
  ]
}
```

---

## API Key Management

Each form has its own API key. This allows you to:
- Share specific forms with other organizations
- Track which form is being accessed
- Revoke access without affecting other forms
- Set per-form rate limits (future feature)

**Never share your form's API key publicly.** Instead:
1. Create separate forms for public submission (no key needed)
2. Use the API key only when retrieving submissions
3. Store keys in environment variables, not code

---

## Retrieving Data for Processing

### Get All Submissions (Paginated)

```bash
# Page 1
curl "https://api.dynamicforms.eu/api/forms/123/submissions?page=1&limit=50" \
  -H "Authorization: Bearer form_sk_xxx"

# Page 2
curl "https://api.dynamicforms.eu/api/forms/123/submissions?page=2&limit=50" \
  -H "Authorization: Bearer form_sk_xxx"
```

### Format for Spreadsheet Import

```python
import requests
import json

API_KEY = "form_sk_xxx"
FORM_ID = 123

# Get all submissions
page = 1
all_submissions = []

while True:
    response = requests.get(
        f"https://api.dynamicforms.eu/api/forms/{FORM_ID}/submissions?page={page}&limit=100",
        headers={"Authorization": f"Bearer {API_KEY}"}
    )
    
    data = response.json()
    all_submissions.extend(data['data'])
    
    if page >= data['pagination']['last_page']:
        break
    page += 1

# Export as JSON
with open('registrations.json', 'w') as f:
    json.dump(all_submissions, f, indent=2)

print(f"Exported {len(all_submissions)} registrations")
```

### Format for Direct Database Insert

```python
# After getting submissions, insert into your database
for submission in all_submissions:
    fields = submission['fields']
    
    # Create team registration
    team = Team.create(
        club_name=fields['club_name'],
        team_name=fields['team_name'],
        coach_email=fields['coach_email'],
        num_players=fields['num_players'],
        division=fields['division'],
        registered_at=submission['submitted_at']
    )
```

---

## Webhooks (Future)

We're planning to support webhooks, which will notify your system when:
- Form is created
- Submission received
- Form closed
- Deletion requested

For now, poll the submissions endpoint periodically.

---

## Support

For your sports organization:
- Technical help: support@dynamicforms.eu
- Integration questions: dev@dynamicforms.eu
- Feature requests: features@dynamicforms.eu

**Happy organizing! ⚽**
