#!/bin/bash

# Employee Management System - API Test Commands
# Replace YOUR_TOKEN_HERE with actual token from login response

BASE_URL="http://127.0.0.1:8000/api"
TOKEN="YOUR_TOKEN_HERE"

echo "=== 1. LOGIN ==="
curl -X POST "$BASE_URL/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "david@gmail.com",
    "password": "12345678"
  }'

echo -e "\n\n=== 2. CREATE EMPLOYEE ==="
curl -X POST "$BASE_URL/employees" \
  -H "Authorization: Bearer $TOKEN" \
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

echo -e "\n\n=== 3. LIST EMPLOYEES ==="
curl -X GET "$BASE_URL/employees" \
  -H "Authorization: Bearer $TOKEN"

echo -e "\n\n=== 4. VIEW EMPLOYEE ==="
curl -X GET "$BASE_URL/employees/1" \
  -H "Authorization: Bearer $TOKEN"

echo -e "\n\n=== 5. CHECK-IN ==="
curl -X POST "$BASE_URL/employees/1/check-in" \
  -H "Authorization: Bearer $TOKEN"

echo -e "\n\n=== 6. CHECK-OUT ==="
curl -X POST "$BASE_URL/employees/1/check-out" \
  -H "Authorization: Bearer $TOKEN"

echo -e "\n\n=== 7. VIEW ATTENDANCE BY MONTH ==="
curl -X GET "$BASE_URL/employees/1/attendance?month=1&year=2026" \
  -H "Authorization: Bearer $TOKEN"

echo -e "\n\n=== 8. LIST ATTENDANCES ==="
curl -X GET "$BASE_URL/attendances?employee_id=1&month=1&year=2026" \
  -H "Authorization: Bearer $TOKEN"

echo -e "\n\n=== 9. GENERATE SALARY ==="
curl -X POST "$BASE_URL/employees/1/generate-salary" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "month": 1,
    "year": 2026
  }'

echo -e "\n\n=== 10. VIEW SALARY HISTORY ==="
curl -X GET "$BASE_URL/salaries?employee_id=1&month=1&year=2026" \
  -H "Authorization: Bearer $TOKEN"

echo -e "\n\n=== 11. VIEW SINGLE SALARY ==="
curl -X GET "$BASE_URL/salaries/1" \
  -H "Authorization: Bearer $TOKEN"

echo -e "\n\n=== 12. UPDATE SALARY BONUS ==="
curl -X PUT "$BASE_URL/salaries/1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "bonus": 500
  }'

echo -e "\n\n=== 13. GET SALARY SLIP ==="
curl -X GET "$BASE_URL/salaries/1/slip" \
  -H "Authorization: Bearer $TOKEN"

echo -e "\n\n=== 14. GET SETTINGS ==="
curl -X GET "$BASE_URL/settings" \
  -H "Authorization: Bearer $TOKEN"

echo -e "\n\n=== 15. UPDATE SETTINGS ==="
curl -X PUT "$BASE_URL/settings" \
  -H "Authorization: Bearer $TOKEN" \
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

echo -e "\n\n=== 16. UPDATE EMPLOYEE ==="
curl -X PUT "$BASE_URL/employees/1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe Updated",
    "position": "Senior Developer",
    "base_salary": 6000
  }'

echo -e "\n\n=== 17. ACTIVATE EMPLOYEE ==="
curl -X POST "$BASE_URL/employees/1/activate" \
  -H "Authorization: Bearer $TOKEN"

echo -e "\n\n=== 18. DEACTIVATE EMPLOYEE ==="
curl -X POST "$BASE_URL/employees/1/deactivate" \
  -H "Authorization: Bearer $TOKEN"

echo -e "\n\nDone!"
