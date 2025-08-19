<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');
        $credentials['password'] = Hash::make($credentials['password']);

        $user = \App\Models\User::where('email', $credentials['username'])->first();
        
        $tokenResult = $user->createToken('Personal Access Token');
        $accessToken = $tokenResult->accessToken;
        $refreshToken = $tokenResult->token->refresh_token ?? null;
        $expiresAt = $tokenResult->token->expires_at;
        $expirationMinutes = now()->diffInMinutes($expiresAt);

        $accessTokenCookie = Cookie::make(
            'access_token',
            $accessToken,
            $expirationMinutes,
            '/',
            null,
            true,
            true,
            false,
            'strict'
        );

        $refreshTokenCookie = null;
        if ($refreshToken) {
            $refreshTokenCookie = Cookie::make(
                'refresh_token',
                $refreshToken,
                60 * 24 * 30,
                '/',
                null,
                true,
                true,
                false,
                'strict'
            );
        }

        $response = response()->json([
            'status' => 'success',
            'message' => 'Login exitoso',
            'user' => [
                'id' => $user->id,
                'username' => $user->name,
                'email' => $user->email,
            ]
        ])->cookie($accessTokenCookie);

        if ($refreshTokenCookie) {
            $response = $response->cookie($refreshTokenCookie);
        }

        return $response;
    }
}
