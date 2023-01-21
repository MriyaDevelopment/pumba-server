<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'email', 'name'
    ];

    protected $hidden = [
        'password',
        'time',
        'ages',
        'energy_level',
        'door_type',
        'stuff',
        'updated_at',
        'created_at',
        'id'
    ];

    protected $table = 'users';

    public function routeNotificationForFcm()
    {
        return $this->fcm_token;
    }
}
