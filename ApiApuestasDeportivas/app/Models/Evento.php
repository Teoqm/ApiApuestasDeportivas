<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;  

class Evento extends Model  
{
    use HasFactory;

    // Estados del evento
    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_FINALIZADO = 'finalizado';

    protected $fillable = [

        'deporte',
        'equipo_local',      
        'equipo_visitante',  
        'fecha',
        'estado',
        'resultado'  // local, empate, visitante (cuando finaliza)            
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    // Relación: Un evento tiene muchas apuestas
    public function apuestas()
    {
        return $this->hasMany(Apuesta::class);
    }

    // Verificar si el evento ya finalizó
    public function finalizado(): bool
    {
        return $this->estado == self::ESTADO_FINALIZADO;
    }
}