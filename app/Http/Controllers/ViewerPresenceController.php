<?php

namespace App\Http\Controllers;

use App\Support\Profile\ActiveViewerCounter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ViewerPresenceController extends Controller
{
    public function __invoke(Request $request, ActiveViewerCounter $viewers): JsonResponse
    {
        $viewerId = hash_hmac(
            'sha256',
            $request->session()->getId(),
            (string) config('app.key'),
        );
        $activeViewerCount = $viewers->touch($viewerId);

        return response()
            ->json([
                'count' => $viewers->displayCount($activeViewerCount),
                'expires_in' => ActiveViewerCounter::ACTIVE_WINDOW_SECONDS,
            ])
            ->header('Cache-Control', 'no-store, private');
    }
}
