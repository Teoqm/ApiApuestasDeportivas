<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model; 

class Apuesta extends Model  
{
    use HasFactory;  

    // Constantes
    const APUESTA_VISITANTE = 'visitante_gana'; 
    const APUESTA_LOCAL = 'local_gana';          
    const APUESTA_EMPATE = 'empate';           
    // Estados de la apuesta 
    const ESTADO_ACTIVA = 'activa';
    const ESTADO_GANADA = 'ganada';
    const ESTADO_PERDIDA = 'perdida';
    const ESTADO_COBRADA = 'cobrada';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [

        'usuario_id',        
        'evento_id',         
        'tipo_apuesta',      
        'monto',             
        'cuota',            
        'estado',            
        'ganancia'           
    ];

    protected $casts = [
        'fecha' => 'datetime',  
    ];

    // Relacion con el usuario que hizo la apuesta
    public function usuario()
    {
        return $this->belongsTo(User::class);
    }

    //Relacion con el evento apostado
    public function evento()
    {
        return $this->belongsTo(Evento::class);
    }
}