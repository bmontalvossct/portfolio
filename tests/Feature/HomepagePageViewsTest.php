<?php

namespace Tests\Feature;

use App\Support\Profile\HomepagePageViews;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class HomepagePageViewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_counts_each_page_request_and_returns_aggregates(): void
    {
        config(['app.timezone' => 'Asia/Manila']);
        Carbon::setTestNow(Carbon::parse('2026-11-01 18:00:00', 'UTC'));
        DB::table('homepage_page_views')->insert([
            ['day' => '2026-06-15', 'count' => 4],
            ['day' => '2026-10-31', 'count' => 2],
        ]);
        $this->withoutVite();

        $this->withHeader('User-Agent', 'Mozilla/5.0')->get('/')
            ->assertOk()->assertInertia(fn (Assert $page) => $page
                ->component('Profile/Show')
                ->has('visitStats.monthly', 6)
                ->where('visitStats.monthly.0.month', '2026-06')
                ->where('visitStats.monthly.0.count', 4)
                ->where('visitStats.monthly.5.month', '2026-11')
                ->where('visitStats.monthly.5.count', 1)
                ->where('visitStats.weekly.start', '2026-11-02')
                ->where('visitStats.weekly.count', 1)
                ->where('visitStats.trackingSince', '2026-06-15'));

        $this->get('/')->assertOk();
        $this->assertDatabaseHas('homepage_page_views', ['day' => '2026-11-02', 'count' => 2]);
        Carbon::setTestNow();
    }

    public function test_known_crawlers_and_head_requests_do_not_increment(): void
    {
        $counter = app(HomepagePageViews::class);
        $counter->recordAndStats(Request::create('/', 'GET', server: ['HTTP_USER_AGENT' => 'Googlebot']));
        $counter->recordAndStats(Request::create('/', 'HEAD'));
        $this->assertDatabaseCount('homepage_page_views', 0);
    }

    public function test_missing_table_returns_null_without_breaking_homepage(): void
    {
        Schema::drop('homepage_page_views');
        $this->withoutVite();
        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page->where('visitStats', null));
    }

    public function test_weekly_days_distinguish_untracked_zero_and_future_days(): void
    {
        config(['app.timezone' => 'UTC']);
        Carbon::setTestNow(Carbon::parse('2026-11-04 12:00:00', 'UTC'));
        DB::table('homepage_page_views')->insert(['day' => '2026-11-03', 'count' => 3]);

        $stats = app(HomepagePageViews::class)->recordAndStats(Request::create('/', 'HEAD'));
        $this->assertSame([
            ['date' => '2026-11-02', 'count' => null],
            ['date' => '2026-11-03', 'count' => 3],
            ['date' => '2026-11-04', 'count' => 0],
            ['date' => '2026-11-05', 'count' => null],
            ['date' => '2026-11-06', 'count' => null],
            ['date' => '2026-11-07', 'count' => null],
            ['date' => '2026-11-08', 'count' => null],
        ], $stats['weekly']['days']);
        $this->assertSame(3, $stats['weekly']['count']);
        Carbon::setTestNow();
    }

    public function test_weekly_days_are_null_when_tracking_has_not_started(): void
    {
        $stats = app(HomepagePageViews::class)->recordAndStats(Request::create('/', 'HEAD'));
        $this->assertNull($stats['trackingSince']);
        $this->assertSame(array_fill(0, 7, null), array_column($stats['weekly']['days'], 'count'));
    }
}
