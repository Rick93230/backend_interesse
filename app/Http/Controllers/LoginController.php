<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Laravel\Passport\Token;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], 422);
            }

            if (!Auth::attempt($request->only('email', 'password'))) {
                return response()->json([
                    'message' => 'Credenciales incorrectas'
                ], 401);
            }

            $user = Auth::user();

            $tokenResult = $user->createToken('Personal Access Token');
            $token = $tokenResult->token;
            
            if ($request->remember_me) {
                $token->expires_at = Carbon::now()->addWeeks(1);
            } else {
                $token->expires_at = Carbon::now()->addHours(1);
            }
            
            $token->save();

            return response()->json([
                'message' => 'Login exitoso',
                'access_token' => $tokenResult->accessToken,
                'token_type' => 'Bearer',
                'expires_at' => Carbon::parse($token->expires_at)->toDateTimeString(),
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->roles->pluck('name')
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request){
        try {
            
            $request->user()->token()->revoke();

            return response()->json([
                'message' => 'Logout exitoso'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al hacer logout',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function user(Request $request)
    {
        try {
            return response()->json([
                'user' => $request->user()
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
