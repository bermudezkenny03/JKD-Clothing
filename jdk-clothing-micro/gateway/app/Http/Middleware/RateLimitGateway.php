<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RateLimitGateway
{
    // Este middleware se encarga de limitar la cantidad de solicitudes que un cliente puede realizar en un período de tiempo determinado, utilizando la dirección IP del cliente como identificador para aplicar la limitación.
    public function handle(Request $request, Closure $next): mixed
    {
        $maxRequests  = config('gateway.rate_limit.max_requests', 60);
        $decaySeconds = config('gateway.rate_limit.decay_seconds', 60);
        $key          = 'rate_limit_' . $request->ip();
        $hits         = Cache::get($key, 0);

        if ($hits >= $maxRequests) {
            return response()->json([
                'message'     => 'Too many requests. Please slow down.',
                'error'       => 'RATE_LIMIT_EXCEEDED',
                'retry_after' => $decaySeconds,
            ], 429)->withHeaders([
                'X-RateLimit-Limit'     => $maxRequests,
                'X-RateLimit-Remaining' => 0,
                'Retry-After'           => $decaySeconds,
            ]);
        }

        if ($hits === 0) {
            Cache::put($key, 1, now()->addSeconds($decaySeconds));
        } else {
            Cache::increment($key);
        }

        return $next($request)->withHeaders([
            'X-RateLimit-Limit'     => $maxRequests,
            'X-RateLimit-Remaining' => max(0, $maxRequests - $hits - 1),
        ]);
    }
}
