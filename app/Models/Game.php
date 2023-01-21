<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'id', 'title', 'subtitle', 'type', 'time', 'image', 'description'
    ];

    protected $hidden = [
        'updated_at',
        'created_at'
    ];

    protected $table = 'games';
}
