<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function login(Request $request){
            $credenciales = $request -> only('email', 'password');

            if(Auth::validate($credenciales)){
                $codigo = makeCode();
                //guardar el codigo
                $user =User::where('email', $request->email)->first();
                $user->codigo_verificacion = $codigo;
                $user->save();

                //Enviar correo, info sacada de stack overflow y laracasts
                Mail::raw("Hola, tu código de verificación para la API es: $codigo", function ($message) use ($user) {
                    $message->to($user->email)->subject('Tu código de acceso');
                });

                return response()->json(['status' => 'Credenciales correctas, correo enviado', 'codigo' => $codigo],200);

                return response()->json([
                'message' => 'Login Correcto',
                'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'codigo' => $user->codigo
            ]
        ]);
            }
            else{
                return response()->json([
                    'email'=>'La credenciales no son correctas'
                ],401);
            }
        }

    public function me(){
        return response()->json(auth('api')->user());
    }

    public function logout()
        {
            try {

                JWTAuth::invalidate(JWTAuth::getToken());

                return response()->json([
                    'message' => 'Sesión cerrada exitosamente'
                ], 200);

            } catch (Exception $e) {
                return response()->json([
                    'error' => 'No se pudo cerrar la sesión, el token podría ser inválido o ya expiró'
                ], 500);
            }
    }

    public function refresh()
        {
            try {
                $newToken = JWTAuth::refresh(JWTAuth::getToken());

                return response()->json([
                    'access_token' => $newToken,
                    'token_type' => 'bearer',
                    'message' => 'Token renovado exitosamente'
                ], 200);

            } catch (Exception $e) {
                return response()->json([
                    'error' => 'No se pudo renovar el token. Inicie sesión nuevamente.'
                ], 401);
            }
        }



    /**
     * Genera un código aleatorio de 6 dígitos
     * @return string Código de 6 dígitos
     */
    public function makeCode()
    {
        // Genera bytes aleatorios y los convierte a hexadecimal
        $bytes = random_bytes(3); // 3 bytes = 6 caracteres hex
        $codigo = bin2hex($bytes);
        return $codigo;
    }

}