<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GoogleAiCredlyBadgesMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_adds_the_eight_verified_badges_with_exact_public_metadata(): void
    {
        $expected = [
            '6b9517c0-4f34-44b2-ac5e-8b187218448a' => ['Google AI Professional Certificate', '782f33e7-c4b5-4d1d-a7ce-02f25b7b9a1e'],
            '867c3728-1b67-4a39-b377-c0a780fb180b' => ['Google AI for App Building', '2cf18bea-6b75-401a-bf9d-ac0a23f582fd'],
            'daf1b63f-a887-4c3b-bf75-848f4cecc69d' => ['Google AI for Brainstorming and Planning', 'fb6dc8be-1471-4aab-b2ab-da1f76080ecf'],
            '8d9f4d51-7b24-4a8e-9ec9-6a5a95bbf90e' => ['Google AI for Content Creation', 'f0c5c16d-1480-4968-9259-50851d03f79a'],
            'ecf91e1f-1db3-4d3d-b9d6-3a4e721ef0a5' => ['Google AI for Data Analysis', '97e068dc-d64a-4806-a6cd-5c4e68fa3d5f'],
            '6c65fe3a-9263-40bf-9105-a6bdd2a26c69' => ['Google AI for Research and Insights', 'c065d009-d51b-40f8-8f9b-4b90cb4ece67'],
            '61d6c68d-9e35-4223-a9a2-95ea7ec98c8c' => ['Google AI for Writing and Communicating', '791a77e5-1590-40bc-92a9-c69126fa4e4b'],
            '649fcb31-788c-4c1c-8b19-ad652dfaa4e0' => ['Google AI Fundamentals', '36ecc7ff-8ccf-4bd8-83a7-6f35797e0b64'],
        ];

        $this->assertSame(8, DB::table('credential_badges')->count());

        foreach ($expected as $externalId => [$title, $imageId]) {
            $badge = DB::table('credential_badges')->where('external_id', $externalId)->first();
            $this->assertNotNull($badge);
            $this->assertSame('credly', $badge->provider);
            $this->assertSame($title, $badge->name);
            $this->assertSame('Coursera', $badge->issuer);
            $this->assertSame('2026-10-05', $badge->issued_at);
            $this->assertNull($badge->expires_at);
            $this->assertSame('https://images.credly.com/images/'.$imageId.'/blob', $badge->image_url);
            $this->assertSame('https://www.credly.com/badges/'.$externalId, $badge->certificate_url);
            $this->assertSame('ai_data', $badge->category);
            $this->assertNotEmpty($badge->description);
            $this->assertNotEmpty(json_decode($badge->skills, true));
            $this->assertSame($title === 'Google AI Professional Certificate', (bool) $badge->is_featured);
        }

        $certificate = DB::table('credential_badges')->where('external_id', '6b9517c0-4f34-44b2-ac5e-8b187218448a')->first();
        $this->assertCount(10, json_decode($certificate->skills, true));
        $this->assertContains('Responsible AI', json_decode($certificate->skills, true));
    }

    public function test_it_adds_only_missing_badges_and_preserves_existing_admin_metadata_and_order(): void
    {
        $certificateId = '6b9517c0-4f34-44b2-ac5e-8b187218448a';
        // Remove only new migration fixtures from the isolated in-memory database.
        DB::table('credential_badges')->where('external_id', '!=', $certificateId)->delete();
        DB::table('credential_badges')->where('external_id', $certificateId)->update([
            'name' => 'Admin-edited certificate title',
            'image_url' => '/custom-image.png',
            'description' => 'Custom description.',
            'sort_order' => 77,
            'is_featured' => false,
            'synced_at' => '2026-11-01 12:00:00',
        ]);
        $originalId = DB::table('credential_badges')->insertGetId([
            'provider' => 'credly', 'external_id' => 'existing-badge', 'name' => 'Existing badge',
            'sort_order' => 100, 'is_featured' => true,
        ]);
        $otherProviderId = DB::table('credential_badges')->insertGetId([
            'provider' => 'another-provider', 'external_id' => '867c3728-1b67-4a39-b377-c0a780fb180b',
            'name' => 'Other provider badge', 'sort_order' => 10,
        ]);
        $before = DB::table('credential_badges')->orderBy('id')->get();
        $beforeIds = $before->pluck('id');

        $this->migration()->up();

        $this->assertEquals($before, DB::table('credential_badges')->whereIn('id', $beforeIds)->orderBy('id')->get());
        $this->assertSame(9, DB::table('credential_badges')->where('provider', 'credly')->count());
        $this->assertSame(10, DB::table('credential_badges')->count());
        $this->assertSame(range(101, 107), DB::table('credential_badges')->whereNotIn('id', $beforeIds)->orderBy('sort_order')->pluck('sort_order')->all());
        $this->assertSame(100, DB::table('credential_badges')->find($originalId)->sort_order);
        $this->assertSame(10, DB::table('credential_badges')->find($otherProviderId)->sort_order);

        $after = DB::table('credential_badges')->orderBy('id')->get();
        $this->travel(1)->hour();
        $this->migration()->up();
        $this->assertEquals($after, DB::table('credential_badges')->orderBy('id')->get());
    }

    public function test_rollback_keeps_earned_badges_and_later_admin_edits(): void
    {
        DB::table('credential_badges')->where('external_id', '6b9517c0-4f34-44b2-ac5e-8b187218448a')->update(['description' => 'Later admin edit.']);
        $before = DB::table('credential_badges')->orderBy('id')->get();

        $this->migration()->down();

        $this->assertEquals($before, DB::table('credential_badges')->orderBy('id')->get());
    }

    private function migration(): object
    {
        return require database_path('migrations/2026_11_14_000001_add_google_ai_credly_badges.php');
    }
}
