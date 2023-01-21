<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Memory extends Model
{
    use HasFactory;

    protected $fillable = [
        'id', 'date', 'name', 'note', 'color', 'image'
    ];

    protected $hidden = [
        'updated_at',
        'created_at',
        'childId',
        'api_token'
    ];

    protected $table = 'memories';
}
