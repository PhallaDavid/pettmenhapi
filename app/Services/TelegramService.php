<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    /**
     * Send message to a telegram chat
     */
    public static function sendMessage($chatId, $message)
    {
        $token = Setting::getValue('telegram_bot_token');

        if (!$token || $token === 'YOUR_BOT_TOKEN_HERE') {
            Log::warning('Telegram Bot Token not configured.');
            return false;
        }

        if (!$chatId || $chatId === 'YOUR_ATTENDANCE_CHAT_ID' || $chatId === 'YOUR_CHECKOUT_CHAT_ID' || $chatId === 'YOUR_LEAVE_CHAT_ID') {
            Log::warning('Telegram Chat ID not configured.');
            return false;
        }

        try {
            $response = Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown'
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Telegram Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send Attendance Alert
     */
    public static function sendAttendanceAlert($attendance, $type)
    {
        $chatId = Setting::getValue('telegram_attendance_chat_id');
        $employee = $attendance->employee;
        $time = \Carbon\Carbon::parse($attendance->{$type})->setTimezone('Asia/Phnom_Penh')->format('h:i A');
        $date = \Carbon\Carbon::parse($attendance->date)->format('d-M-Y');

        $typeName = ($type === 'check_in') ? '*Check-In*' : '🚩 *Check-Out*';
        $statusEmoji = $attendance->status === 'late' ? '⚠️' : '✨';

        $message = "*Attendance Alert*\n\n";
        $message .= "*Employee:* {$employee->name}\n";
        $message .= "*Action:* {$typeName}\n";
        $message .= "*Time:* {$time}\n";
        $message .= "*Date:* {$date}\n";
        $message .= "*Status:* {$statusEmoji} " . strtoupper($attendance->status) . "\n";

        if ($type === 'check_in' && $attendance->late_minutes > 0) {
            $hours = floor($attendance->late_minutes / 60);
            $mins = $attendance->late_minutes % 60;
            $lateFormat = ($hours > 0) ? "{$hours}h {$mins}m" : "{$mins}m";
            $message .= "🕒 *Late:* {$lateFormat}\n";
        }

        return self::sendMessage($chatId, $message);
    }

    /**
     * Send Checkout Alert
     */
    public static function sendCheckoutAlert($checkout)
    {
        $chatId = Setting::getValue('telegram_checkout_chat_id');
        $patient = $checkout->patient;
        $disease = $checkout->diseaseCategory;
        $time = \Carbon\Carbon::now('Asia/Phnom_Penh')->format('h:i A');

        $statusEmoji = $checkout->status === 'paid' ? '💰' : ($checkout->status === 'partial' ? '⏳' : '🔴');
        $isDebt = $checkout->debt_amount > 0 ? " (ជំពាក់: \${$checkout->debt_amount})" : "";

        $message = "*Checkout Alert*\n\n";
        $message .= "*Patient:* {$patient->fullname}\n";
        $message .= "*Treatment:* " . ($disease ? $disease->name : 'N/A') . "\n";
        $message .= "*Total:* \${$checkout->total_amount}\n";

        if ($checkout->discount_amount > 0) {
            $message .= "*Discount:* \${$checkout->discount_amount}\n";
        }

        $message .= "*Paid:* \${$checkout->paid_amount}\n";

        if ($checkout->debt_amount > 0) {
            $message .= "*Debt (ជំពាក់):* \${$checkout->debt_amount}\n";
        }

        $message .= "*Status:* {$statusEmoji} " . strtoupper($checkout->status) . "\n";
        $message .= "*Method:* " . strtoupper($checkout->payment_method) . "\n";
        $message .= "*Time:* {$time}\n";

        return self::sendMessage($chatId, $message);
    }

    /**
     * Send Leave Alert
     */
    public static function sendLeaveAlert($leaveRequest, $type = 'new')
    {
        $chatId = Setting::getValue('telegram_leave_chat_id');
        $user = $leaveRequest->user;
        $category = $leaveRequest->category;
        
        $startDate = \Carbon\Carbon::parse($leaveRequest->start_date)->format('d-M-Y');
        $endDate = \Carbon\Carbon::parse($leaveRequest->end_date)->format('d-M-Y');
        $dateRange = ($startDate === $endDate) ? $startDate : "{$startDate} to {$endDate}";
        
        $createdAt = $leaveRequest->created_at->setTimezone('Asia/Phnom_Penh')->format('d-M-Y h:i A');

        $typeName = ($type === 'new') ? '🆕 *New Leave Request*' : '🔄 *Leave Status Updated*';
        $statusEmoji = $leaveRequest->status === 'approved' ? '✅' : ($leaveRequest->status === 'rejected' ? '❌' : '⏳');
        $leaveType = str_replace('_', ' ', ucfirst($leaveRequest->leave_type));
        
        if ($leaveRequest->leave_type === 'custom' && $leaveRequest->start_time && $leaveRequest->end_time) {
            $startTime = \Carbon\Carbon::parse($leaveRequest->start_time)->format('h:i A');
            $endTime = \Carbon\Carbon::parse($leaveRequest->end_time)->format('h:i A');
            $leaveType .= " ({$startTime} - {$endTime})";
        }

        $message = "{$typeName}\n\n";
        $message .= "*Employee:* {$user->name}\n";
        $message .= "*Date:* {$dateRange}\n";
        $message .= "*Category:* " . ($category ? $category->name : 'N/A') . "\n";
        $message .= "*Type:* {$leaveType}\n";
        $message .= "*Status:* {$statusEmoji} " . strtoupper($leaveRequest->status) . "\n";
        $message .= "*Reason:* {$leaveRequest->reason}\n";

        if ($leaveRequest->review_note) {
            $message .= "*Review Note:* {$leaveRequest->review_note}\n";
        }

        if ($type === 'new') {
            $message .= "*Submitted At:* {$createdAt}\n";
        }

        return self::sendMessage($chatId, $message);
    }
}
