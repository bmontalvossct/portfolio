<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventVersionedContentMutation
{
    public function handle(Request $request, Closure $next): Response
    {
        abort(409, 'This content is managed in the repository. Update the versioned portfolio content and deploy it to change the public site.');
    }
}
