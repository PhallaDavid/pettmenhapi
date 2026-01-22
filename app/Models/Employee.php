<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'position',
        'base_salary',
        'working_days',
        'salary_per_day',
        'overtime_rate',
        'status',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'working_days' => 'integer',
        'salary_per_day' => 'decimal:2',
        'overtime_rate' => 'decimal:2',
    ];

    /**
     * Calculate salary per day
     */
    public function calculateSalaryPerDay()
    {
        if ($this->working_days > 0) {
            $this->salary_per_day = $this->base_salary / $this->working_days;
        }
        return $this->salary_per_day;
    }

    /**
     * Get attendances for this employee
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
                $employee->salary_per_day = $employee->base_salary / $employee->working_days;
            }
        });
    }
}
