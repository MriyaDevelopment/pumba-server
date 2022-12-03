<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Child extends Model
{
    use HasFactory;

    protected $fillable = [
        'id', 'name', 'avatar', 'birth', 'gender'
    ];

    protected $hidden = [
        'api_token',
        'updated_at',
        'created_at'
    ];
}
