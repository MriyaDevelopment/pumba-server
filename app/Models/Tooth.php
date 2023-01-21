<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tooth extends Model
{
    use HasFactory;

    protected $fillable = [
        'id', 'toothId'
    ];

    protected $hidden = [
        'childId',
        'api_token',
        'updated_at',
        'created_at'
    ];

    protected $table = 'teeth';

    protected $casts = [
        'childId' => 'string',
        'toothId' => 'string',
        'id' => 'string',
    ];
}
