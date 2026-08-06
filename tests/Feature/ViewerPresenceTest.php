<?php

namespace Tests\Feature;

use App\Support\Profile\ActiveViewerCounter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ViewerPresenceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['cache.default' => 'array']);
        Cache::clear();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_presence_counter_counts_unique_active_viewers_and_expires_inactive_ones(): void
    {
        Carbon::setTestNow('2026-08-06 12:00:00');
        $counter = app(ActiveViewerCounter::class);

        $this->assertSame(1, $counter->touch('viewer-one'));
        $this->assertSame(1, $counter->touch('viewer-one'));
        $this->assertSame(2, $counter->touch('viewer-two'));

        Carbon::setTestNow(now()->addSeconds(ActiveViewerCounter::ACTIVE_WINDOW_SECONDS + 1));

        $this->assertSame(1, $counter->touch('viewer-three'));
    }

    public function test_display_count_uses_a_bounded_fluctuating_baseline_plus_real_viewers(): void
    {
        Carbon::setTestNow('2026-08-06 12:00:00');
        $counter = app(ActiveViewerCounter::class);

        $this->assertSame(28, $counter->displayCount(3));

        foreach (range(1, 20) as $step) {
            Carbon::setTestNow(now()->addSeconds(ActiveViewerCounter::BASELINE_UPDATE_SECONDS + 1));
            $displayCount = $counter->displayCount(3);

            $this->assertGreaterThanOrEqual(ActiveViewerCounter::BASELINE_FLOOR + 3, $displayCount);
            $this->assertLessThanOrEqual(ActiveViewerCounter::BASELINE_CEILING + 3, $displayCount);
        }
    }

    public function test_presence_heartbeat_returns_the_current_count_without_caching(): void
    {
        $this->postJson('/viewer-presence')
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertJson([
                'count' => ActiveViewerCounter::BASELINE_CEILING + 1,
                'expires_in' => ActiveViewerCounter::ACTIVE_WINDOW_SECONDS,
            ]);
    }
}
