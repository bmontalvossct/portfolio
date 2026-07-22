<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePortfolioAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if ((bool) $request->session()->get('portfolio_admin_authenticated', false)) {
            return $next($request);
        }

        return redirect()->route('admin.login');
    }
}
