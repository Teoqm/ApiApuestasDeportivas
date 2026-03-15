<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Evento extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    const APUESTA_VISTANTE = 'Vistante Gana';
    const APUESTA_LOCAL = 'Locala Gana';
    const APUESTA_EMPATE = 'usuario';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'deporte',
        'eq_visantete',
        'eq_local',
        'fecha',
        'apuesta',
        'monton',
        'couto'
    ];

    
}
