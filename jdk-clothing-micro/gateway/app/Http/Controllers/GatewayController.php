<?php

namespace App\Http\Controllers;

use App\Services\ProxyService;
use Illuminate\Http\Request;

class GatewayController extends Controller
{
    public function __construct(private ProxyService $proxy) {}

    public function userProxy(Request $request, string $path = '')
    {
        return $this->proxy->forward($request, 'user', $path);
    }

    public function catalogProxy(Request $request, string $path = '')
    {
        return $this->proxy->forward($request, 'catalog', $path);
    }
}