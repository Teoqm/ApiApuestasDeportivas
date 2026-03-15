<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Models\Evento;
use Illuminate\Support\Facades\Validator;

class EventosBaseController extends BaseController
{
    public function index(){

        $productos = Evento::all();

         return response()->json([
            'message'=> 'Listado de todos los productos',
            'data'=> $productos,]);
    }

    public function store(Request $request){

        $validador = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100',
            'precio' => 'required|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'descripcion' => 'nullable|string'
        ]);
        if($validador->fails()){
            return response()->json([
                'errors' => $validador->errors()
            ],422);
        }

        $productos = Producto::create([
            'nombre' => $request->input('nombre'),
            'precio' => $request->input('precio'),
        ]);

        return response()->json([
            'message' => 'Producto creado correctamente',
            'data'=> $productos,], 201);
    }

    public function show(String $id){

        $producto = Producto::find($id);

        if(!$producto){
            return response()->json([
            'message' => "No se encontro el producto solicitado con id ($id)"], 404);
        }

        return response()->json([
            'message' => "Producto encontrado con id ($id)",
            'data'=> $producto]);
    }

    public function update(Request $request, String $id){

        $producto = Producto::find($id);

         if(!$producto){
            return response()->json([
            'message' => "No se encontro el producto solicitado con id ($id)"], 404);
        }

        $validador = Validator::make($request->all(),[
            'nombre' => 'nullable|string|max:100',
            'precio' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'descripcion' => 'nullable|string'
        ]);

        if($validador->fails()){
            return response()->json([
                'errors' => $validador->errors()
            ],422);
        }

        $producto->update([
            'nombre' => $request->input('nombre', $producto->nombre),
            'precio' => $request->input('precio', $producto->precio),
        ]);

        return response()->json([
            'message' => "Producto con id ($id) actualizado correctamente",
            'data'=> $producto]);
    }

    public function destroy(String $id){

        $producto = Producto::find($id);

         if(!$producto){
            return response()->json([
            'message' => "No se encontro el producto solicitado con id ($id)"], 404);
        }

        $producto->delete();

        return response()->json([
            'message' => "Producto con id ($id) ha sido eliminado"]);
    }



}
