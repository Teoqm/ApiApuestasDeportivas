<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use Carbon\Carbon;

class AuthController extends Controller
{
    //valida credenciales y envía código OTP
    public function login(Request $request)
    {
        // Validar entrada
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        // Verificar credenciales sin generar token 
        if (Auth::validate($credentials)) {
            
            // Buscar usuario
            $user = User::where('email', $request->email)->first();
            
            // Generar código de 6 dígitos
            $codigo = $this->makeCode();
            
            // Guardar código y expiración (5 minutos)
            $user->codigo_verificacion = $codigo;
            $user->codigo_expiracion = Carbon::now()->addMinutes(5);
            $user->save();

            // Enviar código por email
            Mail::raw("Hola, tu código de verificación para la API es: $codigo", function ($message) use ($user) {
                $message->to($user->email)->subject('Código de verificación - Api Apuestas');
                });

            return response()->json([
                'message' => 'Credenciales correctas. Revisa tu email para el código OTP.',
                'email' => $user->email
            ], 200);
            
        } else {
            return response()->json([
                'message' => 'Credenciales incorrectas'
            ], 401);
        }
    }

    //Verificar OTP y entregar JWT
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'codigo' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        // Buscar usuario
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        // Verificar código y expiración
        if ($user->codigo_verificacion !== $request->codigo) {
            return response()->json(['message' => 'Código incorrecto'], 401);
        }

        if (Carbon::now()->greaterThan($user->codigo_expiracion)) {
            return response()->json(['message' => 'Código expirado'], 401);
        }

        // Código válido: generar JWT
        $token = JWTAuth::fromUser($user);

        // Limpiar código usado
        $user->codigo_verificacion = null;
        $user->codigo_expiracion = null;
        $user->save();

        return response()->json([
            'message' => 'Autenticación exitosa',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'saldo' => $user->saldo
            ]
        ], 200);
    }

    /**
     * Obtener información del usuario autenticado
     */
    public function me()
    {
        try {
            $user = Auth::user();
            
            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'saldo' => $user->saldo
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'No autenticado'], 401);
        }
    }

    //Cerrar sesión
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'message' => 'Sesión cerrada exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudo cerrar la sesión'
            ], 500);
        }
    }

    //Refrescar token
    public function refresh()
    {
        try {
            $newToken = JWTAuth::refresh(JWTAuth::getToken());

            return response()->json([
                'message' => 'Token renovado exitosamente',
                'access_token' => $newToken
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudo renovar el token. Inicie sesión nuevamente.'
            ], 401);
        }
    }

    //Genera un código aleatorio de 6 dígitos

    public function makeCode()
    {
        // Genera bytes aleatorios y los convierte a hexadecimal
        $bytes = random_bytes(3); // 3 bytes = 6 caracteres hex
        $codigo = bin2hex($bytes);
        return $codigo;
    }
}