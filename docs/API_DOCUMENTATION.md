# OneStop Agency CRM - API Documentation

Version: 2.0  
Base URL: `http://your-domain.com/api/v1`

## Authentication

All API endpoints (except login and register) require a JWT token in the Authorization header:

```http
Authorization: Bearer <your-jwt-token>
```

### Login

```http
POST /auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "email": "user@example.com",
      "first_name": "John",
      "last_name": "Doe",
      "role": "admin"
    },
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
  }
}
```

## Standard Response Format

### Success Response
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { ... }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error description",
  "errors": { ... }
}
```

## Pagination

Paginated endpoints accept these query parameters:
- `page` (default: 1)
- `per_page` (default: 20, max: 100)

Response includes:
```json
{
  "data": [...],
  "total": 100,
  "per_page": 20,
  "current_page": 1,
  "last_page": 5,
  "from": 1,
  "to": 20
}
```

## Endpoints

### Clients

#### Get All Clients
```http
GET /clients?page=1&per_page=20&status=active
```

#### Get Single Client
```http
GET /clients/{id}
```

#### Create Client
```http
POST /clients
Content-Type: application/json

{
  "company_name": "Acme Corp",
  "contact_person": "John Smith",
  "email": "john@acme.com",
  "phone": "+1234567890",
  "source": "website",
  "notes": "Interested in SEO services"
}
```

#### Update Client
```http
PUT /clients/{id}
Content-Type: application/json

{
  "company_name": "Acme Corporation",
  "status": "active"
}
```

#### Delete Client
```http
DELETE /clients/{id}
```

### Deals

#### Get Pipeline View
```http
GET /pipeline
```

Returns all pipeline stages with deals grouped by stage.

#### Create Deal
```http
POST /deals
Content-Type: application/json

{
  "title": "Website Redesign Project",
  "client_id": 5,
  "value": 50000,
  "currency": "USD",
  "expected_close_date": "2025-12-31",
  "probability": 75
}
```

#### Update Deal Stage
```http
PUT /deals/{id}/stage
Content-Type: application/json

{
  "stage_id": 4
}
```

### Quotes

#### Calculate Quote
```http
POST /quotes/calculate
Content-Type: application/json

{
  "items": [
    {
      "description": "Senior Developer",
      "hours": 80,
      "hourly_rate": 75,
      "service_id": 1,
      "role_id": 2
    }
  ],
  "overhead_percentage": 20,
  "margin_percentage": 30,
  "tax_percentage": 18,
  "offer_multiplier": 2.0,
  "currency": "USD"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "subtotal": 6000,
    "overhead_amount": 1200,
    "margin_amount": 2160,
    "tax_amount": 1684.8,
    "total": 11044.8,
    "offer_value": 22089.6,
    "currency": "USD"
  }
}
```

#### Create Quote
```http
POST /quotes
Content-Type: application/json

{
  "client_id": 5,
  "title": "Web Development Quote",
  "items": [...],
  "subtotal": 6000,
  "total": 11044.8,
  "valid_until": "2025-11-30",
  "currency": "USD"
}
```

### Projects

#### Create Project
```http
POST /projects
Content-Type: application/json

{
  "name": "Website Redesign",
  "client_id": 5,
  "start_date": "2025-11-01",
  "end_date": "2025-12-31",
  "budget": 50000,
  "project_manager_id": 2,
  "team": [
    {
      "user_id": 3,
      "role": "Developer",
      "allocation_percentage": 80,
      "hourly_rate": 75
    }
  ]
}
```

#### Add Team Member
```http
POST /projects/{id}/team
Content-Type: application/json

{
  "user_id": 4,
  "role": "Designer",
  "allocation_percentage": 50,
  "hourly_rate": 60
}
```

### Tasks

#### Get Project Tasks (Kanban)
```http
GET /projects/{projectId}/tasks
```

Returns tasks grouped by status:
```json
{
  "todo": [...],
  "in_progress": [...],
  "blocked": [...],
  "done": [...]
}
```

#### Create Task
```http
POST /tasks
Content-Type: application/json

{
  "project_id": 10,
  "title": "Design homepage mockup",
  "description": "Create responsive design for homepage",
  "status": "todo",
  "priority": "high",
  "assigned_to": 4,
  "due_date": "2025-11-15",
  "estimated_hours": 8
}
```

#### Update Task
```http
PUT /tasks/{id}
Content-Type: application/json

{
  "status": "in_progress",
  "actual_hours": 3
}
```

### Time Tracking

#### Start Timer
```http
POST /time-logs/start
Content-Type: application/json

{
  "project_id": 10,
  "task_id": 45,
  "description": "Working on homepage design"
}
```

#### Stop Timer
```http
PUT /time-logs/{id}/stop
```

#### Manual Time Entry
```http
POST /time-logs/manual
Content-Type: application/json

{
  "project_id": 10,
  "task_id": 45,
  "start_time": "2025-10-25 09:00:00",
  "end_time": "2025-10-25 13:00:00",
  "description": "Morning work session",
  "is_billable": 1,
  "manual_justification": "Forgot to start timer"
}
```

### Invoices

#### Create Invoice
```http
POST /invoices
Content-Type: application/json

{
  "client_id": 5,
  "project_id": 10,
  "issue_date": "2025-10-25",
  "due_date": "2025-11-25",
  "items": [
    {
      "description": "Web Development Services",
      "quantity": 80,
      "unit_price": 75,
      "amount": 6000
    }
  ],
  "subtotal": 6000,
  "tax_percentage": 18,
  "tax_amount": 1080,
  "total": 7080,
  "currency": "USD"
}
```

### Payments

#### Record Payment
```http
POST /payments
Content-Type: application/json

{
  "invoice_id": 25,
  "amount": 7080,
  "payment_method": "bank_transfer",
  "transaction_id": "TXN123456",
  "notes": "Payment received via wire transfer"
}
```

### Dashboards

#### Admin Dashboard
```http
GET /dashboard/admin
```

#### Project Manager Dashboard
```http
GET /dashboard/pm
```

#### Sales Dashboard
```http
GET /dashboard/sales
```

#### Team Dashboard
```http
GET /dashboard/team
```

### AI Features

#### Pricing Suggestion
```http
POST /ai/pricing-suggestion
Content-Type: application/json

{
  "service_category": "web_development",
  "complexity": "high",
  "duration": 160
}
```

#### Resource Allocation
```http
POST /ai/resource-allocation
Content-Type: application/json

{
  "project_id": 10,
  "required_skills": ["php", "react", "ui_design"]
}
```

#### Deal Score
```http
POST /ai/deal-score
Content-Type: application/json

{
  "deal_id": 15
}
```

#### AI Insights
```http
GET /ai/insights
```

### Notifications

#### Get Notifications
```http
GET /notifications?page=1&is_read=0
```

#### Mark as Read
```http
PUT /notifications/{id}/read
```

#### Mark All as Read
```http
PUT /notifications/read-all
```

### Reports

#### Financial Report
```http
GET /reports/financial?start_date=2025-10-01&end_date=2025-10-31
```

#### Productivity Report
```http
GET /reports/productivity?start_date=2025-10-01&end_date=2025-10-31&project_id=10
```

#### Utilization Report
```http
GET /reports/utilization?start_date=2025-10-01&end_date=2025-10-31
```

## Error Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |
| 422 | Validation Error |
| 500 | Server Error |

## Rate Limiting

- 100 requests per minute per user
- 429 status code returned when limit exceeded
- Reset time included in response headers

## Webhooks

### Payment Webhook (Razorpay)
```http
POST /payments/razorpay/callback
```

### Payment Webhook (Stripe)
```http
POST /payments/stripe/callback
```

## Support

For API support: api-support@onestopcrm.com

