<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (!$token = Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid credentials',
                'error'   => 'INVALID_CREDENTIALS',
            ], 401);
        }

        $user = Auth::user()->load(['userDetail', 'role']);

        return $this->respondWithToken($token, $user);
    }

    public function logout()
    {
        try {
            Auth::logout();

            return response()->json([
                'message' => 'Session closed successfully',
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'message' => 'Failed to logout.',
                'error'   => 'LOGOUT_FAILED',
            ], 500);
        }
    }

    public function me()
    {
        try {
            $user = Auth::user()->load(['userDetail', 'role']);

            return response()->json([
                'message' => 'Authenticated user retrieved successfully',
                'user'    => $user,
                'modules' => $user->getModulesWithInfo(),
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'message' => 'Token error.',
                'error'   => 'TOKEN_ERROR',
            ], 401);
        }
    }

    public function refresh()
    {
        try {
            $newToken = Auth::refresh();

            return $this->respondWithToken($newToken);
        } catch (TokenExpiredException $e) {
            return response()->json([
                'message' => 'Refresh token expired. Please login again.',
                'error'   => 'REFRESH_TOKEN_EXPIRED',
            ], 401);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'message' => 'Token is invalid.',
                'error'   => 'INVALID_TOKEN',
            ], 401);
        } catch (JWTException $e) {
            return response()->json([
                'message' => 'Could not refresh token.',
                'error'   => 'REFRESH_FAILED',
            ], 500);
        }
    }

    private function respondWithToken(string $token, $user = null): JsonResponse
    {
        $ttlMinutes = config('jwt.ttl');

        $response = [
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => $ttlMinutes * 60,
            'expires_at'   => now()->addMinutes($ttlMinutes)->toIso8601String(),
        ];

        if ($user) {
            $response['user']    = $user;
            $response['modules'] = $user->getModulesWithInfo();
        }

        return response()->json($response);
    }
}
