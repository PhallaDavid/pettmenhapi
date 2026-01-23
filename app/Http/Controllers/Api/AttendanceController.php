<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Setting;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Check-in (Cambodia Time)
     */
    public function checkIn(Request $request, Employee $employee)
    {
        // Check permission
        if (!$request->user()->can('manage attendance')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to manage attendance',
            ], 403);
        }

        // Get current date in Cambodia timezone (date only, no time component)
        $today = Carbon::now('Asia/Phnom_Penh')->toDateString();

        // Check if already checked in today
        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        if ($attendance && $attendance->check_in) {
            return response()->json([
                'success' => false,
                'message' => 'Employee already checked in today',
            ], 422);
        }

        if (!$attendance) {
            $attendance = Attendance::create([
                'employee_id' => $employee->id,
                'date' => $today,
                'check_in' => Carbon::now('Asia/Phnom_Penh'),
            ]);
        } else {
            $attendance->update(['check_in' => Carbon::now('Asia/Phnom_Penh')]);
        }

        $attendance->calculateLateMinutes();
        $attendance->determineStatus();
        $attendance->save();

        return response()->json([
            'success' => true,
            'message' => 'Check-in successful',
            'attendance' => $attendance->load('employee')
        ], 201);
    }

    /**
     * Check-out (Cambodia Time)
     */
    public function checkOut(Request $request, Employee $employee)
    {
        // Check permission
        if (!$request->user()->can('manage attendance')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to manage attendance',
            ], 403);
        }

        $today = Carbon::now('Asia/Phnom_Penh')->toDateString();

        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        if (!$attendance || !$attendance->check_in) {
            return response()->json([
                'success' => false,
                'message' => 'Employee must check in first',
            ], 422);
        }

        if ($attendance->check_out) {
            return response()->json([
                'success' => false,
                'message' => 'Employee already checked out today',
            ], 422);
        }

        $attendance->update(['check_out' => Carbon::now('Asia/Phnom_Penh')]);
        $attendance->calculateOvertimeHours();
        $attendance->determineStatus();
        $attendance->save();

        return response()->json([
            'success' => true,
            'message' => 'Check-out successful',
            'attendance' => $attendance->load('employee')
        ]);
    }

    /**
     * Update attendance
     */
    public function update(Request $request, Attendance $attendance)
    {
        // Check permission
        if (!$request->user()->can('manage attendance')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to manage attendance',
            ], 403);
        }

        $validated = $request->validate([
            'date' => 'sometimes|date',
            'check_in' => 'nullable|date',
            'check_out' => 'nullable|date',
            'status' => 'nullable|in:present,late,absent,leave_paid,leave_unpaid',
        ]);

        $attendance->update($validated);
        $attendance->calculateLateMinutes();
        $attendance->calculateOvertimeHours();
        $attendance->determineStatus();
        $attendance->save();

        return response()->json([
            'success' => true,
            'message' => 'Attendance updated successfully',
            'attendance' => $attendance->load('employee')
        ]);
    }

    /**
     * View attendance by employee and month
     */
    public function byEmployeeMonth(Request $request, Employee $employee)
    {
        // Check permission
        if (!$request->user()->can('view attendance')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view attendance',
            ], 403);
        }

        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'employee' => $employee,
            'month' => $month,
            'year' => $year,
            'attendances' => $attendances
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }

    /**
     * List all attendances
     */
    public function index(Request $request)
    {
        // Check permission
        if (!$request->user()->can('view attendance')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view attendance',
            ], 403);
        }

        $attendances = Attendance::with('employee')
            ->when($request->employee_id, function ($query, $employeeId) {
                return $query->where('employee_id', $employeeId);
            })
            ->when($request->date, function ($query, $date) {
                return $query->whereDate('date', $date);
            })
            ->when($request->month && !$request->date, function ($query) use ($request) {
                $month = $request->month;
                $year = $request->year ?? now()->year;
                $startDate = Carbon::create($year, $month, 1)->startOfMonth();
                $endDate = Carbon::create($year, $month, 1)->endOfMonth();
                return $query->whereBetween('date', [$startDate, $endDate]);
            })
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(20);

        return response()->json($attendances, 200, [], JSON_UNESCAPED_SLASHES);
    }

    /**
     * Scan QR for attendance (Smart Check-in/out)
     */
    public function scanQr(Request $request)
    {
        $validated = $request->validate([
            'qr_code' => 'required|string|exists:employees,qr_code',
        ]);

        $employee = Employee::where('qr_code', $validated['qr_code'])->first();
        $today = Carbon::now('Asia/Phnom_Penh')->toDateString();

        // Find today's attendance
        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        // 1. If no attendance yet today -> Perform Check-in
        if (!$attendance) {
            $attendance = Attendance::create([
                'employee_id' => $employee->id,
                'date' => $today,
                'check_in' => Carbon::now('Asia/Phnom_Penh'),
            ]);
            
            $attendance->calculateLateMinutes();
            $attendance->determineStatus();
            $attendance->save();

            // Send Telegram Alert
            TelegramService::sendAttendanceAlert($attendance, 'check_in');

            return response()->json([
                'success' => true,
                'type' => 'check_in',
                'message' => 'Check-in successful via QR',
                'attendance' => $attendance->load('employee')
            ], 201);
        }

        // 2. If already checked in but NOT checked out -> Perform Check-out
        if (!$attendance->check_out) {
            $attendance->update([
                'check_out' => Carbon::now('Asia/Phnom_Penh')
            ]);
            
            $attendance->calculateOvertimeHours();
            $attendance->determineStatus();
            $attendance->save();

            // Send Telegram Alert
            TelegramService::sendAttendanceAlert($attendance, 'check_out');

            return response()->json([
                'success' => true,
                'type' => 'check_out',
                'message' => 'Check-out successful via QR',
                'attendance' => $attendance->load('employee')
            ]);
        }

        // 3. If already both checked in and out
        return response()->json([
            'success' => false,
            'message' => 'Employee has already completed attendance (check-in & out) for today',
            'attendance' => $attendance->load('employee')
        ], 422);
    }

    /**
     * Scan Company-Wide Fixed QR for attendance
     * (All employees scan the SAME QR on the wall)
     */
    public function scanCompanyQr(Request $request)
    {
        $validated = $request->validate([
            'qr_token' => 'required|string',
        ]);

        $masterToken = Setting::getValue('company_attendance_qr', 'PettMenh-Office-Location-1');

        if ($validated['qr_token'] !== $masterToken) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid QR Code for this office',
            ], 422);
        }

        // Get the authenticated user's employee record
        $user = $request->user();
        $employee = Employee::where('user_id', $user->id)
            ->orWhere('email', $user->email) // Fallback to email matching
            ->first();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Your user account is not linked to an employee record',
            ], 404);
        }

        $today = Carbon::now('Asia/Phnom_Penh')->toDateString();

        // Find today's attendance
        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        // 1. Check-in logic
        if (!$attendance) {
            $attendance = Attendance::create([
                'employee_id' => $employee->id,
                'date' => $today,
                'check_in' => Carbon::now('Asia/Phnom_Penh'),
            ]);
            
            $attendance->calculateLateMinutes();
            $attendance->determineStatus();
            $attendance->save();

            // Send Telegram Alert
            TelegramService::sendAttendanceAlert($attendance, 'check_in');

            return response()->json([
                'success' => true,
                'type' => 'check_in',
                'message' => 'Office Check-in successful',
                'attendance' => $attendance->load('employee')
            ], 201);
        }

        // 2. Check-out logic
        if (!$attendance->check_out) {
            $attendance->update([
                'check_out' => Carbon::now('Asia/Phnom_Penh')
            ]);
            
            $attendance->calculateOvertimeHours();
            $attendance->determineStatus();
            $attendance->save();

            // Send Telegram Alert
            TelegramService::sendAttendanceAlert($attendance, 'check_out');

            return response()->json([
                'success' => true,
                'type' => 'check_out',
                'message' => 'Office Check-out successful',
                'attendance' => $attendance->load('employee')
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'You already scanned today (check-in & out complete)',
        ], 422);
    }

    /**
     * Get attendance for the authenticated user (Self)
     */
    public function myAttendance(Request $request)
    {
        $user = $request->user();
        $employee = Employee::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Your user account is not linked to an employee record',
            ], 404);
        }

        $query = Attendance::where('employee_id', $employee->id);

        // Filter by month/year if provided
        if ($request->has('month')) {
            $year = $request->year ?? now()->year;
            $month = $request->month;
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth();
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        $attendances = $query->orderBy('date', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->name,
                'position' => $employee->position
            ],
            'attendances' => $attendances
        ]);
    }

    /**
     * Get attendance statistics for the authenticated user
     */
    public function myStats(Request $request)
    {
        $user = $request->user();
        $employee = Employee::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Your user account is not linked to an employee record',
            ], 404);
        }

        $year = $request->year ?? Carbon::now('Asia/Phnom_Penh')->year;
        $month = $request->month ?? Carbon::now('Asia/Phnom_Penh')->month;

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $stats = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->selectRaw('
                COUNT(*) as total_records,
                COUNT(CASE WHEN status = "present" THEN 1 END) as present_count,
                COUNT(CASE WHEN status = "late" THEN 1 END) as late_count,
                COUNT(CASE WHEN status = "absent" THEN 1 END) as absent_count,
                COUNT(CASE WHEN status IN ("leave_paid", "leave_unpaid") THEN 1 END) as leave_count,
                SUM(late_minutes) as total_late_minutes,
                SUM(overtime_hours) as total_overtime_hours
            ')
            ->first();

        return response()->json([
            'success' => true,
            'year' => (int)$year,
            'month' => (int)$month,
            'employee' => $employee->name,
            'statistics' => [
                'present' => (int)($stats->present_count ?? 0),
                'late' => (int)($stats->late_count ?? 0),
                'absent' => (int)($stats->absent_count ?? 0),
                'leave' => (int)($stats->leave_count ?? 0),
                'total_records' => (int)($stats->total_records ?? 0),
                'total_late_minutes' => (int)($stats->total_late_minutes ?? 0),
                'total_overtime_hours' => (float)($stats->total_overtime_hours ?? 0)
            ]
        ]);
    }

    /**
     * Get overall attendance statistics for a specific month/year
     */
    public function overallStats(Request $request)
    {
        // Check permission
        if (!$request->user()->can('view attendance')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view attendance stats',
            ], 403);
        }

        $year = $request->year ?? Carbon::now('Asia/Phnom_Penh')->year;
        $month = $request->month ?? Carbon::now('Asia/Phnom_Penh')->month;

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $query = Attendance::whereBetween('date', [$startDate, $endDate]);

        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        $stats = $query->selectRaw('
                COUNT(*) as total_records,
                COUNT(CASE WHEN status = "present" THEN 1 END) as present_count,
                COUNT(CASE WHEN status = "late" THEN 1 END) as late_count,
                COUNT(CASE WHEN status = "absent" THEN 1 END) as absent_count,
                COUNT(CASE WHEN status IN ("leave_paid", "leave_unpaid") THEN 1 END) as leave_count,
                SUM(late_minutes) as total_late_minutes,
                SUM(overtime_hours) as total_overtime_hours
            ')
            ->first();

        return response()->json([
            'success' => true,
            'year' => (int)$year,
            'month' => (int)$month,
            'scope' => $request->has('employee_id') ? 'Single Employee' : 'All Employees',
            'statistics' => [
                'present' => (int)($stats->present_count ?? 0),
                'late' => (int)($stats->late_count ?? 0),
                'absent' => (int)($stats->absent_count ?? 0),
                'leave' => (int)($stats->leave_count ?? 0),
                'total_records' => (int)($stats->total_records ?? 0),
                'total_late_minutes' => (int)($stats->total_late_minutes ?? 0),
                'total_overtime_hours' => (float)($stats->total_overtime_hours ?? 0)
            ]
        ]);
    }

    /**
     * Delete attendance record
     */
    public function destroy(Attendance $attendance)
    {
        $attendance->delete();

        return response()->json([
            'success' => true,
            'message' => 'Attendance record deleted successfully'
        ]);
    }
}
