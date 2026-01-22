<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Get all settings
     */
    public function index(Request $request)
    {
        // Check permission
        if (!$request->user()->can('manage settings')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to manage settings',
            ], 403);
        }

        $settings = Setting::all();
        return response()->json($settings, 200, [], JSON_UNESCAPED_SLASHES);
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        // Check permission
        if (!$request->user()->can('manage settings')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to manage settings',
            ], 403);
        }

        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'required|string',
        ]);

        foreach ($validated['settings'] as $settingData) {
            Setting::setValue(
                $settingData['key'],
                $settingData['value']
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully',
            'settings' => Setting::all()
        ]);
    }
}
