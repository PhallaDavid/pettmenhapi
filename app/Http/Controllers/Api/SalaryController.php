<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Salary;
use App\Services\SalaryService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SalaryController extends Controller
{
    protected $salaryService;

    public function __construct(SalaryService $salaryService)
    {
        $this->salaryService = $salaryService;
    }

    /**
     * Generate salary for employee by month
     */
    public function generate(Request $request, Employee $employee)
    {
        // Check permission
        if (!$request->user()->can('manage salaries')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to manage salaries',
            ], 403);
        }

        $validated = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:3000',
        ]);

        try {
            $salary = $this->salaryService->generateSalary(
                $employee,
                $validated['month'],
                $validated['year']
            );

            return response()->json([
                'success' => true,
                'message' => 'Salary generated successfully',
                'salary' => $salary->load('employee')
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate salary: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * View salary history
     */
    public function history(Request $request, Employee $employee = null)
    {
        // Check permission
        if (!$request->user()->can('view salaries')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view salaries',
            ], 403);
        }

        $query = Salary::with('employee');

        if ($employee) {
            $query->where('employee_id', $employee->id);
        }

        if ($request->month) {
            $query->where('month', $request->month);
        }

        if ($request->year) {
            $query->where('year', $request->year);
        }

        $salaries = $query->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->paginate(10);

        return response()->json($salaries, 200, [], JSON_UNESCAPED_SLASHES);
    }

    /**
     * View single salary
     */
    public function show(Request $request, Salary $salary)
    {
        // Check permission
        if (!$request->user()->can('view salaries')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view salaries',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'salary' => $salary->load('employee')
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }

    /**
     * Update salary (for bonus, manual adjustments)
     */
    public function update(Request $request, Salary $salary)
    {
        // Check permission
        if (!$request->user()->can('manage salaries')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to manage salaries',
            ], 403);
        }

        $validated = $request->validate([
            'bonus' => 'nullable|numeric|min:0',
        ]);

        $salary->update($validated);

        // Recalculate total salary
        $salary->total_salary = $salary->employee->base_salary 
            - $salary->deduction 
            + $salary->overtime_pay 
            + $salary->bonus;
        $salary->save();

        return response()->json([
            'success' => true,
            'message' => 'Salary updated successfully',
            'salary' => $salary->load('employee')
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }

    /**
     * Get salary slip data (JSON format)
     */
    public function slip(Request $request, Salary $salary)
    {
        // Check permission
        if (!$request->user()->can('view salaries')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view salaries',
            ], 403);
        }

        $salary->load('employee');
        
        return response()->json([
            'success' => true,
            'salary_slip' => [
                'employee' => [
                    'id' => $salary->employee->id,
                    'name' => $salary->employee->name,
                    'email' => $salary->employee->email,
                    'position' => $salary->employee->position,
                ],
                'period' => [
                    'month' => $salary->month,
                    'year' => $salary->year,
                    'month_name' => date('F', mktime(0, 0, 0, $salary->month, 1)),
                ],
                'attendance_summary' => [
                    'present_days' => $salary->present_days,
                    'absent_days' => $salary->absent_days,
                    'leave_paid_days' => $salary->leave_paid_days,
                    'leave_unpaid_days' => $salary->leave_unpaid_days,
                    'late_days' => $salary->late_days,
                ],
                'salary_breakdown' => [
                    'base_salary' => number_format($salary->employee->base_salary, 2),
                    'overtime_pay' => number_format($salary->overtime_pay, 2),
                    'bonus' => number_format($salary->bonus, 2),
                    'deduction' => number_format($salary->deduction, 2),
                    'total_salary' => number_format($salary->total_salary, 2),
                ],
                'generated_at' => $salary->created_at->format('Y-m-d H:i:s'),
            ]
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }
}
