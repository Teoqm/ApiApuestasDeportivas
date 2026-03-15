<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Models\Apuesta;
use App\Models\Evento;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApuestasController extends BaseController
{
    //Listar todas las apuestas solo admin
    public function index()
    {
        // Solo admin puede ver todas
        $apuestas = Apuesta::with(['usuario', 'evento'])->get();

        return response()->json([
            'message' => 'Listado de todas las apuestas',
            'data' => $apuestas
        ]);
    }

    //Listar apuestas del usuario autenticado
    public function misApuestas()
    {
        $user = Auth::user();
        
        $apuestas = Apuesta::where('usuario_id', $user->id)
                          ->with('evento')
                          ->orderBy('created_at', 'desc')
                          ->get();

        return response()->json([
            'message' => 'Tus apuestas',
            'data' => $apuestas
        ]);
    }

    //Mostrar una apuesta específica
    public function show(String $id)
    {
        $user = Auth::user();
        
        $apuesta = Apuesta::with(['usuario', 'evento'])->find($id);

        if (!$apuesta) {
            return response()->json([
                'message' => "No se encontró la apuesta con id ($id)"
            ], 404);
        }

        // Verificar que sea el dueño o admin
        if ($user->id !== $apuesta->usuario_id && !$user->isAdmin()) {
            return response()->json([
                'message' => 'No autorizado para ver esta apuesta'
            ], 403);
        }

        return response()->json([
            'message' => "Apuesta encontrada",
            'data' => $apuesta
        ]);
    }

    /*
    Crear una nueva apuesta
    Las cuotas se ingresan manualmente en el momento de la apuesta
    */
    
    public function store(Request $request)
    {
        // Validar datos de entrada
        $validator = Validator::make($request->all(), [
            'evento_id' => 'required|exists:eventos,id',
            'tipo_apuesta' => 'required|in:local,empate,visitante',
            'monto' => 'required|numeric|min:1000', // Mínimo 1000 para apuesta
            'cuota' => 'required|numeric|min:1.01', // Cuota mínima 1.01
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        // Obtener usuario autenticado
        $user = Auth::user();

        // Verificar que el usuario tenga saldo suficiente
        if ($user->saldo < $request->monto) {
            return response()->json([
                'message' => 'Saldo insuficiente',
                'saldo_actual' => $user->saldo,
                'monto_requerido' => $request->monto
            ], 400);
        }

        // Verificar que el evento existe y está pendiente
        $evento = Evento::find($request->evento_id);
        
        if (!$evento) {
            return response()->json([
                'message' => 'El evento no existe'
            ], 404);
        }

        if ($evento->estado !== Evento::ESTADO_PENDIENTE) {
            return response()->json([
                'message' => 'El evento ya finalizó, no se pueden hacer apuestas'
            ], 400);
        }

        // Calcular ganancia potencial
        $ganancia_potencial = $request->monto * $request->cuota;

        // INICIO DE TRANSACCIÓN (importante por el dinero)
        DB::beginTransaction();

        try {
            // 1. Crear la apuesta
            $apuesta = Apuesta::create([
                'usuario_id' => $user->id,
                'evento_id' => $request->evento_id,
                'tipo_apuesta' => $request->tipo_apuesta,
                'monto' => $request->monto,
                'cuota' => $request->cuota,
                'ganancia' => $ganancia_potencial,
                'estado' => Apuesta::ESTADO_ACTIVA
            ]);

            // 2. Descontar saldo del usuario
            $user->saldo -= $request->monto;
            $user->save();

            DB::commit();

            return response()->json([
                'message' => 'Apuesta realizada con éxito',
                'data' => [
                    'apuesta' => $apuesta->load('evento'),
                    'saldo_restante' => $user->saldo,
                    'ganancia_potencial' => $ganancia_potencial
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'message' => 'Error al procesar la apuesta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar una apuesta específica
     */
    public function show($id)
    {
        $user = Auth::user();
        
        $apuesta = Apuesta::with(['evento', 'usuario'])
                    ->find($id);

        if (!$apuesta) {
            return response()->json([
                'message' => "No se encontró la apuesta con id ($id)"
            ], 404);
        }

        // Verificar que el usuario sea el dueño o admin
        if ($user->id !== $apuesta->usuario_id && !$user->isAdmin()) {
            return response()->json([
                'message' => 'No autorizado para ver esta apuesta'
            ], 403);
        }

        return response()->json([
            'message' => 'Detalle de la apuesta',
            'data' => $apuesta
        ]);
    }

    /**
     * Cobrar una apuesta ganada
     */
    public function cobrar($id)
    {
        $user = Auth::user();
        
        $apuesta = Apuesta::where('id', $id)
                    ->where('usuario_id', $user->id)
                    ->first();

        if (!$apuesta) {
            return response()->json([
                'message' => 'Apuesta no encontrada'
            ], 404);
        }

        // Verificar que la apuesta esté ganada
        if ($apuesta->estado !== Apuesta::ESTADO_GANADA) {
            return response()->json([
                'message' => 'Esta apuesta no está en estado ganada',
                'estado_actual' => $apuesta->estado
            ], 400);
        }

        // Verificar que el evento haya finalizado
        if (!$apuesta->evento->finalizado()) {
            return response()->json([
                'message' => 'El evento aún no ha finalizado'
            ], 400);
        }

        DB::beginTransaction();

        try {
            // Acreditar ganancia al usuario
            $user->saldo += $apuesta->ganancia;
            $user->save();

            // Marcar apuesta como cobrada
            $apuesta->estado = Apuesta::ESTADO_COBRADA;
            $apuesta->save();

            DB::commit();

            return response()->json([
                'message' => '¡Felicidades! Has cobrado tu apuesta',
                'data' => [
                    'ganancia' => $apuesta->ganancia,
                    'nuevo_saldo' => $user->saldo,
                    'apuesta' => $apuesta
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'message' => 'Error al cobrar la apuesta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancelar una apuesta (solo admin, o antes de que comience el evento)
     */
    public function cancelar($id)
    {
        $user = Auth::user();
        
        // Solo admin puede cancelar
        if (!$user->isAdmin()) {
            return response()->json([
                'message' => 'No autorizado para cancelar apuestas'
            ], 403);
        }

        $apuesta = Apuesta::find($id);

        if (!$apuesta) {
            return response()->json([
                'message' => 'Apuesta no encontrada'
            ], 404);
        }

        // Solo se pueden cancelar apuestas activas
        if ($apuesta->estado !== Apuesta::ESTADO_ACTIVA) {
            return response()->json([
                'message' => 'Solo se pueden cancelar apuestas activas',
                'estado_actual' => $apuesta->estado
            ], 400);
        }

        DB::beginTransaction();

        try {
            // Devolver el dinero al usuario
            $usuario = User::find($apuesta->usuario_id);
            $usuario->saldo += $apuesta->monto;
            $usuario->save();

            // Cambiar estado de la apuesta
            $apuesta->estado = 'cancelada';
            $apuesta->save();

            DB::commit();

            return response()->json([
                'message' => 'Apuesta cancelada y saldo devuelto al usuario',
                'data' => [
                    'apuesta' => $apuesta,
                    'usuario' => [
                        'id' => $usuario->id,
                        'saldo_restaurado' => $usuario->saldo
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'message' => 'Error al cancelar la apuesta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Estadísticas del usuario
     */
    public function estadisticas()
    {
        $user = Auth::user();

        $stats = [
            'total_apostado' => Apuesta::where('usuario_id', $user->id)
                                ->sum('monto'),
            'apuestas_ganadas' => Apuesta::where('usuario_id', $user->id)
                                ->where('estado', Apuesta::ESTADO_GANADA)
                                ->count(),
            'apuestas_perdidas' => Apuesta::where('usuario_id', $user->id)
                                ->where('estado', Apuesta::ESTADO_PERDIDA)
                                ->count(),
            'apuestas_activas' => Apuesta::where('usuario_id', $user->id)
                                ->where('estado', Apuesta::ESTADO_ACTIVA)
                                ->count(),
            'total_ganancias' => Apuesta::where('usuario_id', $user->id)
                                ->where('estado', Apuesta::ESTADO_COBRADA)
                                ->sum('ganancia'),
            'saldo_actual' => $user->saldo
        ];

        return response()->json([
            'message' => 'Estadísticas del usuario',
            'data' => $stats
        ]);
    }
}