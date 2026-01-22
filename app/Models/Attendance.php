<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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
        'date' => 'date',
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
     * Assuming work starts at 9:00 AM
     */
    public function calculateLateMinutes()
    {
        if (!$this->check_in) {
            return 0;
        }

        $workStartTime = Carbon::parse($this->date->format('Y-m-d') . ' 09:00:00');
        $checkInTime = Carbon::parse($this->check_in);

        if ($checkInTime->greaterThan($workStartTime)) {
            $this->late_minutes = $checkInTime->diffInMinutes($workStartTime);
        } else {
            $this->late_minutes = 0;
        }

        return $this->late_minutes;
    }

    /**
     * Calculate overtime hours based on check-out time
     * Assuming work ends at 5:00 PM (8 hours)
     */
    public function calculateOvertimeHours()
    {
        if (!$this->check_out || !$this->check_in) {
            return 0;
        }

        $workEndTime = Carbon::parse($this->date->format('Y-m-d') . ' 17:00:00');
        $checkOutTime = Carbon::parse($this->check_out);

        if ($checkOutTime->greaterThan($workEndTime)) {
            $overtimeMinutes = $checkOutTime->diffInMinutes($workEndTime);
            $this->overtime_hours = round($overtimeMinutes / 60, 2);
        } else {
            $this->overtime_hours = 0;
        }

        return $this->overtime_hours;
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
