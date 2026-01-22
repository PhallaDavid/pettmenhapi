<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Salary;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SalaryService
{
    /**
     * Generate salary for an employee for a specific month
     */
    public function generateSalary(Employee $employee, int $month, int $year): Salary
    {
        return DB::transaction(function () use ($employee, $month, $year) {
            // Check if salary already exists
            $existingSalary = Salary::where('employee_id', $employee->id)
                ->where('month', $month)
                ->where('year', $year)
                ->first();

            if ($existingSalary) {
                return $existingSalary;
            }

            // Get attendances for the month
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth();

            $attendances = Attendance::where('employee_id', $employee->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->get();

            // Calculate days
            $presentDays = $attendances->where('status', 'present')->count();
            $lateDays = $attendances->where('status', 'late')->count();
            $absentDays = $attendances->where('status', 'absent')->count();
            $leavePaidDays = $attendances->where('status', 'leave_paid')->count();
            $leaveUnpaidDays = $attendances->where('status', 'leave_unpaid')->count();

            // Calculate overtime pay
            $totalOvertimeHours = $attendances->sum('overtime_hours');
            $overtimePay = $totalOvertimeHours * $employee->overtime_rate;

            // Calculate deductions
            $deduction = $this->calculateDeduction(
                $employee,
                $absentDays,
                $leaveUnpaidDays,
                $lateDays,
                $attendances
            );

            // Calculate total salary
            $totalSalary = $employee->base_salary - $deduction + $overtimePay;

            // Create salary record
            return Salary::create([
                'employee_id' => $employee->id,
                'month' => $month,
                'year' => $year,
                'present_days' => $presentDays,
                'absent_days' => $absentDays,
                'leave_paid_days' => $leavePaidDays,
                'leave_unpaid_days' => $leaveUnpaidDays,
                'late_days' => $lateDays,
                'overtime_pay' => $overtimePay,
                'deduction' => $deduction,
                'bonus' => 0, // Can be set manually later
                'total_salary' => $totalSalary,
            ]);
        });
    }

    /**
     * Calculate deduction based on absent days, unpaid leave, and late penalties
     */
    protected function calculateDeduction(
        Employee $employee,
        int $absentDays,
        int $leaveUnpaidDays,
        int $lateDays,
        $attendances
    ): float {
        $deduction = 0;

        // Deduction for absent days
        $deduction += $absentDays * $employee->salary_per_day;

        // Deduction for unpaid leave days
        $deduction += $leaveUnpaidDays * $employee->salary_per_day;

        // Late penalty deduction
        $late10MinPenalty = (float) Setting::getValue('late_10_min_penalty', 5);
        $late30MinPenalty = (float) Setting::getValue('late_30_min_penalty', 10);

        foreach ($attendances->where('status', 'late') as $attendance) {
            $lateMinutes = $attendance->late_minutes;
            $dailySalary = $employee->salary_per_day;

            if ($lateMinutes >= 30) {
                // 30+ minutes late - higher penalty
                $deduction += ($dailySalary * $late30MinPenalty) / 100;
            } elseif ($lateMinutes >= 10) {
                // 10-29 minutes late - lower penalty
                $deduction += ($dailySalary * $late10MinPenalty) / 100;
            }
        }

        return round($deduction, 2);
    }

    /**
     * Generate salaries for all active employees for a specific month
     */
    public function generateSalariesForMonth(int $month, int $year): array
    {
        $employees = Employee::where('status', 'active')->get();
        $generated = [];

        foreach ($employees as $employee) {
            try {
                $salary = $this->generateSalary($employee, $month, $year);
                $generated[] = $salary;
            } catch (\Exception $e) {
                \Log::error("Failed to generate salary for employee {$employee->id}: " . $e->getMessage());
            }
        }

        return $generated;
    }
}
