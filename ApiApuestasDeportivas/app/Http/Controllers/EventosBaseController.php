<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Models\Evento;
use Illuminate\Support\Facades\Validator;

class EventosBaseController extends BaseController
{
    public function index(){

        $eventos = Evento::all();

         return response()->json([
            'message'=> 'Listado de todos los Eventos',
            'data'=> $$eventos,]);
    }

    public function store(Request $request){

        //se valida si al  inofrmacion si es aceptable
        $validador = Validator::make($request->all(), [
            'deporte' => 'nullable|string|max:10',
            'nombre' => 'required|string|max:100',
            'eq_visante' => 'required|string|max:20',
            'eq_local' => 'required|string|max:20',
            'fecha' => 'required|string|max:20',
            'apuesta' => 'required|string|max:20',
            'monton' => 'required|integer|min:0',
            'couta' => 'required|integer|min:'
        ]);
        if($validador->fails()){
            return response()->json([
                'errors' => $validador->errors()
            ],422);
        }
        // s ecrea un nuevo envento
        $eventos = Evento::create([
            'nombre' => $request->input('nombre'),
            'eq_visante' => $request->input('eq_visante'),
            'eq_local' => $request->input('eq_local' ),
            'fecha' => $request->input('fecha'),
            'apuesta' => $request->input('apuesta'),
            'monton' => $request->input('monton'),
            'couta' => $request->input('couta')




        ]);

        return response()->json([
            'message' => 'Evento creado correctamente',
            'data'=> $eventos,], 201);
    }

    public function show(String $id){

        $evento = Evento::find($id);

        if(!$evento){
            return response()->json([
            'message' => "No se encontro el evento solicitado con id ($id)"], 404);
        }

        return response()->json([
            'message' => "evento encontrado con id ($id)",
            'data'=> $evento]);
    }

    public function update(Request $request, String $id){

        $evento = Evento::find($id);

        if(!$evento){
            return response()->json([
            'message' => "No se encontro el evento solicitado con id ($id)"], 404);
        }

        //se valida si al  inofrmacion si es aceptable
        $validador = Validator::make($request->all(), [
            'deporte' => 'nullable|string|max:10',
            'nombre' => 'nullable|string|max:100',
            'eq_visante' => 'nullable|string|max:20',
            'eq_local' => 'nullable|string|max:20',
            'fecha' => 'nullable|string|max:20',
            'apuesta' => 'nullable|string|max:20',
            'monton' => 'nullable|integer|min:0',
            'couta' => 'nullable|integer|min:'
        ]);
        if($validador->fails()){
            return response()->json([
                'errors' => $validador->errors()
            ],422);
        }

        $evento->update([

            'deporte' => $request->input('deporte', $evento->deporte),
            'nombre' => $request->input('nombre', $evento->nombre),
            'eq_visante' => $request->input('eq_visante', $evento->eq_visante),
            'eq_local' => $request->input('eq_local', $evento->eq_local),
            'fecha' => $request->input('fecha', $evento->fecha),
            'apuesta' => $request->input('apuesta', $evento->apuesta),
            'monton' => $request->input('monton', $evento->monton),
            'couta' => $request->input('couta', $evento->monton),
        ]);

        return response()->json([
            'message' => "Evento con id ($id) actualizado correctamente",
            'data'=> $evento]);
    }

    public function destroy(String $id){

        $evento = Evento::find($id);

         if(!$evento){
            return response()->json([
            'message' => "No se encontro el evento solicitado con id ($id)"], 404);
        }

        $producto->delete();

        return response()->json([
            'message' => "evento con id ($id) ha sido eliminado"]);
    }


    

}
