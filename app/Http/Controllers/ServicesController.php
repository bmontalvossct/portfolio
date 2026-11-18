<?php

namespace App\Http\Controllers;

use App\Support\Profile\VersionedPortfolioContent;
use Inertia\Inertia;
use Inertia\Response;

class ServicesController extends Controller
{
    public function __invoke(): Response
    {
        $profile = (object) app(VersionedPortfolioContent::class)->get('profile');

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
