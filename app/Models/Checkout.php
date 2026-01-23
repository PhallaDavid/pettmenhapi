<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Checkout extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'disease_category_id',
        'employee_id',
        'total_amount',
        'discount_amount',
        'final_amount',
        'paid_amount',
        'debt_amount',
        'payment_method',
        'status',
        'notes',
        'checkout_date',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'debt_amount' => 'decimal:2',
        'checkout_date' => 'date',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function diseaseCategory()
    {
        return $this->belongsTo(DiseaseCategory::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
