# Attendance System - Cambodia Timezone Guide

## Overview

This attendance system is configured to use **Cambodia Time (Asia/Phnom_Penh, UTC+7)** for all check-in and check-out operations.

## Configuration

### 1. Application Timezone

The application timezone is set in `config/app.php`:

```php
'timezone' => 'Asia/Phnom_Penh',
```

### 2. System Settings (API Configurable)

The system uses dynamic settings stored in the database. These can be managed via the Settings API.

| Key                      | Default    | Description                                  |
| ------------------------ | ---------- | -------------------------------------------- |
| `work_start_time`        | `09:00:00` | Official work start time                     |
| `work_end_time`          | `17:00:00` | Official work end time                       |
| `late_threshold_minutes` | `0`        | Grace period before being marked "late"      |
| `working_days_per_month` | `26`       | Standard working days for salary calculation |

### 3. How It Works

#### Check-In Process

- **Endpoint**: `POST /api/employees/{employee}/check-in`
- **Time Recorded**: Current Cambodia time (UTC+7)
- **Late Calculation**: Compares check-in time against 9:00 AM Cambodia time
- **Example Response**:

```json
{
    "success": true,
    "message": "Check-in successful",
    "attendance": {
        "employee_id": 1,
        "date": "2026-01-23T00:00:00.000000Z",
        "check_in": "2026-01-23T06:19:43.000000Z",
        "late_minutes": 0,
        "status": "present",
        "employee": {
            "id": 1,
            "name": "John Doe",
            ...
        }
    }
}
```

#### Check-Out Process

- **Endpoint**: `POST /api/employees/{employee}/check-out`
- **Time Recorded**: Current Cambodia time (UTC+7)
- **Overtime Calculation**: Compares check-out time against 5:00 PM Cambodia time
- **Example Response**:

```json
{
    "success": true,
    "message": "Check-out successful",
    "attendance": {
        "employee_id": 1,
        "check_in": "2026-01-23T06:19:43.000000Z",
        "check_out": "2026-01-23T10:30:00.000000Z",
        "overtime_hours": 0.5,
        "status": "present"
    }
}
```

## Work Schedule

The system uses the following default work schedule (Cambodia Time):

- **Work Start**: 9:00 AM
- **Work End**: 5:00 PM (17:00)
- **Total Hours**: 8 hours

### Late Minutes Calculation

- If employee checks in **after 9:00 AM**, late minutes are calculated
- Example: Check-in at 9:15 AM = 15 late minutes

### Overtime Hours Calculation

- If employee checks out **after 5:00 PM**, overtime hours are calculated
- Example: Check-out at 6:30 PM = 1.5 overtime hours

## API Endpoints

### Test Timezone

```bash
GET /api/time-check
```

Returns current Cambodia time and timezone configuration:

```json
{
    "success": true,
    "current_time_cambodia": "2026-01-23 13:22:28",
    "timezone": "Asia/Phnom_Penh",
    "server_time": "2026-01-23 13:22:28"
}
```

### QR Scan Attendance (Smart Logic)

- **Endpoint**: `POST /api/attendances/scan`
- **Logic**: Automatically decides between check-in and check-out.
- **Payload**: `{"qr_code": "employee-unique-uuid"}`

1. If no record today -> **Check-in**.
2. If already checked-in -> **Check-out**.
3. If both done -> **Error (Already completed)**.

### Central Office QR (One QR for All)

Instead of each employee having a QR, you can print **ONE** QR code on the wall and every employee scans it with their phone while logged in.

- **Endpoint**: `POST /api/attendances/scan-office`
- **Payload**: `{"qr_token": "PettMenh-Office-Location-1"}`
- **Setup**: Link employees to users by updating the `user_id` field in the `employees` table.

### Check-In

```bash
POST /api/employees/{employee_id}/check-in
Authorization: Bearer {token}
```

### Check-Out

```bash
POST /api/employees/{employee_id}/check-out
Authorization: Bearer {token}
```

### View Attendance

```bash
GET /api/employees/{employee_id}/attendance?month=1&year=2026
Authorization: Bearer {token}
```

### List All Attendances

```bash
GET /api/attendances?employee_id={id}&month={month}&year={year}
Authorization: Bearer {token}
```

### Delete Attendance Record

```bash
DELETE /api/attendances/{attendance_id}
Authorization: Bearer {token}
```

## Attendance Status

The system automatically determines attendance status:

- **present**: Checked in and checked out on time
- **late**: Checked in after 9:00 AM
- **absent**: No check-in record
- **leave_paid**: Paid leave (manually set)
- **leave_unpaid**: Unpaid leave (manually set)

## Database Storage

All timestamps are stored in **UTC format** in the database but are automatically converted to Cambodia time when:

- Displaying to users
- Calculating late minutes
- Calculating overtime hours
- Comparing with work schedule times

## Important Notes

1. **Timezone Consistency**: All time-related operations use Cambodia timezone (Asia/Phnom_Penh)
2. **Database Format**: Timestamps are stored in UTC but displayed in Cambodia time
3. **Work Hours**: Default work hours are 9:00 AM - 5:00 PM Cambodia time
4. **Automatic Calculations**: Late minutes and overtime hours are calculated automatically
5. **Status Updates**: Attendance status is determined automatically based on check-in/out times

## Testing

To test the timezone configuration:

1. **Check current time**:

```bash
curl http://127.0.0.1:8000/api/time-check
```

2. **Perform check-in** (requires authentication):

```bash
curl -X POST http://127.0.0.1:8000/api/employees/1/check-in \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

3. **Verify the timestamp** in the response matches Cambodia time (UTC+7)

## Troubleshooting

### Issue: Times are showing in UTC

**Solution**: Clear Laravel cache

```bash
php artisan config:clear
php artisan cache:clear
```

### Issue: Late minutes calculated incorrectly

**Solution**: Verify the timezone in `config/app.php` is set to `Asia/Phnom_Penh`

### Issue: Check-in/out not working

**Solution**:

1. Ensure user has `manage attendance` permission
2. Verify employee exists and is active
3. Check that employee hasn't already checked in/out for the day

### Issue: Old records showing wrong date (e.g., "2026-01-22T17:00:00.000000Z" instead of "2026-01-23")

**Solution**: This happens with records created before the timezone fix. You need to delete the old record and create a new one.

**Delete old attendance record**:

```bash
curl -X DELETE http://127.0.0.1:8000/api/attendances/{attendance_id} \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Then create a new check-in**:

```bash
curl -X POST http://127.0.0.1:8000/api/employees/{employee_id}/check-in \
  -H "Authorization: Bearer YOUR_TOKEN"
```

The new record will have the correct date in Cambodia timezone.

## Example Workflow

1. **Employee arrives at 8:45 AM** (Cambodia time)
    - Check-in API call
    - System records: 8:45 AM Cambodia time
    - Late minutes: 0 (arrived early)
    - Status: "present"

2. **Employee leaves at 6:15 PM** (Cambodia time)
    - Check-out API call
    - System records: 6:15 PM Cambodia time
    - Overtime hours: 1.25 hours (75 minutes / 60)
    - Status: "present"

3. **System calculates salary**
    - Base salary: Based on working days
    - Overtime pay: 1.25 hours × overtime rate
    - Deductions: Based on late minutes/absences

## Support

For any issues or questions about the attendance system, please contact the development team.
