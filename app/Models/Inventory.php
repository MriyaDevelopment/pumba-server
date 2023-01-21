<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'id', 'name', 'image', 'gameId'
    ];

    protected $hidden = [
        'updated_at',
        'created_at'
    ];

    protected $table = 'inventories';
}
