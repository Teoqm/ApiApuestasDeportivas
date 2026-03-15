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
    Route::get('eventos', [ProductoBaseController::class, 'show'])->middleware('role:admin,usuario');

    
    
    // ===== RUTAS De Admin =====
    //crea nuevo evento
    Route::post('eventos', [EventosBaseController::class, 'index']) ->middleware('role:admin') ;
    
    
    //  Rutas accesibles para admin
    Route::middleware([\App\Http\Middleware\CheckRole::class . ':admin,operador'])->group(function () {
        Route::post('productos', [ProductoBaseController::class, 'store']);
        Route::put('productos/{id}', [ProductoBaseController::class, 'update']);
    });
    
    
    //  Rutas accesibles SOLO para admin
    Route::middleware([\App\Http\Middleware\CheckRole::class . ':admin'])->group(function () {
        Route::delete('productos/{id}', [ProductoBaseController::class, 'destroy']);
    });
});middleware('auth:sanctum');
