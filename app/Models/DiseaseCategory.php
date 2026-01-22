<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiseaseCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'price_promotion',
        'promotion_percent',
        'date_start_promotion',
        'date_end_promotion',
        'price_after_promotion',
        'promotion_note',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'price_promotion' => 'decimal:2',
        'price_after_promotion' => 'decimal:2',
        'promotion_percent' => 'decimal:2',
        'date_start_promotion' => 'date',
        'date_end_promotion' => 'date',
    ];
}

