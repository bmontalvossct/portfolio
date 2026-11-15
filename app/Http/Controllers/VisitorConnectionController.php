<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VisitorConnectionController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $ip = $request->ip();
        $publicIp = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_GLOBAL_RANGE);

        return response()->json(['ip' => $publicIp ?: null])
            ->header('Cache-Control', 'private, no-store, max-age=0');
    }
}
