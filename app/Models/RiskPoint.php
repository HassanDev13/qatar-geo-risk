<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiskPoint extends Model
{
    protected $fillable = [
        'name',
        'lat',
        'lng',
        'radius',
        'status',
        'submitted_by_ip',
        'description',
        'image',
    ];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
        'radius' => 'integer',
    ];
}
