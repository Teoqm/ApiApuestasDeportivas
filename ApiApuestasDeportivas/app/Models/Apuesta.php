<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Apuesta extends Model
{
    use HasFactory;

    // Tipos de apuesta
    const TIPO_LOCAL = 'local';
    const TIPO_EMPATE = 'empate';
    const TIPO_VISITANTE = 'visitante';

    // Estados de la apuesta
    const ESTADO_ACTIVA = 'activa';
    const ESTADO_GANADA = 'ganada';
    const ESTADO_PERDIDA = 'perdida';
    const ESTADO_COBRADA = 'cobrada';

    protected $table = 'apuestas';

    protected $fillable = [
        'usuario_id',
        'evento_id',
        'tipo_apuesta',  // local, empate, visitante
        'monto',
        'cuota',
        'estado',
        'ganancia'       // monto * cuota si gana
    ];

    // Relación: Una apuesta pertenece a un usuario
    public function usuario()
    {
        return $this->belongsTo(User::class);
    }

    // Relación: Una apuesta pertenece a un evento
    public function evento()
    {
        return $this->belongsTo(Evento::class);
    }

    // Calcular ganancia potencial
    public function calcularGanancia(): float
    {
        return $this->monto * $this->cuota;
    }

    // Verificar si el usuario puede cobrar
    public function esCobrable(): bool
    {
        return $this->estado == self::ESTADO_GANADA && 
               $this->evento->finalizado();
    }
}