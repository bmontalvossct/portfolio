<?php

namespace App\Http\Controllers;

use App\Models\GuestbookEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GuestbookController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        if ($request->filled('website')) {
            return back()->with('success', 'Thanks for sharing your review.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'role_or_organization' => ['nullable', 'string', 'max:180'],
            'body' => ['required', 'string', 'min:12', 'max:1500'],
            'rating' => ['nullable', 'integer', 'between:1,5'],
            'website' => ['nullable', 'string', 'max:0'],
        ]);

        GuestbookEntry::query()->create([
            ...$validated,
            'public_id' => (string) str()->uuid(),
            'status' => 'pending',
            'ip_hash' => hash_hmac('sha256', (string) $request->ip(), (string) config('app.key')),
        ]);

        return back()->with('success', 'Thanks for sharing your review.');
    }
}
