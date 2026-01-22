<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Check-in
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

        $today = Carbon::today();

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
                'check_in' => now(),
            ]);
        } else {
            $attendance->update(['check_in' => now()]);
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
     * Check-out
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

        $today = Carbon::today();

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

        $attendance->update(['check_out' => now()]);
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
            ->when($request->month, function ($query, $month) use ($request) {
                $year = $request->year ?? now()->year;
                $startDate = Carbon::create($year, $month, 1)->startOfMonth();
                $endDate = Carbon::create($year, $month, 1)->endOfMonth();
                return $query->whereBetween('date', [$startDate, $endDate]);
            })
            ->orderBy('date', 'desc')
            ->paginate(10);

        return response()->json($attendances, 200, [], JSON_UNESCAPED_SLASHES);
    }
}
