<?php

namespace Tests\Feature;

use App\Mail\GuestbookEntrySubmitted;
use App\Models\Achievement;
use App\Models\CredentialBadge;
use App\Models\DesignMedia;
use App\Models\Education;
use App\Models\GuestbookEntry;
use App\Models\PortfolioTool;
use App\Models\Profile;
use App\Models\Project;
use App\Models\WorkExperience;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminPortfolioTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['portfolio.admin_token' => 'test-portfolio-token']);
        $this->withoutVite();
        $this->seed();
    }

    public function test_admin_pages_require_the_secret_token(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');

        $this->get('/admin/login')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Login')
                ->where('profile.avatar_url', '/images/profile-avatar.jpg'));

        $this->post('/admin/login', ['token' => 'incorrect'])
            ->assertSessionHasErrors('token');

        $this->post('/admin/login', ['token' => 'test-portfolio-token'])
            ->assertRedirect('/admin')
            ->assertSessionHas('portfolio_admin_authenticated', true);

        $this->withSession(['portfolio_admin_authenticated' => true])
            ->get('/admin')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Index')
                ->has('projects', 6)
                ->where('projects.0.title', 'Governed MCH Forecasting and Risk Prediction DSS')
                ->has('publishedWorks', 3)
                ->where('publishedWorks.0.publication', 'Accepted by IDAP (International Data & Analytics Proceedings)')
                ->where('publishedWorks.1.title', 'A Hybrid Time-Series Forecasting and Underperformance Risk Prediction System for Child Immunization Coverage in Surigao del Norte, Philippines: Evidence from FHSIS Quarterly Data')
                ->where('publishedWorks.2.title', 'SURe-Health: PDOHO-SDN Official Publication')
                ->has('portfolioTools', 37)
                ->has('achievements', 6)
                ->has('credentialBadges', 38)
                ->has('education', 2)
                ->has('workExperiences', 5)
                ->where('pendingReviewCount', 0)
            );
    }

    public function test_admin_sees_pending_review_badge_and_can_retry_a_notification(): void
    {
        Mail::fake();
        $session = ['portfolio_admin_authenticated' => true];
        $pending = GuestbookEntry::query()->create([
            'public_id' => (string) Str::uuid(),
            'name' => 'Pending Reviewer',
            'email' => 'pending@example.test',
            'body' => 'A pending review awaiting moderation and notification.',
            'rating' => 5,
            'status' => 'pending',
            'notification_status' => 'failed',
            'notification_error' => 'Previous transport failure.',
        ]);
        $pending->forceFill(['created_at' => now()->addMinute()])->saveQuietly();
        GuestbookEntry::query()->create([
            'public_id' => (string) Str::uuid(),
            'name' => 'Second Pending Reviewer',
            'email' => 'second@example.test',
            'body' => 'Another pending review awaiting approval.',
            'status' => 'pending',
        ]);
        GuestbookEntry::query()->create([
            'public_id' => (string) Str::uuid(),
            'name' => 'Approved Reviewer',
            'email' => 'approved@example.test',
            'body' => 'An already approved review.',
            'status' => 'approved',
        ]);

        $this->withSession($session)
            ->get('/admin')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('pendingReviewCount', 2)
                ->has('guestbookEntries', 3)
                ->where('guestbookEntries.0.id', $pending->id)
                ->where('guestbookEntries.0.notification_status', 'failed')
                ->where('guestbookEntries.0.notification_error', 'Previous transport failure.'));

        $this->withSession(['portfolio_admin_authenticated' => false])->post("/admin/guestbook/{$pending->id}/notification")->assertRedirect('/admin/login');

        $this->withSession($session)
            ->post("/admin/guestbook/{$pending->id}/notification")
            ->assertRedirect()
            ->assertSessionHas('success', 'Review notification was accepted by the configured mail transport.');

        Mail::assertSent(GuestbookEntrySubmitted::class, fn (GuestbookEntrySubmitted $mail): bool => $mail->entry->is($pending) && $mail->hasTo('inquiries@brittmontalvo.dev')
        );
        $this->assertSame('accepted', $pending->refresh()->notification_status);
        $this->assertNull($pending->notification_error);
        $this->assertNotNull($pending->notification_accepted_at);
    }

    public function test_admin_can_edit_hide_and_delete_an_automatically_published_review(): void
    {
        $session = ['portfolio_admin_authenticated' => true];
        $entry = GuestbookEntry::query()->create([
            'public_id' => (string) Str::uuid(),
            'name' => 'Original Reviewer',
            'email' => 'original@example.test',
            'role_or_organization' => 'Original organization',
            'body' => 'An automatically published review ready for an administrator to edit.',
            'rating' => 5,
            'status' => 'approved',
            'approved_at' => now(),
        ]);
        $edited = [
            'name' => 'Edited Reviewer',
            'email' => 'edited@example.test',
            'role_or_organization' => 'Research collaborator',
            'body' => 'The updated review accurately describes the systems and research work.',
            'rating' => 4,
            'status' => 'approved',
            'admin_reply' => 'Thank you for the thoughtful review.',
        ];

        $this->withSession($session)
            ->put("/admin/guestbook/{$entry->id}", $edited)
            ->assertRedirect()
            ->assertSessionHas('success', 'Review updated.');

        $entry->refresh();
        $this->assertSame('Edited Reviewer', $entry->name);
        $this->assertSame('edited@example.test', $entry->email);
        $this->assertSame('Research collaborator', $entry->role_or_organization);
        $this->assertSame(4, $entry->rating);
        $this->assertNotNull($entry->approved_at);
        $this->assertNotNull($entry->replied_at);

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->missing('guestbookEntries'));

        $this->withSession($session)
            ->put("/admin/guestbook/{$entry->id}", [
                ...$edited,
                'status' => 'hidden',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Review updated.');

        $entry->refresh();
        $this->assertSame('hidden', $entry->status);
        $this->assertNull($entry->approved_at);
        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->missing('guestbookEntries'));

        $this->withSession($session)
            ->delete("/admin/guestbook/{$entry->id}")
            ->assertRedirect()
            ->assertSessionHas('success', 'Guestbook entry removed.');

        $this->assertDatabaseMissing('guestbook_entries', ['id' => $entry->id]);
    }

    public function test_admin_cannot_mutate_versioned_profile_or_achievements(): void
    {
        $session = ['portfolio_admin_authenticated' => true];
        $this->withSession($session)->put('/admin/profile', [])->assertStatus(409);
        $this->withSession($session)->post('/admin/achievements', [])->assertStatus(409);
    }

    public function test_admin_cannot_mutate_versioned_projects_publications_or_certificates(): void
    {
        $session = ['portfolio_admin_authenticated' => true];
        foreach (['projects', 'published-works', 'certificates'] as $path) {
            $this->withSession($session)->post('/admin/'.$path, [])->assertStatus(409);
        }
    }

    public function test_admin_cannot_mutate_versioned_design_media(): void
    {
        $this->withSession(['portfolio_admin_authenticated' => true])->post('/admin/design-media', [])->assertStatus(409);
    }

    public function test_admin_cannot_mutate_versioned_work_and_education(): void
    {
        $session = ['portfolio_admin_authenticated' => true];
        foreach (['work-experiences', 'education'] as $path) {
            $this->withSession($session)->post('/admin/'.$path, [])->assertStatus(409);
        }
    }

    public function test_admin_cannot_mutate_versioned_badges(): void
    {
        $this->withSession(['portfolio_admin_authenticated' => true])->post('/admin/credential-badges', [])->assertStatus(409);
    }

    public function test_admin_cannot_mutate_versioned_portfolio_tools(): void
    {
        $this->withSession(['portfolio_admin_authenticated' => true])->post('/admin/portfolio-tools', [])->assertStatus(409);
    }
}
