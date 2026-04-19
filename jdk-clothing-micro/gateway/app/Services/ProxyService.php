<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Http\Client\ConnectionException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ProxyService
{
    public function forward(Request $request, string $service, string $path): SymfonyResponse
    {
        $config  = config("gateway.services.{$service}");
        $baseUrl = rtrim($config['url'], '/');
        $prefix  = $config['prefix'];

        $targetUrl = $path
            ? "{$baseUrl}/{$prefix}/{$path}"
            : "{$baseUrl}/{$prefix}";

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

            $response = $request->hasFile('image') || $request->hasFile('file')
                ? $this->forwardWithFiles($http, $request, $targetUrl)
                : $http->{strtolower($request->method())}($targetUrl, $request->all());

            Log::info('[Gateway] Response received', [
                'status'  => $response->status(),
                'service' => $service,
                'to'      => $targetUrl,
            ]);

            return response($response->body(), $response->status())
                ->withHeaders([
                    'Content-Type' => $response->header('Content-Type', 'application/json'),
                    'X-Gateway'    => 'JDK-Gateway/1.0',
                    'X-Service'    => $service,
                ]);

        } catch (ConnectionException $e) {
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

    private function buildHeaders(Request $request): array
    {
        $headers = [
            'Accept'            => 'application/json',
            'X-Forwarded-For'   => $request->ip(),
            'X-Forwarded-Host'  => $request->getHost(),
            'X-Request-ID'      => (string) Str::uuid(),
            'X-Gateway-Version' => '1.0',
        ];

        if ($token = $request->bearerToken()) {
            $headers['Authorization'] = "Bearer {$token}";
        }

        if ($user = $request->get('_gateway_user')) {
            $headers['X-User-Id']      = $user['sub']       ?? '';
            $headers['X-User-Email']   = $user['email']     ?? '';
            $headers['X-User-Name']    = $user['name']      ?? '';
            $headers['X-User-Role']    = $user['role']      ?? '';
            $headers['X-User-Role-Id'] = $user['role_id']   ?? ''; 
            $headers['X-User-Status']  = $user['status']    ?? '';
        }

        return $headers;
    }

    private function forwardWithFiles($http, Request $request, string $url): SymfonyResponse
    {
        $files = [];
        foreach ($request->allFiles() as $key => $file) {
            $files[$key] = fopen($file->getRealPath(), 'r');
        }

        return $http->attach($files)->post($url, $request->except(array_keys($files)));
    }
}