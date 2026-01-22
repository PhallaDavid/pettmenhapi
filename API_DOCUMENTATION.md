# Employee Management System - API Documentation

## Base URL
```
http://127.0.0.1:8000/api
```

## Authentication
All endpoints (except `/login`) require Bearer token authentication:
```
Authorization: Bearer {your_token}
```

---

## 🔐 Authentication

### Login
**POST** `/login`

**Request:**
```json
{
    "email": "david@gmail.com",
    "password": "12345678"
}
```

**Response (200):**
```json
{
    "success": true,
    "access_token": "1|xxxxxxxxxxxx",
    "token_type": "Bearer",
    "user": {
        "id": 1,
        "name": "Super Admin",
        "email": "david@gmail.com",
        "roles": [...],
        "permissions": [...]
    }
}
```

---

## 👥 Employee Management

### 1. List Employees
**GET** `/employees`

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
    "current_page": 1,
    "data": [
        {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "phone": "1234567890",
            "position": "Developer",
            "base_salary": "5000.00",
            "working_days": 26,
            "salary_per_day": "192.31",
            "overtime_rate": "25.00",
            "status": "active",
            "created_at": "2026-01-22T10:00:00.000000Z",
            "updated_at": "2026-01-22T10:00:00.000000Z"
        }
    ],
    "per_page": 10,
    "total": 1
}
```

### 2. Create Employee
**POST** `/employees`

**Request:**
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "1234567890",
    "position": "Developer",
    "base_salary": 5000,
    "working_days": 26,
    "overtime_rate": 25,
    "status": "active"
}
```

**Response (201):**
```json
{
    "success": true,
    "message": "Employee created successfully",
    "employee": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "phone": "1234567890",
        "position": "Developer",
        "base_salary": "5000.00",
        "working_days": 26,
        "salary_per_day": "192.31",
        "overtime_rate": "25.00",
        "status": "active",
        "created_at": "2026-01-22T10:00:00.000000Z",
        "updated_at": "2026-01-22T10:00:00.000000Z"
    }
}
```

### 3. View Employee Details
**GET** `/employees/{id}`

**Response (200):**
```json
{
    "success": true,
    "employee": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "phone": "1234567890",
        "position": "Developer",
        "base_salary": "5000.00",
        "working_days": 26,
        "salary_per_day": "192.31",
        "overtime_rate": "25.00",
        "status": "active",
        "attendances": [...],
        "salaries": [...],
        "created_at": "2026-01-22T10:00:00.000000Z",
        "updated_at": "2026-01-22T10:00:00.000000Z"
    }
}
```

### 4. Update Employee
**PUT** `/employees/{id}`

**Request:**
```json
{
    "name": "John Doe Updated",
    "position": "Senior Developer",
    "base_salary": 6000,
    "overtime_rate": 30
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Employee updated successfully",
    "employee": {
        "id": 1,
        "name": "John Doe Updated",
        "email": "john@example.com",
        "position": "Senior Developer",
        "base_salary": "6000.00",
        "salary_per_day": "230.77",
        ...
    }
}
```

### 5. Delete Employee
**DELETE** `/employees/{id}`

**Response (200):**
```json
{
    "success": true,
    "message": "Employee deleted successfully"
}
```

### 6. Activate Employee
**POST** `/employees/{id}/activate`

**Response (200):**
```json
{
    "success": true,
    "message": "Employee activated successfully",
    "employee": {
        "id": 1,
        "status": "active",
        ...
    }
}
```

### 7. Deactivate Employee
**POST** `/employees/{id}/deactivate`

**Response (200):**
```json
{
    "success": true,
    "message": "Employee deactivated successfully",
    "employee": {
        "id": 1,
        "status": "inactive",
        ...
    }
}
```

---

## ⏰ Attendance Management

### 1. Check-In
**POST** `/employees/{id}/check-in`

**Request:**
```json
{}
```

**Response (201):**
```json
{
    "success": true,
    "message": "Check-in successful",
    "attendance": {
        "id": 1,
        "employee_id": 1,
        "date": "2026-01-22",
        "check_in": "2026-01-22T09:15:00.000000Z",
        "check_out": null,
        "late_minutes": 15,
        "overtime_hours": "0.00",
        "status": "late",
        "employee": {
            "id": 1,
            "name": "John Doe",
            ...
        }
    }
}
```

### 2. Check-Out
**POST** `/employees/{id}/check-out`

**Request:**
```json
{}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Check-out successful",
    "attendance": {
        "id": 1,
        "employee_id": 1,
        "date": "2026-01-22",
        "check_in": "2026-01-22T09:00:00.000000Z",
        "check_out": "2026-01-22T18:30:00.000000Z",
        "late_minutes": 0,
        "overtime_hours": "1.50",
        "status": "present",
        "employee": {...}
    }
}
```

### 3. List Attendances
**GET** `/attendances?employee_id=1&month=1&year=2026`

**Response (200):**
```json
{
    "current_page": 1,
    "data": [
        {
            "id": 1,
            "employee_id": 1,
            "date": "2026-01-22",
            "check_in": "2026-01-22T09:00:00.000000Z",
            "check_out": "2026-01-22T17:00:00.000000Z",
            "late_minutes": 0,
            "overtime_hours": "0.00",
            "status": "present",
            "employee": {
                "id": 1,
                "name": "John Doe",
                ...
            }
        }
    ]
}
```

### 4. Update Attendance
**PUT** `/attendances/{id}`

**Request:**
```json
{
    "check_in": "2026-01-22 09:00:00",
    "check_out": "2026-01-22 17:00:00",
    "status": "present"
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Attendance updated successfully",
    "attendance": {
        "id": 1,
        "date": "2026-01-22",
        "check_in": "2026-01-22T09:00:00.000000Z",
        "check_out": "2026-01-22T17:00:00.000000Z",
        "late_minutes": 0,
        "overtime_hours": "0.00",
        "status": "present",
        ...
    }
}
```

### 5. View Attendance by Employee/Month
**GET** `/employees/{id}/attendance?month=1&year=2026`

**Response (200):**
```json
{
    "success": true,
    "employee": {
        "id": 1,
        "name": "John Doe",
        ...
    },
    "month": 1,
    "year": 2026,
    "attendances": [
        {
            "id": 1,
            "date": "2026-01-01",
            "check_in": "2026-01-01T09:00:00.000000Z",
            "check_out": "2026-01-01T17:00:00.000000Z",
            "status": "present",
            "late_minutes": 0,
            "overtime_hours": "0.00"
        },
        {
            "id": 2,
            "date": "2026-01-02",
            "check_in": null,
            "check_out": null,
            "status": "absent",
            "late_minutes": 0,
            "overtime_hours": "0.00"
        }
    ]
}
```

---

## 💰 Salary Management

### 1. Generate Salary
**POST** `/employees/{id}/generate-salary`

**Request:**
```json
{
    "month": 1,
    "year": 2026
}
```

**Response (201):**
```json
{
    "success": true,
    "message": "Salary generated successfully",
    "salary": {
        "id": 1,
        "employee_id": 1,
        "month": 1,
        "year": 2026,
        "present_days": 20,
        "absent_days": 2,
        "leave_paid_days": 2,
        "leave_unpaid_days": 1,
        "late_days": 3,
        "overtime_pay": "75.00",
        "deduction": "250.00",
        "bonus": "0.00",
        "total_salary": "4825.00",
        "employee": {
            "id": 1,
            "name": "John Doe",
            ...
        }
    }
}
```

### 2. View Salary History
**GET** `/salaries?employee_id=1&month=1&year=2026`

**Response (200):**
```json
{
    "current_page": 1,
    "data": [
        {
            "id": 1,
            "employee_id": 1,
            "month": 1,
            "year": 2026,
            "present_days": 20,
            "absent_days": 2,
            "leave_paid_days": 2,
            "leave_unpaid_days": 1,
            "late_days": 3,
            "overtime_pay": "75.00",
            "deduction": "250.00",
            "bonus": "0.00",
            "total_salary": "4825.00",
            "employee": {...}
        }
    ]
}
```

### 3. View Single Salary
**GET** `/salaries/{id}`

**Response (200):**
```json
{
    "success": true,
    "salary": {
        "id": 1,
        "employee_id": 1,
        "month": 1,
        "year": 2026,
        "present_days": 20,
        "absent_days": 2,
        "leave_paid_days": 2,
        "leave_unpaid_days": 1,
        "late_days": 3,
        "overtime_pay": "75.00",
        "deduction": "250.00",
        "bonus": "0.00",
        "total_salary": "4825.00",
        "employee": {...}
    }
}
```

### 4. Update Salary (Add Bonus)
**PUT** `/salaries/{id}`

**Request:**
```json
{
    "bonus": 500
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Salary updated successfully",
    "salary": {
        "id": 1,
        "bonus": "500.00",
        "total_salary": "5325.00",
        ...
    }
}
```

### 5. Get Salary Slip (JSON)
**GET** `/salaries/{id}/slip`

**Response (200):**
```json
{
    "success": true,
    "salary_slip": {
        "employee": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "position": "Developer"
        },
        "period": {
            "month": 1,
            "year": 2026,
            "month_name": "January"
        },
        "attendance_summary": {
            "present_days": 20,
            "absent_days": 2,
            "leave_paid_days": 2,
            "leave_unpaid_days": 1,
            "late_days": 3
        },
        "salary_breakdown": {
            "base_salary": "5000.00",
            "overtime_pay": "75.00",
            "bonus": "500.00",
            "deduction": "250.00",
            "total_salary": "5325.00"
        },
        "generated_at": "2026-01-22 10:00:00"
    }
}
```

---

## ⚙️ Settings Management

### 1. Get All Settings
**GET** `/settings`

**Response (200):**
```json
[
    {
        "id": 1,
        "key": "late_10_min_penalty",
        "value": "5",
        "description": "Percentage deduction for 10-29 minutes late",
        "created_at": "2026-01-22T10:00:00.000000Z",
        "updated_at": "2026-01-22T10:00:00.000000Z"
    },
    {
        "id": 2,
        "key": "late_30_min_penalty",
        "value": "10",
        "description": "Percentage deduction for 30+ minutes late",
        "created_at": "2026-01-22T10:00:00.000000Z",
        "updated_at": "2026-01-22T10:00:00.000000Z"
    }
]
```

### 2. Update Settings
**PUT** `/settings`

**Request:**
```json
{
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
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Settings updated successfully",
    "settings": [
        {
            "id": 1,
            "key": "late_10_min_penalty",
            "value": "7",
            ...
        },
        {
            "id": 2,
            "key": "late_30_min_penalty",
            "value": "15",
            ...
        }
    ]
}
```

---

## 📝 Test Scenarios

### Complete Flow Example:

1. **Login:**
```bash
POST http://127.0.0.1:8000/api/login
Content-Type: application/json

{
    "email": "david@gmail.com",
    "password": "12345678"
}
```

2. **Create Employee:**
```bash
POST http://127.0.0.1:8000/api/employees
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "1234567890",
    "position": "Developer",
    "base_salary": 5000,
    "working_days": 26,
    "overtime_rate": 25,
    "status": "active"
}
```

3. **Check-In:**
```bash
POST http://127.0.0.1:8000/api/employees/1/check-in
Authorization: Bearer {token}
```

4. **Check-Out:**
```bash
POST http://127.0.0.1:8000/api/employees/1/check-out
Authorization: Bearer {token}
```

5. **Generate Salary:**
```bash
POST http://127.0.0.1:8000/api/employees/1/generate-salary
Authorization: Bearer {token}
Content-Type: application/json

{
    "month": 1,
    "year": 2026
}
```

6. **Get Salary Slip:**
```bash
GET http://127.0.0.1:8000/api/salaries/1/slip
Authorization: Bearer {token}
```

---

## 🔒 Permission Requirements

- **View Employees:** `view employees`
- **Create Employees:** `create employees`
- **Edit Employees:** `edit employees`
- **Delete Employees:** `delete employees`
- **View Attendance:** `view attendance`
- **Manage Attendance:** `manage attendance`
- **View Salaries:** `view salaries`
- **Manage Salaries:** `manage salaries`
- **Manage Settings:** `manage settings`

---

## ⚠️ Error Responses

### 403 Forbidden (No Permission):
```json
{
    "success": false,
    "message": "You do not have permission to view employees"
}
```

### 422 Validation Error:
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": ["The email has already been taken."],
        "base_salary": ["The base salary must be at least 0."]
    }
}
```

### 404 Not Found:
```json
{
    "message": "No query results for model [App\\Models\\Employee] 999"
}
```
