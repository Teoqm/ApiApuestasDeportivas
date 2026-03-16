<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventosBaseController; 
use App\Http\Controllers\ApuestaController;   

// ===== RUTAS PÚBLICAS =====
Route::post('login', [AuthController::class, 'login']);

// Verificar OTP
Route::post('verify-otp', [AuthController::class, 'verifyOtp']);

// Ruta de prueba
Route::get('/test', function() {
    return response()->json(['mensaje' => 'API funciona correctamente']);
});

// ===== RUTAS CON JWT =====
Route::middleware(['jwt.auth'])->group(function () {
    
    // === RUTAS PARA CUALQUIER AUTENTICADO (admin y usuario) ===
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('me', [AuthController::class, 'me']);
    

    
    // === RUTAS DE EVENTOS para todos ===
    // Listar todos eventos
    Route::get('eventos', [EventosBaseController::class, 'index']);      
    Route::get('eventos/{id}', [EventosBaseController::class, 'show']);  // Ver uno
    
    // === RUTAS DE APUESTAS todos pueden apostar ===
    Route::get('mis-apuestas', [ApuestaController::class, 'misApuestas']);
    Route::post('apuestas', [ApuestaController::class, 'store']);
    Route::get('apuestas/{id}', [ApuestaController::class, 'show']);
    Route::post('apuestas/{id}/cobrar', [ApuestaController::class, 'cobrar']);
    
    // === RUTAS SOLO PARA ADMIN ===
    Route::middleware([\App\Http\Middleware\CheckRole::class . ':admin'])->group(function () {
        // Gestión de eventos
        Route::post('eventos', [EventosBaseController::class, 'store']);
        Route::put('eventos/{id}', [EventosBaseController::class, 'update']);
        Route::delete('eventos/{id}', [EventosBaseController::class, 'destroy']);
        Route::post('eventos/{id}/simular', [EventosBaseController::class, 'simularResultado']);
        
        // Gestión de apuestas (admin)
        Route::get('admin/apuestas', [ApuestaController::class, 'index']);
        Route::post('admin/apuestas/{id}/cancelar', [ApuestaController::class, 'cancelar']);
    });
});