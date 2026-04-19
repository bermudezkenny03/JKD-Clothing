<?php

namespace App\Http\Controllers;

use App\Services\ProxyService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class GatewayController extends Controller
{
    public function __construct(private ProxyService $proxy) {}

    public function userProxy(Request $request, string $path = ''): SymfonyResponse
    {
        $fullPath = $this->resolvePath($request);
        return $this->proxy->forward($request, 'user', $fullPath);
    }

    public function catalogProxy(Request $request, string $path = ''): SymfonyResponse
    {
        $fullPath = $this->resolvePath($request, 'catalog');
        return $this->proxy->forward($request, 'catalog', $fullPath);
    }

    private function resolvePath(Request $request, string $stripSegment = ''): string
    {
        $path = $request->path();
        $path = preg_replace('#^api/#', '', $path);

        if ($stripSegment) {
            $path = preg_replace('#^' . preg_quote($stripSegment, '#') . '/?#', '', $path);
        }

        return trim($path, '/');
    }
}
