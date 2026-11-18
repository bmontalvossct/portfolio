<?php

namespace Tests\Feature;

use App\Mail\GuestbookEntrySubmitted;
use App\Models\Certificate;
use App\Models\CredentialBadge;
use App\Models\GuestbookEntry;
use App\Support\Profile\CredlyBadgeSyncer;
use App\Support\Profile\GitHubContributionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProfilePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_renders_versioned_sections(): void
    {
        $this->withoutVite();
        $this->seed();

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Profile/Show')
                ->where('profile.display_name', 'Britt Kristoff B. Montalvo, MSIT')
                ->where('profile.headline', 'Registered Cybersecurity Professional (DICT Intermediate Level 2) | Systems Analyst, Researcher, & Developer')
                ->where('profile.github_username', 'bmontalvossct')
                ->where('profile.email', 'inquiries@brittmontalvo.dev')
                ->where('profile.bio', 'Specializing in full-stack web development, cybersecurity, network infrastructure, AI-powered solutions, data analytics, and workflow automation. Helping businesses, educational institutions, and organizations build secure, scalable, and user-friendly digital solutions that streamline operations, improve efficiency, and support digital transformation.')
                ->where('profile.external_links.2.label', 'LinkedIn')
                ->where('profile.external_links.2.url', 'https://www.linkedin.com/in/britt-kristoff-montalvo/')
                ->has('badges', 38)
                ->where('badges.0.name', 'Networking Basics')
                ->where('badges.0.issuer', 'Cisco')
                ->has('publishedWorks', 3)
                ->has('projects', 6)
                ->has('portfolioTools', 37)
                ->where('portfolioTools.0.category', 'backend')
                ->where('portfolioTools.0.name', 'PHP')
                ->where('portfolioTools.4.category', 'frontend')
                ->where('portfolioTools.8.category', 'automation')
                ->where('portfolioTools.13.name', 'NotebookLM')
                ->where('portfolioTools.14.name', 'Hugging Face')
                ->where('portfolioTools.15.name', 'GPTZero')
                ->where('portfolioTools.16.name', 'Microsoft Power BI')
                ->where('portfolioTools.20.category', 'design')
                ->where('portfolioTools.24.name', 'cPanel')
                ->where('portfolioTools.29.name', 'Notion')
                ->where('portfolioTools.30.name', 'Google Workspace')
                ->where('portfolioTools.33.name', 'GitLab')
                ->where('portfolioTools.34.name', 'Vercel')
                ->missing('designMedia')
                ->has('achievements', 6)
                ->where('achievements.0.title', 'DICT Registered Cybersecurity Professional - Intermediate Level 2')
                ->where('achievements.0.image_url', '/repository-media/portfolio/organizations/dict.svg')
                ->where('achievements.0.external_url', 'https://dict.gov.ph')
                ->where('achievements.1.title', 'Civil Service Eligibility - Professional')
                ->where('achievements.2.title', 'SAP Certified - Implementation Consultant')
                ->where('achievements.2.image_url', '/images/brands/sap.svg')
                ->where('achievements.3.title', 'MikroTik Consultant')
                ->where('achievements.3.image_url', 'https://cdn.simpleicons.org/mikrotik?viewbox=auto')
                ->where('achievements.3.external_url', 'https://mikrotik.com/consultants?category=consultants&f[0]=cert%3AMTCNA&f[1]=cert%3AMTCRE&f[2]=cert%3AMTCUME&region=Philippines')
                ->where('achievements.4.title', 'GPTZero Ambassador')
                ->where('achievements.4.image_url', 'https://gptzero.me/favicon.ico')
                ->where('achievements.4.external_url', null)
                ->where('achievements.4.certificate_preview_url', null)
                ->where('achievements.5.title', 'Scam Ready ASEAN Master Trainer')
                ->where('achievements.5.issuer', 'SmartCT / ASEAN Foundation')
                ->where('achievements.5.achieved_on', null)
                ->where('achievements.5.image_url', '/images/recognitions/scam-ready-asean-logo.png')
                ->where('achievements.5.certificate_preview_url', '/images/recognitions/scam-ready-asean-certificate.jpg')
                ->has('certificates', 51)
                ->where('certificates.0.title', 'Lean Six Sigma: Yellow Belt')
                ->where('certificates.0.issuer', 'Alison')
                ->where('certificates.0.category', 'project_management')
                ->where('certificates.0.issued_on', 'Jul 2026')
                ->where('certificates.0.file_url', 'https://alison.com/verify/31557408f8')
                ->where('certificates.4.title', 'Agile Project Management')
                ->where('certificates.4.issuer', 'Google')
                ->where('certificates.4.provider_name', 'Google')
                ->where('certificates.4.thumbnail_url', 'https://api.iconify.design/logos/google-icon.svg')
                ->has('education', 2)
                ->where('education.0.institution', 'Caraga State University')
                ->where('education.0.program', 'Master of Science in Information Technology')
                ->where('education.0.end_date', 'June 2026')
                ->where('education.0.logo_url', '/repository-media/portfolio/organizations/caraga-state-university.png')
                ->has('workExperiences', 5)
                ->where('workExperiences.0.position', 'Freelancer')
                ->where('workExperiences.0.is_current', true)
                ->has('workExperiences.0.responsibilities', 18)
                ->where('workExperiences.1.logo_url', '/repository-media/portfolio/organizations/snsu.png')
                ->where('workExperiences.1.end_date', 'June 18, 2026')
                ->where('workExperiences.1.is_current', false)
                ->has('workExperiences.1.responsibilities', 7)
                ->where('workExperiences.1.responsibilities.6', 'Research and Market Analysis: Conduct research and market analysis to stay updated with industry trends and advancements in information systems.')
                ->where('workExperiences.2.logo_url', '/repository-media/portfolio/organizations/department-of-health.png')
                ->where('workExperiences.2.position', 'Computer Programmer II')
                ->where('workExperiences.2.end_date', 'November 25, 2024')
                ->where('workExperiences.2.summary', 'Implemented and supported electronic medical record systems across health facilities, including readiness assessment, training, monitoring, and technical reporting.')
                ->has('workExperiences.2.responsibilities', 18)
                ->where('workExperiences.2.responsibilities.0', 'Oversee the overall implementation of EMR Records.')
                ->where('workExperiences.2.responsibilities.17', 'Email advisories/administrative orders to health facilities.')
                ->where('workExperiences.2.is_current', false)
                ->where('workExperiences.3.logo_url', '/repository-media/portfolio/organizations/department-of-health.png')
                ->has('workExperiences.3.responsibilities', 18)
                ->where('workExperiences.3.responsibilities.17', 'Email advisories/administrative orders to health facilities.')
                ->has('workExperiences.4.responsibilities', 7)
                ->where('workExperiences.4.responsibilities.6', 'Provide technical assistance for company ICT equipment under supervision, learning troubleshooting techniques and maintenance procedures to ensure equipment functionality.')
                ->missing('guestbookEntries')
                ->has('publishedWorks', 3)
                ->where('publishedWorks.0.title', 'Forecasting Child Immunization Coverage and Predicting Underperformance Risk Using a Hybrid Time-Series Framework: Evidence from FHSIS Data')
                ->where('publishedWorks.0.publication', 'Accepted by IDAP (International Data & Analytics Proceedings)')
                ->where('publishedWorks.1.title', 'A Hybrid Time-Series Forecasting and Underperformance Risk Prediction System for Child Immunization Coverage in Surigao del Norte, Philippines: Evidence from FHSIS Quarterly Data')
                ->where('publishedWorks.2.title', 'SURe-Health: PDOHO-SDN Official Publication')
                ->where('publishedWorks.2.cover_url', '/repository-media/portfolio/publications/sure-health-cover.webp')
                ->where('publishedWorks.2.cover_preview_url', null)
                ->where('publishedWorks.2.external_url', 'https://online.fliphtml5.com/utpit/pzyy/')
                ->where('projects.0.title', 'Governed MCH Forecasting and Risk Prediction DSS')
                ->where('projects.0.category', 'Health informatics DSS')
                ->where('projects.0.repo_url', 'https://github.com/bmontalvossct/Forecasting-and-Risk-Prediction')
                ->where('projects.0.thumbnail_url', '/repository-media/portfolio/projects/mch-forecasting-risk-dss.png')
                ->where('projects.1.title', 'ClassCheck')
                ->where('projects.1.url', 'https://classcheck.brittmontalvo.dev')
                ->where('projects.2.title', 'SDN Electronic Medical Records Dashboard')
                ->where('projects.2.url', 'https://app.powerbi.com/view?r=eyJrIjoiMDBmNTFkOTYtMGQ1ZC00MTUzLWIxMmEtMzgwMTUwMmE5ODUzIiwidCI6IjE5NWQzN2JlLTllMGEtNDIwNS1hZGY0LWEyNTk5ZTllMWNjYSIsImMiOjEwfQ%3D%3D&pageName=ReportSection')
                ->where('projects.3.title', 'Surigao del Norte State University Public Website')
                ->where('projects.3.url', 'https://gamma.snsu.edu.ph')
                ->where('projects.3.thumbnail_url', '/repository-media/portfolio/projects/snsu-gamma-home.png')
                ->where('projects.4.repo_url', 'https://github.com/kristoffmontalvo218/Amplifier-repo')
            );

        $this->assertFileExists(public_path('images/brands/sap.svg'));
    }

    public function test_services_page_presents_the_full_quote_only_catalog_separate_from_homepage(): void
    {
        $this->withoutVite();
        $this->seed();

        $this->get('/services')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Profile/Services')
                ->where('profile.display_name', 'Britt Kristoff B. Montalvo, MSIT')
                ->where('profile.email', 'inquiries@brittmontalvo.dev')
                ->has('services', 9)
                ->where('services.0.title', 'Digital Transformation & IT Consulting')
                ->where('services.1.title', 'Custom Web & Information Systems')
                ->where('services.2.title', 'Data Analytics, Dashboards & Decision Support')
                ->where('services.5.title', 'Cybersecurity Assessment & Network Consulting')
                ->where('services.8.title', 'Speaking, Workshops & Technical Training')
                ->missing('services.0.rate')
                ->missing('services.0.rate_note')
                ->missing('services.8.rate')
                ->missing('services.8.rate_note')
            );

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Profile/Show')
                ->missing('services')
            );
    }

    public function test_profile_page_does_not_expose_the_unused_media_archive(): void
    {
        $this->withoutVite();
        Storage::fake('public');
        config()->set('portfolio.discover_stored_media_in_tests', true);
        $this->seed();

        Storage::disk('public')->put('portfolio/Images/Campaign Poster.jpg', 'image');
        Storage::disk('public')->put('portfolio/Images/Brochure Design.pdf', 'pdf');
        Storage::disk('public')->put('portfolio/Videos/Launch Reel.mp4', 'video');
        Storage::disk('public')->put('portfolio/Videos/Profile Video.mp4', 'private profile motion asset');
        Storage::disk('public')->put('portfolio/design-thumbnails/Launch Reel.jpg', 'thumbnail');
        Storage::disk('public')->put('portfolio/Images/notes.txt', 'not media');

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->missing('designMedia')
            );

        $this->get('/designs')
            ->assertRedirect('/#designs');
    }

    public function test_complete_portfolio_seeders_are_idempotent_and_exclude_private_certificate_files(): void
    {
        $this->seed();
        $this->seed();

        $this->assertDatabaseCount('profiles', 1);
        $this->assertDatabaseCount('achievements', 6);
        $this->assertDatabaseCount('certificates', 51);
        $this->assertDatabaseCount('credential_badges', 38);
        $this->assertDatabaseCount('education', 2);
        $this->assertDatabaseMissing('education', ['institution' => 'San Nicolas Academy', 'program' => 'High School']);
        $this->assertDatabaseCount('work_experiences', 5);
        $this->assertDatabaseMissing('work_experiences', ['organization' => 'TMB Tax Bureau']);
        $this->assertDatabaseCount('published_works', 3);
        $this->assertDatabaseCount('projects', 6);
        $this->assertDatabaseCount('portfolio_tools', 37);
        $this->assertDatabaseHas('certificates', [
            'title' => 'CCNA Routing and Switching: Routing and Switching Essentials',
            'issuer' => 'Cisco',
        ]);
        $this->assertDatabaseHas('certificates', [
            'title' => 'AWS AI Practitioner Challenge',
            'issuer' => 'Udacity',
        ]);
        $this->assertDatabaseHas('certificates', [
            'title' => 'Agile Project Management',
            'issuer' => 'Google',
        ]);
        $this->assertDatabaseHas('certificates', [
            'title' => 'Data Privacy Competency Framework',
            'issuer' => 'Department of Information and Communications Technology',
        ]);
        $this->assertDatabaseMissing('certificates', ['title' => 'Cyber Threat Management']);
        $this->assertDatabaseHas('certificates', [
            'title' => 'Omada Certified Network Administrator (OCNA) - Wireless',
            'verification_code' => '57E94B6682EC4E95',
        ]);
        $this->assertSame(0, Certificate::query()->where('file_url', 'like', '%drive.google.com%')->count());
        $this->assertSame(0, Certificate::query()->whereNotNull('local_path')->count());
        $this->assertDatabaseMissing('credential_badges', ['external_id' => 'credly-profile-brittm']);
    }

    public function test_guestbook_entries_require_approval_and_never_expose_email_addresses(): void
    {
        $this->withoutVite();
        Mail::fake();
        $this->seed();

        $this->post('/guestbook', [
            'name' => 'Portfolio Reviewer',
            'email' => 'reviewer@example.test',
            'role_or_organization' => 'Research partner',
            'body' => 'The portfolio presents the research and systems work clearly.',
            'rating' => 5,
            'website' => '',
        ])->assertRedirect()
            ->assertSessionHas('success', 'Thank you for your note. It will appear after review.');

        $entry = GuestbookEntry::query()->firstOrFail();

        Mail::assertSent(GuestbookEntrySubmitted::class, function (GuestbookEntrySubmitted $mail) use ($entry): bool {
            return $mail->hasTo('inquiries@brittmontalvo.dev') && $mail->entry->is($entry);
        });
        $this->assertSame('pending', $entry->status);
        $this->assertNull($entry->approved_at);
        $this->assertSame('accepted', $entry->notification_status);
        $this->assertNotNull($entry->notification_attempted_at);
        $this->assertNotNull($entry->notification_accepted_at);
        $this->assertNull($entry->notification_error);

        $this->get('/')->assertOk()
            ->assertInertia(fn (Assert $page) => $page->missing('guestbookEntries'));

        $this->withSession(['portfolio_admin_authenticated' => true])
            ->put("/admin/guestbook/{$entry->id}", [
                'name' => $entry->name,
                'email' => $entry->email,
                'role_or_organization' => $entry->role_or_organization,
                'body' => $entry->body,
                'rating' => $entry->rating,
                'status' => 'approved',
            ])->assertRedirect();

        $this->assertNotNull($entry->fresh()->approved_at);
        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->missing('guestbookEntries')
            );
    }

    public function test_guestbook_review_is_preserved_when_the_mail_transport_fails(): void
    {
        $this->withoutVite();
        $this->seed();
        Mail::shouldReceive('to')
            ->once()
            ->andThrow(new \RuntimeException('Test transport unavailable.'));

        $this->post('/guestbook', [
            'name' => 'Offline Reviewer',
            'email' => 'offline@example.test',
            'body' => 'This review remains available for moderation if email delivery fails.',
            'rating' => 4,
            'website' => '',
        ])->assertRedirect()->assertSessionHas('success');

        $entry = GuestbookEntry::query()->where('email', 'offline@example.test')->firstOrFail();

        $this->assertSame('pending', $entry->status);
        $this->assertNull($entry->approved_at);
        $this->assertSame('failed', $entry->notification_status);
        $this->assertNotNull($entry->notification_attempted_at);
        $this->assertNull($entry->notification_accepted_at);
        $this->assertSame('Test transport unavailable.', $entry->notification_error);

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->missing('guestbookEntries'));
    }

    public function test_guestbook_honeypot_does_not_store_or_send_a_note(): void
    {
        Mail::fake();

        $this->post('/guestbook', ['website' => 'https://spam.example'])
            ->assertRedirect()
            ->assertSessionHas('success', 'Thank you for your note. It will appear after review.');

        $this->assertDatabaseCount('guestbook_entries', 0);
        Mail::assertNothingSent();
    }

    public function test_dedicated_badge_and_certificate_archives_are_categorized(): void
    {
        $this->withoutVite();
        $this->seed();

        Certificate::query()->create([
            'source' => 'admin',
            'external_id' => 'archive-certificate',
            'title' => 'Applied Cybersecurity Operations',
            'issuer' => 'Example Security Institute',
            'description' => 'Professional cybersecurity training.',
            'file_url' => 'https://example.test/certificates/cybersecurity',
            'tags' => ['Cybersecurity', 'Network defense'],
            'sort_order' => 1,
            'is_featured' => true,
        ]);

        $this->get('/badges')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Profile/CredentialArchive')
                ->where('archiveType', 'badges')
                ->where('alternate.url', '/certifications')
                ->has('categories', 8)
                ->has('items', 38)
                ->where('items.0.title', 'Google AI Professional Certificate')
                ->where('items.0.category', 'ai_data')
            );

        $this->get('/certifications')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Profile/CredentialArchive')
                ->where('profile.avatar_url', '/images/profile-avatar.jpg')
                ->where('archiveType', 'certificates')
                ->where('alternate.url', '/badges')
                ->has('items', 51)
                ->where('items', fn ($items) => collect($items)->doesntContain('title', 'Applied Cybersecurity Operations'))
                ->where('items.0.title', 'Lean Six Sigma: Yellow Belt')
                ->where('items.0.category', 'project_management')
                ->where('items.0.date', 'Jul 2026')
                ->where('items.0.image_url', 'https://cdn01.alison-static.net/public/html/vendor/img/favicon/apple-touch-icon.png')
                ->where('items.0.verify_url', 'https://alison.com/verify/31557408f8')
                ->where('items.1.title', 'Sangfor Network Security and Endpoint Secure Technical Training')
                ->where('items.1.category', 'cybersecurity')
                ->where('items.1.date', 'Mar 2026')
                ->where('items.1.image_url', '/repository-media/portfolio/organizations/sangfor.png')
                ->where('items.1.verify_url', null)
            );
    }

    public function test_credly_sync_maps_public_payload_into_badges(): void
    {
        Http::fake([
            '*credly.com/*' => Http::response([
                'data' => [
                    [
                        'id' => 'badge-1',
                        'issued_at' => '2026-01-10',
                        'public_url' => 'https://www.credly.com/badges/badge-1',
                        'badge_template' => [
                            'name' => 'Research Data Foundations',
                            'description' => 'Validated data and research workflow skills.',
                            'image_url' => 'https://example.test/badge.png',
                            'issuer' => ['name' => 'Example Issuer'],
                            'skills' => [
                                ['name' => 'Research'],
                                ['name' => 'Data'],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $result = app(CredlyBadgeSyncer::class)->sync('brittm', null, false);

        $this->assertSame(1, $result['synced']);
        $this->assertDatabaseHas('credential_badges', [
            'provider' => 'credly',
            'external_id' => 'badge-1',
            'name' => 'Research Data Foundations',
            'issuer' => 'Example Issuer',
        ]);
        $this->assertEquals(
            ['Research', 'Data'],
            CredentialBadge::query()->where('external_id', 'badge-1')->firstOrFail()->skills
        );
    }

    public function test_github_contribution_service_maps_public_calendar(): void
    {
        Http::fake([
            'github.com/users/*/contributions' => Http::response(<<<'HTML'
                <h2 id="js-contribution-activity-description">12 contributions in the last year</h2>
                <table>
                    <td class="ContributionCalendar-day" data-date="2026-07-12" data-level="0"></td>
                    <td class="ContributionCalendar-day" data-date="2026-07-13" data-level="3"></td>
                </table>
                HTML),
        ]);

        $activity = app(GitHubContributionService::class)->forUser('bmontalvossct');

        $this->assertSame(12, $activity['total']);
        $this->assertSame('2026-07-12', $activity['from']);
        $this->assertSame(3, $activity['weeks'][0]['days'][1]['level']);
    }
}
