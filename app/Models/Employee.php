<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'position',
        'base_salary',
        'working_days',
        'salary_per_day',
        'overtime_rate',
        'status',
        'qr_code',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'working_days' => 'integer',
        'salary_per_day' => 'decimal:2',
        'overtime_rate' => 'decimal:2',
    ];

    protected $appends = ['qr_code_url'];

    /**
     * Get the QR code URL for scanning
     */
    public function getQrCodeUrlAttribute()
    {
        if (!$this->qr_code) {
            return null;
        }
        
        // Generate a URL for the QR code image (using Google Charts API or similar)
        return "https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=" . urlencode($this->qr_code) . "&choe=UTF-8";
    }

    /**
     * Calculate salary per day
     */
    public function calculateSalaryPerDay()
    {
        if ($this->working_days > 0) {
            $this->salary_per_day = (string) round($this->base_salary / $this->working_days, 2);
        }
        return $this->salary_per_day;
    }

    /**
     * Get the user associated with the employee
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the employee's attendances
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get salaries for this employee
     */
    public function salaries()
    {
        return $this->hasMany(Salary::class);
    }

    /**
     * Boot method to auto-calculate salary_per_day
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($employee) {
            if ($employee->base_salary && $employee->working_days) {
                $employee->salary_per_day = (string) ($employee->base_salary / $employee->working_days);
            }

            if (!$employee->qr_code) {
                $employee->qr_code = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }
}
