<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ProxyService
{
    // Este método se encarga de recibir la solicitud del cliente, construir la URL del servicio destino,
    public function forward(Request $request, string $service, string $path): SymfonyResponse
    {
        $config  = config("gateway.services.{$service}");
        $baseUrl = rtrim($config['url'], '/');
        $prefix  = $config['prefix'];

        $cleanPath = ltrim($path, '/');
        $targetUrl = "{$baseUrl}/{$prefix}/" . $cleanPath;

        $targetUrl = rtrim($targetUrl, '/');

        if ($request->query()) {
            $targetUrl .= '?' . http_build_query($request->query());
        }

        Log::info('[Gateway] Forwarding request', [
            'method'  => $request->method(),
            'from'    => $request->url(),
            'to'      => $targetUrl,
            'service' => $service,
            'ip'      => $request->ip(),
        ]);

        try {
            $http = Http::timeout($config['timeout'])
                ->connectTimeout($config['connect_timeout'] ?? 3)
                ->withHeaders($this->buildHeaders($request));

            if ($request->hasFile('image') || $request->hasFile('file')) {
                $response = $this->forwardWithFiles($http, $request, $targetUrl);
            } else {
                $response = $http->{strtolower($request->method())}(
                    $targetUrl,
                    $request->all()
                );
            }

            Log::info('[Gateway] Response received', [
                'status'  => $response->status(),
                'service' => $service,
            ]);

            return response($response->body(), $response->status())
                ->withHeaders([
                    'Content-Type' => $response->header('Content-Type', 'application/json'),
                    'X-Gateway'    => 'JDK-Gateway/1.0',
                    'X-Service'    => $service,
                ]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('[Gateway] Service unreachable', [
                'service' => $service,
                'url'     => $targetUrl,
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'message' => "Service '{$service}' is currently unavailable.",
                'error'   => 'SERVICE_UNAVAILABLE',
            ], 503);
        } catch (\Exception $e) {
            Log::error('[Gateway] Unexpected error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'An unexpected error occurred in the gateway.',
                'error'   => 'GATEWAY_ERROR',
            ], 500);
        }
    }

    // Este método construye los encabezados que se enviarán al servicio destino, incluyendo información del usuario autenticado.
    private function buildHeaders(Request $request): array
    {
        $headers = [
            'Accept'            => 'application/json',
            'X-Forwarded-For'   => $request->ip(),
            'X-Forwarded-Host'  => $request->getHost(),
            'X-Request-ID'      => (string) \Illuminate\Support\Str::uuid(),
            'X-Gateway-Version' => '1.0',
        ];

        if ($token = $request->bearerToken()) {
            $headers['Authorization'] = "Bearer {$token}";
        }

        if ($user = $request->get('_gateway_user')) {
            $headers['X-User-Id']    = $user['sub']     ?? '';
            $headers['X-User-Email'] = $user['email']   ?? '';
            $headers['X-User-Role']  = $user['role_id'] ?? '';
        }

        return $headers;
    }

    // Este método se encarga de manejar las solicitudes que incluyen archivos adjuntos, utilizando el método attach de Laravel HTTP Client.
    private function forwardWithFiles($http, Request $request, string $url): SymfonyResponse
    {
        $files = [];
        foreach ($request->allFiles() as $key => $file) {
            $files[$key] = fopen($file->getRealPath(), 'r');
        }

        return $http->attach($files)->post($url, $request->except(array_keys($files)));
    }
}
