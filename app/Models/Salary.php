<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salary extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'month',
        'year',
        'present_days',
        'absent_days',
        'leave_paid_days',
        'leave_unpaid_days',
        'late_days',
        'overtime_pay',
        'deduction',
        'bonus',
        'total_salary',
    ];

    protected $casts = [
        'month' => 'integer',
        'year' => 'integer',
        'present_days' => 'integer',
        'absent_days' => 'integer',
        'leave_paid_days' => 'integer',
        'leave_unpaid_days' => 'integer',
        'late_days' => 'integer',
        'overtime_pay' => 'decimal:2',
        'deduction' => 'decimal:2',
        'bonus' => 'decimal:2',
        'total_salary' => 'decimal:2',
    ];

    /**
     * Get the employee that owns the salary
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
