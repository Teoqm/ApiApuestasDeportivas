<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductoBaseController;


// Rutas públicas
Route::post('login', [AuthController::class, 'login']);

// GET - Ruta de prueba para verificar que la API funciona
Route::get('/test', function() {
    return response()->json(['mensaje' => 'API funciona correctamente']);
});


//RUtas con seguriada
Route::middleware(['auth:api'])->group(function () {
    

    //  === Rutas accesibles para admin, usuario ===
    Route::post('logout', [AuthController::class, 'logout']) ->middleware('role:admin,usuario');
    
    Route::post('refresh', [AuthController::class, 'refresh'])->middleware('role:admin,usuario');
    
    // Ruta para obtener información del usuario autenticado
    Route::get('me', [AuthController::class, 'me'])->middleware('role:admin,usuario');

    //ver eventos
    Route::get('eventos', [EventosBaseController::class, 'show'])->middleware('role:admin,usuario');
    //ver evento especifico
    Route::get('eventos/{id}', [EventosBaseController::class, 'show'])->middleware('role:admin,usuario');
    
    
    // ===== RUTAS De Admin =====
    //  Rutas accesibles para admin
    Route::middleware([\App\Http\Middleware\CheckRole::class . ':admin'])->group(function () {
        //crea eventos
        Route::post('eventos', [EventosBaseController::class, 'store']) ->middleware('role:admin');
        //modificar eventos
        Route::put('eventos/{id}', [EventosBaseController::class, 'update']) ->middleware('role:admin');
        //eleminar evemtos
        Route::delete('eventos/{id}', [EventosBaseController::class, 'destroy']) ->middleware('role:admin');
    });
    
});middleware('auth:sanctum');
