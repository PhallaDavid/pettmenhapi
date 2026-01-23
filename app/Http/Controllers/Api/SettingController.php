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

    /**
     * Update Telegram settings
     */
    public function updateTelegram(Request $request)
    {
        // Check permission
        if (!$request->user()->can('manage settings')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to manage settings',
            ], 403);
        }

        $validated = $request->validate([
            'bot_token' => 'required|string',
            'attendance_chat_id' => 'required|string',
            'checkout_chat_id' => 'required|string',
        ]);

        Setting::setValue('telegram_bot_token', $validated['bot_token']);
        Setting::setValue('telegram_attendance_chat_id', $validated['attendance_chat_id']);
        Setting::setValue('telegram_checkout_chat_id', $validated['checkout_chat_id']);

        return response()->json([
            'success' => true,
            'message' => 'Telegram settings updated successfully'
        ]);
    }
}
