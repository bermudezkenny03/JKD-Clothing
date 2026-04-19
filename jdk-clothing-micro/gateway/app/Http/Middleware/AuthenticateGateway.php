<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthenticateGateway
{
    // Este middleware se encarga de validar el token JWT presente en la solicitud, decodificarlo y extraer la información del usuario autenticado.
    public function handle(Request $request, Closure $next): mixed
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'message' => 'Token not provided.',
                'error'   => 'MISSING_TOKEN',
            ], 401);
        }

        $payload = $this->decodeJWT($token);

        if (!$payload) {
            return response()->json([
                'message' => 'Invalid or expired token.',
                'error'   => 'INVALID_TOKEN',
            ], 401);
        }

        $request->merge(['_gateway_user' => $payload]);

        Log::info('[Gateway Auth] Token valid', [
            'user_id' => $payload['sub']   ?? null,
            'email'   => $payload['email'] ?? null,
        ]);

        return $next($request);
    }

    // Este método se encarga de decodificar el token JWT, verificar su firma y validar su contenido (expiración, algoritmo, etc.).
    private function decodeJWT(string $token): ?array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return null;
        }

        try {
            $header  = json_decode($this->base64UrlDecode($parts[0]), true);
            $payload = json_decode($this->base64UrlDecode($parts[1]), true);

            if (!$header || !$payload) {
                return null;
            }

            if (($header['alg'] ?? '') !== 'HS256') {
                Log::warning('[Gateway Auth] Unsupported algorithm', [
                    'alg' => $header['alg'] ?? 'none'
                ]);
                return null;
            }

            $secret    = env('JWT_SECRET');
            $signature = $this->base64UrlDecode($parts[2]);
            $expected  = hash_hmac('sha256', $parts[0] . '.' . $parts[1], $secret, true);

            if (!hash_equals($expected, $signature)) {
                Log::warning('[Gateway Auth] Invalid signature');
                return null;
            }

            if (isset($payload['exp']) && $payload['exp'] < time()) {
                Log::info('[Gateway Auth] Token expired');
                return null;
            }

            if (isset($payload['nbf']) && $payload['nbf'] > time()) {
                return null;
            }

            return $payload;
        } catch (\Exception $e) {
            Log::error('[Gateway Auth] JWT decode error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    // Este método se encarga de decodificar una cadena codificada en base64url, que es el formato utilizado en los tokens JWT.
    private function base64UrlDecode(string $input): string
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $input .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }
}
