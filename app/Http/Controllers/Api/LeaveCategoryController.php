<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LeaveCategory;
use Illuminate\Http\Request;

class LeaveCategoryController extends Controller
{
    /**
     * Display a listing of leave categories.
     */
    public function index()
    {
        $categories = LeaveCategory::all();
        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * Store a newly created leave category.
     */
    public function store(Request $request)
    {
        if (!$request->user()->can('manage settings')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to manage leave categories'
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|unique:leave_categories,name',
            'color' => 'nullable|string',
            'icon' => 'nullable|string',
            'requires_attachment' => 'boolean',
        ]);

        $category = LeaveCategory::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Leave category created successfully',
            'data' => $category
        ], 201);
    }

    /**
     * Update the specified leave category.
     */
    public function update(Request $request, LeaveCategory $leaveCategory)
    {
        if (!$request->user()->can('manage settings')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to manage leave categories'
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|unique:leave_categories,name,' . $leaveCategory->id,
            'color' => 'nullable|string',
            'icon' => 'nullable|string',
            'requires_attachment' => 'sometimes|boolean',
        ]);

        $leaveCategory->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Leave category updated successfully',
            'data' => $leaveCategory
        ]);
    }

    /**
     * Remove the specified leave category.
     */
    public function destroy(Request $request, LeaveCategory $leaveCategory)
    {
        if (!$request->user()->can('manage settings')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to manage leave categories'
            ], 403);
        }

        $leaveCategory->delete();

        return response()->json([
            'success' => true,
            'message' => 'Leave category deleted successfully'
        ]);
    }
}
