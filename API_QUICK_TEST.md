# 🚀 Quick API Test Guide

## Base URL
```
http://127.0.0.1:8000/api
```

---

## 📋 Step-by-Step Test Flow

### Step 1: Login
```bash
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "david@gmail.com",
    "password": "12345678"
  }'
```

**Save the `access_token` from response!**

---

### Step 2: Create Employee
```bash
curl -X POST http://127.0.0.1:8000/api/employees \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "1234567890",
    "position": "Developer",
    "base_salary": 5000,
    "working_days": 26,
    "overtime_rate": 25,
    "status": "active"
  }'
```

---

### Step 3: Check-In
```bash
curl -X POST http://127.0.0.1:8000/api/employees/1/check-in \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

### Step 4: Check-Out
```bash
curl -X POST http://127.0.0.1:8000/api/employees/1/check-out \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

### Step 5: View Attendance by Month
```bash
curl -X GET "http://127.0.0.1:8000/api/employees/1/attendance?month=1&year=2026" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

### Step 6: Generate Salary
```bash
curl -X POST http://127.0.0.1:8000/api/employees/1/generate-salary \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "month": 1,
    "year": 2026
  }'
```

---

### Step 7: Get Salary Slip
```bash
curl -X GET http://127.0.0.1:8000/api/salaries/1/slip \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

### Step 8: Update Salary Bonus
```bash
curl -X PUT http://127.0.0.1:8000/api/salaries/1 \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "bonus": 500
  }'
```

---

### Step 9: Get Settings
```bash
curl -X GET http://127.0.0.1:8000/api/settings \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

### Step 10: Update Settings
```bash
curl -X PUT http://127.0.0.1:8000/api/settings \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "settings": [
      {
        "key": "late_10_min_penalty",
        "value": "7"
      },
      {
        "key": "late_30_min_penalty",
        "value": "15"
      }
    ]
  }'
```

---

## 📝 Postman Collection JSON

Save this as `Employee_Management.postman_collection.json`:

```json
{
  "info": {
    "name": "Employee Management API",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "Login",
      "request": {
        "method": "POST",
        "header": [{"key": "Content-Type", "value": "application/json"}],
        "body": {
          "mode": "raw",
          "raw": "{\n  \"email\": \"david@gmail.com\",\n  \"password\": \"12345678\"\n}"
        },
        "url": {
          "raw": "{{base_url}}/login",
          "host": ["{{base_url}}"],
          "path": ["login"]
        }
      }
    },
    {
      "name": "Create Employee",
      "request": {
        "method": "POST",
        "header": [
          {"key": "Authorization", "value": "Bearer {{token}}"},
          {"key": "Content-Type", "value": "application/json"}
        ],
        "body": {
          "mode": "raw",
          "raw": "{\n  \"name\": \"John Doe\",\n  \"email\": \"john@example.com\",\n  \"phone\": \"1234567890\",\n  \"position\": \"Developer\",\n  \"base_salary\": 5000,\n  \"working_days\": 26,\n  \"overtime_rate\": 25,\n  \"status\": \"active\"\n}"
        },
        "url": {
          "raw": "{{base_url}}/employees",
          "host": ["{{base_url}}"],
          "path": ["employees"]
        }
      }
    },
    {
      "name": "Check-In",
      "request": {
        "method": "POST",
        "header": [{"key": "Authorization", "value": "Bearer {{token}}"}],
        "url": {
          "raw": "{{base_url}}/employees/1/check-in",
          "host": ["{{base_url}}"],
          "path": ["employees", "1", "check-in"]
        }
      }
    },
    {
      "name": "Check-Out",
      "request": {
        "method": "POST",
        "header": [{"key": "Authorization", "value": "Bearer {{token}}"}],
        "url": {
          "raw": "{{base_url}}/employees/1/check-out",
          "host": ["{{base_url}}"],
          "path": ["employees", "1", "check-out"]
        }
      }
    },
    {
      "name": "Generate Salary",
      "request": {
        "method": "POST",
        "header": [
          {"key": "Authorization", "value": "Bearer {{token}}"},
          {"key": "Content-Type", "value": "application/json"}
        ],
        "body": {
          "mode": "raw",
          "raw": "{\n  \"month\": 1,\n  \"year\": 2026\n}"
        },
        "url": {
          "raw": "{{base_url}}/employees/1/generate-salary",
          "host": ["{{base_url}}"],
          "path": ["employees", "1", "generate-salary"]
        }
      }
    }
  ],
  "variable": [
    {"key": "base_url", "value": "http://127.0.0.1:8000/api"},
    {"key": "token", "value": ""}
  ]
}
```

---

## 🔑 All Endpoints Summary

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/login` | Login |
| GET | `/employees` | List employees |
| POST | `/employees` | Create employee |
| GET | `/employees/{id}` | View employee |
| PUT | `/employees/{id}` | Update employee |
| DELETE | `/employees/{id}` | Delete employee |
| POST | `/employees/{id}/activate` | Activate employee |
| POST | `/employees/{id}/deactivate` | Deactivate employee |
| POST | `/employees/{id}/check-in` | Check-in |
| POST | `/employees/{id}/check-out` | Check-out |
| GET | `/attendances` | List attendances |
| PUT | `/attendances/{id}` | Update attendance |
| GET | `/employees/{id}/attendance` | Attendance by month |
| POST | `/employees/{id}/generate-salary` | Generate salary |
| GET | `/salaries` | Salary history |
| GET | `/salaries/{id}` | View salary |
| PUT | `/salaries/{id}` | Update salary |
| GET | `/salaries/{id}/slip` | Get salary slip |
| GET | `/settings` | Get settings |
| PUT | `/settings` | Update settings |

---

## ⚠️ Important Notes

1. **Replace `YOUR_TOKEN_HERE`** with actual token from login
2. **Replace `{id}`** with actual IDs (1, 2, etc.)
3. All endpoints except `/login` require authentication
4. Use `Bearer` token in Authorization header
5. Content-Type should be `application/json` for POST/PUT requests
