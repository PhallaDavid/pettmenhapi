<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        // Check permission
        if (!$request->user()->can('view employees')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view employees',
            ], 403);
        }

        $employees = Employee::paginate(10);
        return response()->json($employees, 200, [], JSON_UNESCAPED_SLASHES);
    }

    public function store(Request $request)
    {
        // Check permission
        if (!$request->user()->can('create employees')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to create employees',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email',
            'phone' => 'nullable|string|max:20',
            'position' => 'nullable|string|max:255',
            'base_salary' => 'required|numeric|min:0',
            'working_days' => 'nullable|integer|min:1|max:31',
            'overtime_rate' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:active,inactive',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $employee = Employee::create($validated);
        $employee->calculateSalaryPerDay();
        $employee->save();

        return response()->json([
            'success' => true,
            'message' => 'Employee created successfully',
            'employee' => $employee
        ], 201);
    }

    public function show(Request $request, Employee $employee)
    {
        // Check permission
        if (!$request->user()->can('view employees')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view employees',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'employee' => $employee->load('attendances', 'salaries')
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }

    public function update(Request $request, Employee $employee)
    {
        // Check permission
        if (!$request->user()->can('edit employees')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to edit employees',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:employees,email,' . $employee->id,
            'phone' => 'nullable|string|max:20',
            'position' => 'nullable|string|max:255',
            'base_salary' => 'sometimes|numeric|min:0',
            'working_days' => 'nullable|integer|min:1|max:31',
            'overtime_rate' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:active,inactive',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $employee->update($validated);
        $employee->calculateSalaryPerDay();
        $employee->save();

        return response()->json([
            'success' => true,
            'message' => 'Employee updated successfully',
            'employee' => $employee
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }

    public function destroy(Request $request, Employee $employee)
    {
        // Check permission
        if (!$request->user()->can('delete employees')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete employees',
            ], 403);
        }

        $employee->delete();

        return response()->json([
            'success' => true,
            'message' => 'Employee deleted successfully'
        ]);
    }

    /**
     * Activate employee
     */
    public function activate(Request $request, Employee $employee)
    {
        // Check permission
        if (!$request->user()->can('edit employees')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to edit employees',
            ], 403);
        }

        $employee->update(['status' => 'active']);

        return response()->json([
            'success' => true,
            'message' => 'Employee activated successfully',
            'employee' => $employee
        ]);
    }

    /**
     * Deactivate employee
     */
    public function deactivate(Request $request, Employee $employee)
    {
        // Check permission
        if (!$request->user()->can('edit employees')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to edit employees',
            ], 403);
        }

        $employee->update(['status' => 'inactive']);

        return response()->json([
            'success' => true,
            'message' => 'Employee deactivated successfully',
            'employee' => $employee
        ]);
    }
}
