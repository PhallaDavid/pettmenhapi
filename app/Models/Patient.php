<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'date_of_birth',
        'gender',
        'phone',
        'email',
        'address',
        'medical_history',
        'emergency_contact_name',
        'emergency_contact_phone',
        'status',
        'is_follow_up',
    ];
}
