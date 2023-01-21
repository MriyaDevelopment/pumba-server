<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class SaveGame extends Model
{
    protected $table = 'savedGames';
    protected $fillable = [
        'id', 'gameId'
    ];

    protected $hidden = [
        'api_token',
        'updated_at',
        'created_at'
    ];
}
