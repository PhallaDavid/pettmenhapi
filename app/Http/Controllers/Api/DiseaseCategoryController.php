<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DiseaseCategory;
use Illuminate\Http\Request;

class DiseaseCategoryController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->user()->can('view disease categories')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view disease categories',
            ], 403);
        }

        $items = DiseaseCategory::paginate(10);
        return response()->json($items, 200, [], JSON_UNESCAPED_SLASHES);
    }

    public function store(Request $request)
    {
        if (!$request->user()->can('create disease categories')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to create disease categories',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:disease_categories,name',
            'price' => 'nullable|numeric|min:0',
            'price_promotion' => 'nullable|numeric|min:0',
            'promotion_percent' => 'nullable|numeric|min:0|max:100',
            'date_start_promotion' => 'nullable|date',
            'date_end_promotion' => 'nullable|date|after_or_equal:date_start_promotion',
            'price_after_promotion' => 'nullable|numeric|min:0',
            'promotion_note' => 'nullable|string',
        ]);

        $item = DiseaseCategory::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Disease category created successfully',
            'disease_category' => $item,
        ], 201, [], JSON_UNESCAPED_SLASHES);
    }

    public function show(Request $request, DiseaseCategory $diseaseCategory)
    {
        if (!$request->user()->can('view disease categories')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view disease categories',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'disease_category' => $diseaseCategory,
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }

    public function update(Request $request, DiseaseCategory $diseaseCategory)
    {
        if (!$request->user()->can('edit disease categories')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to edit disease categories',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:disease_categories,name,' . $diseaseCategory->id,
            'price' => 'nullable|numeric|min:0',
            'price_promotion' => 'nullable|numeric|min:0',
            'promotion_percent' => 'nullable|numeric|min:0|max:100',
            'date_start_promotion' => 'nullable|date',
            'date_end_promotion' => 'nullable|date|after_or_equal:date_start_promotion',
            'price_after_promotion' => 'nullable|numeric|min:0',
            'promotion_note' => 'nullable|string',
        ]);

        $diseaseCategory->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Disease category updated successfully',
            'disease_category' => $diseaseCategory,
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }

    public function destroy(Request $request, DiseaseCategory $diseaseCategory)
    {
        if (!$request->user()->can('delete disease categories')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete disease categories',
            ], 403);
        }

        $diseaseCategory->delete();

        return response()->json([
            'success' => true,
            'message' => 'Disease category deleted successfully',
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }
}

