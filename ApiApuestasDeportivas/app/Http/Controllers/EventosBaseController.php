<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Models\Evento;
use Illuminate\Support\Facades\Validator;

class EventosBaseController extends BaseController
{
    // Listar todos los eventos
    public function index()
    {
        $eventos = Evento::all();

        return response()->json([
            'message' => 'Listado de todos los Eventos',
            'data' => $eventos,  
        ]);
    }

    //Crear un nuevo evento solo admin
    public function store(Request $request)
    {
        // Validar según los campos del MODELO
        $validator = Validator::make($request->all(), [
            'deporte' => 'required|string|max:50',
            'equipo_local' => 'required|string|max:100',      
            'equipo_visitante' => 'required|string|max:100',  
            'fecha' => 'required|date',                        
            'estado' => 'sometimes|in:pendiente,finalizado', 
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        // Crear evento con los campos correctos
        $evento = Evento::create([
            'deporte' => $request->deporte,
            'equipo_local' => $request->equipo_local,
            'equipo_visitante' => $request->equipo_visitante,
            'fecha' => $request->fecha,
            'estado' => $request->estado ?? Evento::ESTADO_PENDIENTE, // valor por defecto pendiente
        ]);

        return response()->json([
            'message' => 'Evento creado correctamente',
            'data' => $evento,
        ], 201);
    }

    //Mostrar un evento específico

    public function show(String $id)
    {
        $evento = Evento::find($id);

        if (!$evento) {
            return response()->json([
                'message' => "No se encontró el evento solicitado con id ($id)"
            ], 404);
        }

        return response()->json([
            'message' => "Evento encontrado con id ($id)",
            'data' => $evento
        ]);
    }

    //Actualizar un evento solo admin

    public function update(Request $request, String $id)
    {
        $evento = Evento::find($id);

        if (!$evento) {
            return response()->json([
                'message' => "No se encontró el evento solicitado con id ($id)"
            ], 404);
        }

        // Validar según los campos 
        $validator = Validator::make($request->all(), [
            'deporte' => 'sometimes|string|max:50',
            'equipo_local' => 'sometimes|string|max:100',
            'equipo_visitante' => 'sometimes|string|max:100',
            'fecha' => 'sometimes|date',
            'estado' => 'sometimes|in:pendiente,finalizado',
            'resultado' => 'sometimes|in:local,empate,visitante', // para simular resultado
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        // Actualizar solo los campos que vienen en la petición
        $evento->update($request->only([
            'deporte',
            'equipo_local',
            'equipo_visitante',
            'fecha',
            'estado',
            'resultado'
        ]));

        return response()->json([
            'message' => "Evento con id ($id) actualizado correctamente",
            'data' => $evento
        ]);
    }

    // Eliminar un evento solo admin
    public function destroy(String $id)
    {
        $evento = Evento::find($id);

        if (!$evento) {
            return response()->json([
                'message' => "No se encontró el evento solicitado con id ($id)"
            ], 404);
        }
        $evento->delete();

        return response()->json([
            'message' => "Evento con id ($id) ha sido eliminado"
        ]);
    }

    //Simular resultado de un evento (método extra útil para el admin)
    
    public function simularResultado(Request $request, String $id)
    {
        $evento = Evento::find($id);

        if (!$evento) {
            return response()->json([
                'message' => "No se encontró el evento solicitado con id ($id)"
            ], 404);
        }

        // Validar resultado
        $validator = Validator::make($request->all(), [
            'resultado' => 'required|in:local,empate,visitante',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        // Actualizar evento con resultado y estado finalizado
        $evento->update([
            'resultado' => $request->resultado,
            'estado' => Evento::ESTADO_FINALIZADO
        ]);

        return response()->json([
            'message' => "Resultado del evento ($id) simulado correctamente",
            'data' => $evento
        ]);
    }
}