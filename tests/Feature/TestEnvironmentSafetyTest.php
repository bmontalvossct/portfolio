<?php

namespace Tests\Feature;

use Tests\TestCase;

class TestEnvironmentSafetyTest extends TestCase
{
    public function test_phpunit_is_isolated_from_the_portfolio_database(): void
    {
        $this->assertTrue(app()->environment('testing'));
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->assertFalse(app()->configurationIsCached());
    }
}
