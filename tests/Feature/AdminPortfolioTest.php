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
                ->where('profile.avatar_url', 'https://avatars.githubusercontent.com/u/52160082?v=4'));

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
                ->has('projects', 5)
                ->where('projects.0.title', 'Governed MCH Forecasting and Risk Prediction DSS')
                ->has('publishedWorks', 2)
                ->where('publishedWorks.0.publication', 'Approved for Scopus publication')
                ->where('publishedWorks.1.title', 'SURe-Health: PDOHO-SDN Official Publication')
                ->has('portfolioTools', 37)
                ->has('achievements', 4)
                ->has('credentialBadges', 29)
                ->has('education', 3)
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
            ->assertInertia(fn (Assert $page) => $page
                ->has('guestbookEntries', 1)
                ->where('guestbookEntries.0.name', 'Edited Reviewer')
                ->where('guestbookEntries.0.body', $edited['body'])
                ->where('guestbookEntries.0.admin_reply', $edited['admin_reply'])
                ->missing('guestbookEntries.0.email')
            );

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
            ->assertInertia(fn (Assert $page) => $page->has('guestbookEntries', 0));

        $this->withSession($session)
            ->delete("/admin/guestbook/{$entry->id}")
            ->assertRedirect()
            ->assertSessionHas('success', 'Guestbook entry removed.');

        $this->assertDatabaseMissing('guestbook_entries', ['id' => $entry->id]);
    }

    public function test_admin_can_update_profile_picture_and_add_achievement(): void
    {
        Storage::fake('public');
        $session = ['portfolio_admin_authenticated' => true];
        $profile = Profile::query()->firstOrFail();

        $this->withSession($session)->post('/admin/profile', [
            '_method' => 'put',
            'display_name' => $profile->display_name,
            'headline' => 'Updated portfolio headline',
            'availability' => $profile->availability,
            'location' => $profile->location,
            'email' => $profile->email,
            'bio' => $profile->bio,
            'credly_username' => $profile->credly_username,
            'github_username' => $profile->github_username,
            'canva_url' => 'https://www.canva.com/example-showcase',
            'external_links_text' => 'GitHub|https://github.com/bmontalvossct',
            'highlights_text' => "Systems analyst\nResearcher",
            'skills_text' => "Laravel\nGraphic design",
            'avatar' => UploadedFile::fake()->image('portrait.jpg', 800, 1000),
        ])->assertRedirect();

        $profile->refresh();
        $this->assertSame('Updated portfolio headline', $profile->headline);
        $this->assertSame('https://www.canva.com/example-showcase', $profile->canva_url);
        $this->assertStringStartsWith('/storage/portfolio/profile/', $profile->avatar_url);

        $this->withSession($session)->post('/admin/achievements', [
            'title' => 'Regional technology award',
            'issuer' => 'Example Institution',
            'summary' => 'Recognized for public-sector information systems work.',
            'tags_text' => 'Technology, Public service',
            'is_featured' => true,
        ])->assertRedirect();

        $this->assertDatabaseHas('achievements', [
            'title' => 'Regional technology award',
            'issuer' => 'Example Institution',
        ]);
        $this->assertTrue(Achievement::query()->where('title', 'Regional technology award')->firstOrFail()->is_featured);
    }

    public function test_admin_can_manage_showcase_publication_and_certificate_records(): void
    {
        $session = ['portfolio_admin_authenticated' => true];

        $this->withSession($session)->post('/admin/projects', [
            'title' => 'University services portal',
            'category' => 'Website',
            'summary' => 'A public-facing university service project.',
            'year' => '2026',
            'url' => 'https://example.test/portal',
            'tags_text' => 'Laravel, Vue',
            'is_featured' => true,
        ])->assertRedirect();

        $project = Project::query()->where('title', 'University services portal')->firstOrFail();

        $this->withSession($session)->post("/admin/projects/{$project->id}", [
            '_method' => 'put',
            'title' => 'University digital services portal',
            'category' => 'Website',
            'summary' => $project->summary,
            'year' => '2026',
            'tags_text' => 'Laravel, Vue, Inertia',
            'is_featured' => true,
        ])->assertRedirect();

        $this->withSession($session)->post('/admin/published-works', [
            'type' => 'article',
            'title' => 'Designing dependable public systems',
            'publication' => 'Technology Review',
            'summary' => 'An article about practical information systems work.',
            'external_url' => 'https://example.test/article',
            'tags_text' => 'Systems, Public service',
            'is_featured' => true,
        ])->assertRedirect();

        $this->withSession($session)->post('/admin/certificates', [
            'title' => 'Advanced Systems Practice',
            'issuer' => 'Example Institution',
            'category' => 'software',
            'file_url' => 'https://example.test/certificate.pdf',
            'tags_text' => 'Systems',
        ])->assertRedirect();

        $this->assertDatabaseHas('projects', ['title' => 'University digital services portal']);
        $this->assertDatabaseHas('published_works', ['title' => 'Designing dependable public systems']);
        $this->assertDatabaseHas('certificates', ['title' => 'Advanced Systems Practice', 'source' => 'admin']);
    }

    public function test_admin_can_upload_and_publish_design_images_and_videos(): void
    {
        Storage::fake('public');
        $session = ['portfolio_admin_authenticated' => true];

        $this->withSession($session)->post('/admin/design-media', [
            'title' => 'Public information campaign',
            'media_type' => 'infographic',
            'description' => 'An infographic prepared for a public information campaign.',
            'year' => '2026',
            'external_url' => 'https://www.canva.com/design/example',
            'is_featured' => true,
            'media' => UploadedFile::fake()->image('campaign.png', 1200, 1600),
        ])->assertRedirect();

        $infographic = DesignMedia::query()->where('title', 'Public information campaign')->firstOrFail();
        $this->assertStringStartsWith('/storage/portfolio/designs/', $infographic->media_url);
        Storage::disk('public')->assertExists(str($infographic->media_url)->after('/storage/')->toString());

        $this->withSession($session)->post('/admin/design-media', [
            'title' => 'Digital campaign reel',
            'media_type' => 'video',
            'description' => 'A short motion design reel.',
            'year' => '2026',
            'media' => UploadedFile::fake()->create('campaign.mp4', 1024, 'video/mp4'),
            'thumbnail' => UploadedFile::fake()->image('campaign-poster.jpg', 1600, 900),
        ])->assertRedirect();

        $video = DesignMedia::query()->where('title', 'Digital campaign reel')->firstOrFail();
        $this->assertStringStartsWith('/storage/portfolio/designs/', $video->media_url);
        $this->assertStringStartsWith('/storage/portfolio/design-thumbnails/', $video->thumbnail_url);

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('designMedia', 2)
                ->where('designMedia.0.title', 'Public information campaign')
                ->where('designMedia.0.media_type', 'infographic')
                ->where('designMedia.1.media_type', 'video')
            );

        $this->withSession($session)
            ->delete("/admin/design-media/{$infographic->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('design_media', ['id' => $infographic->id]);
        Storage::disk('public')->assertMissing(str($infographic->media_url)->after('/storage/')->toString());
    }

    public function test_admin_can_create_edit_and_delete_work_and_education_entries(): void
    {
        Storage::fake('public');
        $session = ['portfolio_admin_authenticated' => true];

        $this->withSession($session)->post('/admin/work-experiences', [
            'organization' => 'Digital Transformation Office',
            'position' => 'Digitalization Specialist',
            'start_date' => 'January 2025',
            'location' => 'Surigao City',
            'summary' => 'Led service digitization initiatives.',
            'responsibilities_text' => "Mapped service workflows\nDelivered internal systems",
            'logo' => UploadedFile::fake()->image('dto.png', 400, 400),
            'sort_order' => 3,
            'is_current' => true,
        ])->assertRedirect();

        $work = WorkExperience::query()->where('organization', 'Digital Transformation Office')->firstOrFail();
        $workLogoPath = str($work->logo_url)->after('/storage/')->toString();
        $this->assertSame(['Mapped service workflows', 'Delivered internal systems'], $work->responsibilities);
        $this->assertNull($work->end_date);
        Storage::disk('public')->assertExists($workLogoPath);

        $this->withSession($session)->post("/admin/work-experiences/{$work->id}", [
            '_method' => 'put',
            'organization' => $work->organization,
            'position' => 'Lead Digitalization Specialist',
            'start_date' => $work->start_date,
            'end_date' => 'June 2026',
            'location' => $work->location,
            'summary' => $work->summary,
            'responsibilities_text' => 'Directed the digital transformation roadmap',
            'sort_order' => 2,
        ])->assertRedirect();

        $work->refresh();
        $this->assertSame('Lead Digitalization Specialist', $work->position);
        $this->assertSame('June 2026', $work->end_date);
        $this->assertSame('/storage/'.$workLogoPath, $work->logo_url);

        $this->withSession($session)->post('/admin/education', [
            'institution' => 'Example State University',
            'program' => 'Doctor of Information Technology',
            'level' => 'Doctoral degree',
            'start_date' => '2026',
            'location' => 'Butuan City',
            'description' => 'Advanced study in digital public services.',
            'activities_text' => "Research colloquium\nGraduate council",
            'logo' => UploadedFile::fake()->image('esu.png', 400, 400),
            'sort_order' => 0,
        ])->assertRedirect();

        $education = Education::query()->where('institution', 'Example State University')->firstOrFail();
        $educationLogoPath = str($education->logo_url)->after('/storage/')->toString();
        $this->assertSame(['Research colloquium', 'Graduate council'], $education->activities);
        Storage::disk('public')->assertExists($educationLogoPath);

        $this->withSession($session)->post("/admin/education/{$education->id}", [
            '_method' => 'put',
            'institution' => $education->institution,
            'program' => $education->program,
            'level' => $education->level,
            'start_date' => $education->start_date,
            'end_date' => '2029',
            'location' => $education->location,
            'description' => 'Updated doctoral study description.',
            'activities_text' => 'Research colloquium',
            'sort_order' => 0,
        ])->assertRedirect();

        $this->assertDatabaseHas('education', [
            'id' => $education->id,
            'end_date' => '2029',
            'description' => 'Updated doctoral study description.',
        ]);

        $this->withSession($session)->delete("/admin/work-experiences/{$work->id}")->assertRedirect();
        $this->withSession($session)->delete("/admin/education/{$education->id}")->assertRedirect();

        $this->assertDatabaseMissing('work_experiences', ['id' => $work->id]);
        $this->assertDatabaseMissing('education', ['id' => $education->id]);
        Storage::disk('public')->assertMissing($workLogoPath);
        Storage::disk('public')->assertMissing($educationLogoPath);
    }

    public function test_admin_can_create_edit_and_delete_credential_badges(): void
    {
        Storage::fake('public');
        $session = ['portfolio_admin_authenticated' => true];

        $this->withSession($session)->post('/admin/credential-badges', [
            'name' => 'Digital Government Professional',
            'issuer' => 'Example Credential Institute',
            'category' => 'professional',
            'description' => 'Verified practice in public-sector digital transformation.',
            'issued_at' => '2026-06-15',
            'certificate_url' => 'https://example.test/credentials/digital-government',
            'skills_text' => 'Digital transformation, Service design',
            'sort_order' => 1,
            'is_featured' => true,
            'image' => UploadedFile::fake()->image('badge.png', 600, 600),
        ])->assertRedirect();

        $badge = CredentialBadge::query()->where('name', 'Digital Government Professional')->firstOrFail();
        $badgeImagePath = str($badge->image_url)->after('/storage/')->toString();
        $this->assertSame('admin', $badge->provider);
        $this->assertSame(['Digital transformation', 'Service design'], $badge->skills);
        Storage::disk('public')->assertExists($badgeImagePath);

        $this->withSession($session)->post("/admin/credential-badges/{$badge->id}", [
            '_method' => 'put',
            'name' => 'Lead Digital Government Professional',
            'issuer' => $badge->issuer,
            'category' => 'project_management',
            'description' => $badge->description,
            'issued_at' => '2026-06-15',
            'expires_at' => '2029-06-15',
            'certificate_url' => $badge->certificate_url,
            'skills_text' => 'Digital transformation, Service design, Leadership',
            'sort_order' => 0,
            'is_featured' => true,
        ])->assertRedirect();

        $badge->refresh();
        $this->assertSame('Lead Digital Government Professional', $badge->name);
        $this->assertSame('project_management', $badge->category);
        $this->assertSame('/storage/'.$badgeImagePath, $badge->image_url);
        $this->assertSame(['Digital transformation', 'Service design', 'Leadership'], $badge->skills);

        $this->withSession($session)->delete("/admin/credential-badges/{$badge->id}")->assertRedirect();

        $this->assertDatabaseMissing('credential_badges', ['id' => $badge->id]);
        Storage::disk('public')->assertMissing($badgeImagePath);
    }

    public function test_admin_can_create_edit_and_delete_portfolio_tools(): void
    {
        Storage::fake('public');
        $session = ['portfolio_admin_authenticated' => true];

        $this->withSession($session)->post('/admin/portfolio-tools', [
            'name' => 'API testing suite',
            'category' => 'development',
            'description' => 'Used to validate and document application endpoints.',
            'external_url' => 'https://example.test/api-tool',
            'sort_order' => 3,
            'is_featured' => true,
            'icon' => UploadedFile::fake()->image('api-tool.png', 400, 400),
        ])->assertRedirect();

        $tool = PortfolioTool::query()->where('name', 'API testing suite')->firstOrFail();
        $iconPath = str($tool->icon_url)->after('/storage/')->toString();
        $this->assertSame('development', $tool->category);
        $this->assertTrue($tool->is_featured);
        Storage::disk('public')->assertExists($iconPath);

        $this->withSession($session)->post("/admin/portfolio-tools/{$tool->id}", [
            '_method' => 'put',
            'name' => 'API testing and documentation',
            'category' => 'automation',
            'description' => 'Used to validate, document, and demonstrate application endpoints.',
            'external_url' => $tool->external_url,
            'sort_order' => 4,
            'is_featured' => true,
        ])->assertRedirect();

        $tool->refresh();
        $this->assertSame('API testing and documentation', $tool->name);
        $this->assertSame('automation', $tool->category);
        $this->assertSame('/storage/'.$iconPath, $tool->icon_url);

        $this->withSession($session)->delete("/admin/portfolio-tools/{$tool->id}")->assertRedirect();

        $this->assertDatabaseMissing('portfolio_tools', ['id' => $tool->id]);
        Storage::disk('public')->assertMissing($iconPath);
    }
}
