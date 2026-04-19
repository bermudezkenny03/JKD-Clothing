<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        if (!$token = Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        $user = Auth::user()->load(['userDetail', 'role']);

        return response()->json([
            'message'      => 'Login successful',
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => config('jwt.ttl') * 60,
            'user'         => $user,
            'modules'      => $user->getModulesWithInfo(),
        ]);
    }

    public function logout()
    {
        Auth::logout();

        return response()->json([
            'message' => 'Session closed',
        ]);
    }

    public function me()
    {
        $user = Auth::user()->load(['userDetail', 'role']);

        return response()->json([
            'message' => 'Authenticated user retrieved successfully',
            'user'    => $user,
            'modules' => $user->getModulesWithInfo(),
        ]);
    }

    public function refresh()
    {
        return response()->json([
            'access_token' => Auth::refresh(),
            'token_type'   => 'bearer',
            'expires_in'   => config('jwt.ttl') * 60,
        ]);
    }
}
