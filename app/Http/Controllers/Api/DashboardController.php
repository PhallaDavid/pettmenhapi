<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function stats()
    {
        $totalPatients = Patient::count();
        $activeToday = Patient::where('status', 'active')->whereDate('updated_at', Carbon::today())->count();
        $newThisMonth = Patient::whereMonth('created_at', Carbon::now()->month)
                               ->whereYear('created_at', Carbon::now()->year)
                               ->count();
        $followUps = Patient::where('is_follow_up', true)->count();

        return response()->json([
            'success' => true,
            'stats' => [
                'total_patients' => $totalPatients,
                'active_today' => $activeToday,
                'new_this_month' => $newThisMonth,
                'follow_ups' => $followUps,
            ]
        ]);
    }

    public function updatePreferences(Request $request)
    {
        $request->validate([
            'theme' => 'sometimes|string|in:light,dark',
            'language' => 'sometimes|string|max:10',
            'sidebar_collapsed' => 'sometimes|boolean',
        ]);

        $user = $request->user();
        $preferences = $user->preferences ?? [];

        if ($request->has('theme')) {
            $preferences['theme'] = $request->theme;
        }
        if ($request->has('language')) {
            $preferences['language'] = $request->language;
        }
        if ($request->has('sidebar_collapsed')) {
            $preferences['sidebar_collapsed'] = $request->sidebar_collapsed;
        }

        $user->preferences = $preferences;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Preferences updated successfully',
            'preferences' => $user->preferences
        ]);
    }
}
