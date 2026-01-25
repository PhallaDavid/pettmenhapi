<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $settings = [
            [
                'key' => 'work_start_time',
                'value' => '09:00:00',
                'description' => 'Official work start time (HH:MM:SS)'
            ],
            [
                'key' => 'work_end_time',
                'value' => '17:00:00',
                'description' => 'Official work end time (HH:MM:SS)'
            ],
            [
                'key' => 'working_days_per_month',
                'value' => '26',
                'description' => 'Standard working days per month'
            ],
            [
                'key' => 'late_threshold_minutes',
                'value' => '0',
                'description' => 'Minutes allowed after start time before being marked as late'
            ],
            [
                'key' => 'company_attendance_qr',
                'value' => 'PettMenh-Office-Location-1',
                'description' => 'Fixed Master QR Code for the Office (Employees scan this with their phone)'
            ],
            [
                'key' => 'telegram_bot_token',
                'value' => 'YOUR_BOT_TOKEN_HERE',
                'description' => 'Telegram Bot API Token'
            ],
            [
                'key' => 'telegram_attendance_chat_id',
                'value' => 'YOUR_ATTENDANCE_CHAT_ID',
                'description' => 'Telegram Group ID for Attendance Alerts'
            ],
            [
                'key' => 'telegram_checkout_chat_id',
                'value' => 'YOUR_CHECKOUT_CHAT_ID',
                'description' => 'Telegram Group ID for Checkout Alerts'
            ],
            [
                'key' => 'telegram_leave_chat_id',
                'value' => 'YOUR_LEAVE_CHAT_ID',
                'description' => 'Telegram Group ID for Leave Request Alerts'
            ],
            [
                'key' => 'company_name',
                'value' => 'Pett Menh Tomacheat',
                'description' => 'Name of the company'
            ]
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
