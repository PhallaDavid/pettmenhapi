<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\Setting;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'check_in',
        'check_out',
        'late_minutes',
        'overtime_hours',
        'status',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'late_minutes' => 'integer',
        'overtime_hours' => 'decimal:2',
    ];

    /**
     * Get the employee that owns the attendance
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Calculate late minutes based on check-in time
     * Using dynamic work start time from settings
     */
    public function calculateLateMinutes()
    {
        if (!$this->check_in) {
            return 0;
        }

        // Get work start time from settings (default 09:00)
        $workStartConfig = Setting::getValue('work_start_time', '09:00');
        $lateThreshold = (int) Setting::getValue('late_threshold_minutes', 0);
        
        // Ensure we're working with Cambodia timezone
        $dateStr = Carbon::parse($this->date)->toDateString();
        $workStartTime = Carbon::parse($dateStr . ' ' . $workStartConfig, 'Asia/Phnom_Penh');
        
        // Add threshold to work start time
        $lateStartTime = $workStartTime->copy()->addMinutes($lateThreshold);
        
        $checkInTime = Carbon::parse($this->check_in)->setTimezone('Asia/Phnom_Penh');

        if ($checkInTime->greaterThan($lateStartTime)) {
            $this->late_minutes = $checkInTime->diffInMinutes($workStartTime);
        } else {
            $this->late_minutes = 0;
        }

        return $this->late_minutes;
    }

    /**
     * Calculate overtime hours based on check-out time
     * Using dynamic work end time from settings
     */
    public function calculateOvertimeHours()
    {
        if (!$this->check_out || !$this->check_in) {
            return 0;
        }

        // Get work end time from settings (default 17:00)
        $workEndConfig = Setting::getValue('work_end_time', '17:00');

        // Ensure we're working with Cambodia timezone
        $dateStr = Carbon::parse($this->date)->toDateString();
        $workEndTime = Carbon::parse($dateStr . ' ' . $workEndConfig, 'Asia/Phnom_Penh');
        $checkOutTime = Carbon::parse($this->check_out)->setTimezone('Asia/Phnom_Penh');

        if ($checkOutTime->greaterThan($workEndTime)) {
            $overtimeMinutes = $checkOutTime->diffInMinutes($workEndTime);
            $this->overtime_hours = (string) round($overtimeMinutes / 60, 2);
        } else {
            $this->overtime_hours = "0.00";
        }

        return (float) $this->overtime_hours;
    }

    /**
     * Determine status based on attendance data
     */
    public function determineStatus()
    {
        if ($this->status === 'leave_paid' || $this->status === 'leave_unpaid') {
            return; // Don't override leave status
        }

        if (!$this->check_in && !$this->check_out) {
            $this->status = 'absent';
        } elseif ($this->check_in && $this->late_minutes > 0) {
            $this->status = 'late';
        } elseif ($this->check_in && $this->check_out) {
            $this->status = 'present';
        }
    }

    /**
     * Boot method to auto-calculate fields
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($attendance) {
            if ($attendance->check_in) {
                $attendance->calculateLateMinutes();
            }
            if ($attendance->check_out && $attendance->check_in) {
                $attendance->calculateOvertimeHours();
            }
            $attendance->determineStatus();
        });
    }
}
