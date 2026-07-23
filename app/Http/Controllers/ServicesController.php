<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Inertia\Inertia;
use Inertia\Response;

class ServicesController extends Controller
{
    public function __invoke(): Response
    {
        $profile = Profile::query()->firstOrFail();

        return Inertia::render('Profile/Services', [
            'profile' => [
                'display_name' => $profile->display_name,
                'avatar_url' => $profile->avatar_url,
                'email' => $profile->email,
                'location' => $profile->location,
                'availability' => $profile->availability,
            ],
            'services' => config('service_catalog'),
        ]);
    }
}
