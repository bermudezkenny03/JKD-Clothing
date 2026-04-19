<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogRequests
{
    // Este middleware se encarga de registrar información detallada sobre cada solicitud entrante, incluyendo el método HTTP, la ruta, la dirección IP del cliente, el agente de usuario y el tiempo que tarda en procesar la solicitud.
    public function handle(Request $request, Closure $next): mixed
    {
        $startTime = microtime(true);

        $response = $next($request);

        $duration = round((microtime(true) - $startTime) * 1000, 2);

        Log::info('[Gateway] Request completed', [
            'method'      => $request->method(),
            'path'        => $request->path(),
            'status'      => $response->getStatusCode(),
            'duration_ms' => $duration,
            'ip'          => $request->ip(),
            'user_agent'  => $request->userAgent(),
        ]);

        $response->headers->set('X-Response-Time', $duration . 'ms');

        return $response;
    }
}
