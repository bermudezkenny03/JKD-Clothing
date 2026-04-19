<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CorsGateway
{
    // Este middleware se encarga de manejar las solicitudes CORS, agregando los encabezados necesarios para permitir el acceso desde diferentes orígenes.
    public function handle(Request $request, Closure $next): mixed
    {
        $allowedOrigins = config('gateway.cors.allowed_origins', ['*']);
        $origin         = $request->header('Origin', '');
        $isAllowed      = in_array('*', $allowedOrigins) || in_array($origin, $allowedOrigins);

        if ($request->isMethod('OPTIONS')) {
            return response('', 204)->withHeaders([
                'Access-Control-Allow-Origin'  => $isAllowed ? $origin : '',
                'Access-Control-Allow-Methods' => implode(', ', config('gateway.cors.allowed_methods')),
                'Access-Control-Allow-Headers' => implode(', ', config('gateway.cors.allowed_headers')),
                'Access-Control-Max-Age'       => '86400',
            ]);
        }

        $response = $next($request);

        if ($isAllowed) {
            $response->headers->set('Access-Control-Allow-Origin',  $origin ?: '*');
            $response->headers->set('Access-Control-Allow-Methods', implode(', ', config('gateway.cors.allowed_methods')));
            $response->headers->set('Access-Control-Allow-Headers', implode(', ', config('gateway.cors.allowed_headers')));
        }

        return $response;
    }
}
