<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Support\Profile\DesignMediaArchive;
use Inertia\Inertia;
use Inertia\Response;

class DesignMediaArchiveController extends Controller
{
    public function __invoke(DesignMediaArchive $archive): Response
    {
        $profile = Profile::query()->firstOrFail();

        return Inertia::render('Profile/DesignMediaArchive', [
            'items' => $archive->all(),
            'profile' => [
                'display_name' => $profile->display_name,
                'avatar_url' => $profile->avatar_url,
                'canva_url' => $profile->canva_url,
            ],
        ]);
    }
}
