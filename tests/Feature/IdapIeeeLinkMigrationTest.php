<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class IdapIeeeLinkMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_adds_the_link_only_to_the_matching_work_without_replacing_custom_links(): void
    {
        $title = 'Forecasting Child Immunization Coverage and Predicting Underperformance Risk Using a Hybrid Time-Series Framework: Evidence from FHSIS Data';
        $target = DB::table('published_works')->insertGetId(['type' => 'scopus_paper', 'title' => $title]);
        $other = DB::table('published_works')->insertGetId(['type' => 'scopus_paper', 'title' => 'Another paper']);
        $migration = require database_path('migrations/2026_11_08_000001_add_idap_ieee_link.php');

        $migration->up();
        $this->assertSame('https://ieeexplore.ieee.org/document/11727904', DB::table('published_works')->where('id', $target)->value('external_url'));
        $this->assertNull(DB::table('published_works')->where('id', $other)->value('external_url'));

        DB::table('published_works')->where('id', $target)->update(['external_url' => 'https://example.org/custom']);
        $migration->up();
        $migration->down();
        $this->assertSame('https://example.org/custom', DB::table('published_works')->where('id', $target)->value('external_url'));
    }
}
