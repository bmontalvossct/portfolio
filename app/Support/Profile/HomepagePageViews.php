<?php

namespace App\Support\Profile;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HomepagePageViews
{
    public function recordAndStats(Request $request): ?array
    {
        try {
            if (! Schema::hasTable('homepage_page_views')) {
                return null;
            }

            $today = Carbon::now(config('app.timezone'))->startOfDay();
            $day = $today->toDateString();

            // This excludes common crawlers, but page views are not unique visitors.
            if ($request->isMethod('GET') && ! preg_match('/bot|crawler|spider|slurp|bingpreview/i', $request->userAgent() ?? '')) {
                DB::table('homepage_page_views')->insertOrIgnore(['day' => $day, 'count' => 0]);
                DB::table('homepage_page_views')->where('day', $day)->increment('count');
            }

            $weekStart = $today->copy()->startOfWeek(Carbon::MONDAY)->toDateString();
            $firstMonth = $today->copy()->startOfMonth()->subMonths(5);
            $rows = DB::table('homepage_page_views')
                ->where('day', '>=', min($firstMonth->toDateString(), $weekStart))
                ->where('day', '<=', $day)->get();
            $monthly = [];
            for ($i = 0; $i < 6; $i++) {
                $month = $firstMonth->copy()->addMonths($i)->format('Y-m');
                $monthly[] = ['month' => $month, 'count' => (int) $rows->filter(fn ($row) => str_starts_with((string) $row->day, $month))->sum('count')];
            }

            $trackingSince = DB::table('homepage_page_views')->min('day');
            $dailyCounts = $rows->keyBy('day');
            $days = [];
            for ($i = 0; $i < 7; $i++) {
                $date = Carbon::parse($weekStart, config('app.timezone'))->addDays($i)->toDateString();
                $days[] = [
                    'date' => $date,
                    'count' => $trackingSince === null || $date < $trackingSince || $date > $day
                        ? null
                        : (int) ($dailyCounts->get($date)->count ?? 0),
                ];
            }

            return [
                'monthly' => $monthly,
                'weekly' => ['start' => $weekStart, 'count' => (int) $rows->filter(fn ($row) => (string) $row->day >= $weekStart)->sum('count'), 'days' => $days],
                'trackingSince' => $trackingSince,
            ];
        } catch (QueryException $exception) {
            // Counting must never prevent the portfolio from loading.
            return null;
        }
    }
}
