<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AdminAuthController extends Controller
{
    public function create(Request $request): Response|RedirectResponse
    {
        if ((bool) $request->session()->get('portfolio_admin_authenticated', false)) {
            return redirect()->route('admin.index');
        }

        $profile = Profile::query()->first();

        return Inertia::render('Admin/Login', [
            'profile' => [
                'display_name' => $profile?->display_name ?? 'Britt Kristoff B. Montalvo, MSIT',
                'avatar_url' => $profile?->avatar_url,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'max:255'],
        ]);

        $expected = (string) config('portfolio.admin_token');

        if ($expected === '' || ! hash_equals($expected, $validated['token'])) {
            throw ValidationException::withMessages([
                'token' => 'The access token is not valid.',
            ]);
        }

        $request->session()->regenerate();
        $request->session()->put('portfolio_admin_authenticated', true);

        return redirect()->intended(route('admin.index'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->forget('portfolio_admin_authenticated');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
