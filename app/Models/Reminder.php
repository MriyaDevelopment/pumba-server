<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'id', 'name', 'note', 'time', 'date', 'repeat', 'color', 'type', 'state'
    ];

    protected $hidden = [
        'api_token',
        'updated_at',
        'created_at'
    ];

    protected $casts = [
        'repeat' => 'boolean',
    ];

    protected $table = 'reminders';
}
