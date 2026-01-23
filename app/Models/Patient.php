<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'fullname',
        'age',
        'gender',
        'is_old_patient',
        'phone_number',
        'disease_category_id',
        'employee_id',
        'address',
        'date_come_again',
        'status',
        'medical_history',
        'emergency_contact_name',
        'emergency_contact_phone',
        'is_follow_up',
    ];

    public function diseaseCategory()
    {
        return $this->belongsTo(DiseaseCategory::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
