<?php

namespace Tests\Feature;

use App\Models\Certificate;
use App\Models\CredentialBadge;
use App\Models\GuestbookEntry;
use App\Support\Profile\CredlyBadgeSyncer;
use App\Support\Profile\GitHubContributionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProfilePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_renders_database_backed_sections(): void
    {
        $this->withoutVite();
        $this->seed();

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Profile/Show')
                ->where('profile.display_name', 'Britt Kristoff B. Montalvo, MSIT')
                ->where('profile.headline', 'Lead Digitalization Expert, information systems analyst, researcher, and multidisciplinary digital maker.')
                ->where('profile.github_username', 'bmontalvossct')
                ->where('profile.email', 'inquiries@brittmontalvo.dev')
                ->where('profile.bio', 'I work across information systems, health technology implementation, data operations, research, and visual publishing. I also provide MikroTik network consulting and serve as a GPTZero Ambassador, promoting responsible and transparent AI use.')
                ->where('profile.external_links.2.label', 'LinkedIn')
                ->where('profile.external_links.2.url', 'https://www.linkedin.com/in/britt-kristoff-montalvo/')
                ->has('badges', 29)
                ->where('badges.0.name', 'Networking Basics')
                ->where('badges.0.issuer', 'Cisco')
                ->has('publishedWorks', 2)
                ->has('projects', 5)
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
                ->has('designMedia', 0)
                ->has('achievements', 4)
                ->where('achievements.0.achieved_on', 'Jun 2022')
                ->where('achievements.0.image_url', '/storage/portfolio/organizations/civil-service-commission.png')
                ->where('achievements.0.external_url', 'https://www.scribd.com/document/589242525/Caraga-06192022-PRO')
                ->where('achievements.1.title', 'SAP Certified - Implementation Consultant')
                ->where('achievements.1.image_url', '/images/brands/sap.svg')
                ->where('achievements.2.title', 'MikroTik Consultant')
                ->where('achievements.2.image_url', 'https://cdn.simpleicons.org/mikrotik?viewbox=auto')
                ->where('achievements.2.external_url', 'https://mikrotik.com/consultants?category=consultants&f[0]=cert%3AMTCNA&f[1]=cert%3AMTCRE&f[2]=cert%3AMTCUME&region=Philippines')
                ->where('achievements.3.title', 'GPTZero Ambassador')
                ->where('achievements.3.image_url', 'https://gptzero.me/favicon.ico')
                ->where('achievements.3.external_url', null)
                ->has('certificates', 51)
                ->where('certificates.0.title', 'Lean Six Sigma: Yellow Belt')
                ->where('certificates.0.issuer', 'Alison')
                ->where('certificates.0.category', 'project_management')
                ->where('certificates.0.issued_on', 'Jul 2026')
                ->where('certificates.0.file_url', 'https://alison.com/verify/31557408f8')
                ->where('certificates.0.thumbnail_url', 'https://cdn01.alison-static.net/public/html/vendor/img/favicon/apple-touch-icon.png')
                ->where('certificates.1.title', 'Sangfor Network Security and Endpoint Secure Technical Training')
                ->where('certificates.1.issuer', 'ITDEPOT / Sangfor')
                ->where('certificates.1.category', 'cybersecurity')
                ->where('certificates.1.issued_on', 'Mar 2026')
                ->where('certificates.1.file_url', null)
                ->where('certificates.1.thumbnail_url', '/storage/portfolio/organizations/sangfor.png')
                ->where('certificates.4.title', 'Agile Project Management')
                ->where('certificates.4.issuer', 'Google')
                ->where('certificates.4.provider_name', 'Google')
                ->where('certificates.4.thumbnail_url', 'https://api.iconify.design/logos/google-icon.svg')
                ->has('education', 3)
                ->where('education.0.institution', 'Caraga State University')
                ->where('education.0.program', 'Master of Science in Information Technology')
                ->where('education.0.end_date', 'June 2026')
                ->where('education.0.logo_url', '/storage/portfolio/organizations/caraga-state-university.png')
                ->has('workExperiences', 5)
                ->where('workExperiences.0.logo_url', '/storage/portfolio/organizations/snsu.png')
                ->where('workExperiences.0.end_date', '2026')
                ->where('workExperiences.0.is_current', false)
                ->where('workExperiences.1.logo_url', '/storage/portfolio/organizations/tmb-tax-bureau.png')
                ->where('workExperiences.1.position', 'Virtual Assistant')
                ->where('workExperiences.1.end_date', '2026')
                ->where('workExperiences.1.summary', 'Created courses, e-books, and visual assets while managing GoHighLevel and supporting a range of graphic design requirements.')
                ->where('workExperiences.1.responsibilities.0', 'Course creation')
                ->where('workExperiences.1.responsibilities.2', 'GoHighLevel management')
                ->where('workExperiences.1.responsibilities.3', 'E-book creation')
                ->where('workExperiences.1.is_current', false)
                ->where('workExperiences.2.logo_url', '/storage/portfolio/organizations/department-of-health.png')
                ->has('guestbookEntries', 0)
                ->where('publishedWorks.0.title', 'A Hybrid Time-Series Forecasting and Underperformance Risk Prediction System for Child Immunization Coverage in Surigao del Norte, Philippines: Evidence from FHSIS Quarterly Data')
                ->where('publishedWorks.0.publication', 'Approved for Scopus publication')
                ->where('publishedWorks.0.published_on', null)
                ->where('publishedWorks.1.title', 'SURe-Health: PDOHO-SDN Official Publication')
                ->where('publishedWorks.1.cover_url', '/storage/portfolio/publications/sure-health-cover.webp')
                ->where('publishedWorks.1.external_url', 'https://online.fliphtml5.com/utpit/pzyy/')
                ->where('projects.0.title', 'Governed MCH Forecasting and Risk Prediction DSS')
                ->where('projects.0.category', 'Health informatics DSS')
                ->where('projects.0.repo_url', 'https://github.com/bmontalvossct/Forecasting-and-Risk-Prediction')
                ->where('projects.0.thumbnail_url', '/storage/portfolio/projects/mch-forecasting-risk-dss.png')
                ->where('projects.1.title', 'SDN Electronic Medical Records Dashboard')
                ->where('projects.1.url', 'https://app.powerbi.com/view?r=eyJrIjoiMDBmNTFkOTYtMGQ1ZC00MTUzLWIxMmEtMzgwMTUwMmE5ODUzIiwidCI6IjE5NWQzN2JlLTllMGEtNDIwNS1hZGY0LWEyNTk5ZTllMWNjYSIsImMiOjEwfQ%3D%3D&pageName=ReportSection')
                ->where('projects.2.title', 'Surigao del Norte State University Public Website')
                ->where('projects.2.url', 'https://gamma.snsu.edu.ph')
                ->where('projects.2.thumbnail_url', '/storage/portfolio/projects/snsu-gamma-home.png')
                ->where('projects.3.repo_url', 'https://github.com/kristoffmontalvo218/Amplifier-repo')
            );

        $this->assertFileExists(public_path('images/brands/sap.svg'));
    }

    public function test_profile_page_discovers_images_videos_and_pdf_media_from_portfolio_storage(): void
    {
        $this->withoutVite();
        Storage::fake('public');
        config()->set('portfolio.discover_stored_media_in_tests', true);
        $this->seed();

        Storage::disk('public')->put('portfolio/Images/Campaign Poster.jpg', 'image');
        Storage::disk('public')->put('portfolio/Images/Brochure Design.pdf', 'pdf');
        Storage::disk('public')->put('portfolio/Videos/Launch Reel.mp4', 'video');
        Storage::disk('public')->put('portfolio/design-thumbnails/Launch Reel.jpg', 'thumbnail');
        Storage::disk('public')->put('portfolio/Images/notes.txt', 'not media');

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('designMedia', 3)
                ->where('designMedia.0.title', 'Brochure Design')
                ->where('designMedia.0.media_type', 'pdf')
                ->where('designMedia.0.type_label', 'Brochure PDF')
                ->where('designMedia.0.media_url', '/storage/portfolio/Images/Brochure%20Design.pdf')
                ->where('designMedia.1.title', 'Campaign Poster')
                ->where('designMedia.1.media_type', 'image')
                ->where('designMedia.1.media_url', '/storage/portfolio/Images/Campaign%20Poster.jpg')
                ->where('designMedia.2.title', 'Launch Reel')
                ->where('designMedia.2.media_type', 'video')
                ->where('designMedia.2.media_url', '/storage/portfolio/Videos/Launch%20Reel.mp4')
                ->where('designMedia.2.thumbnail_url', '/storage/portfolio/design-thumbnails/Launch%20Reel.jpg')
            );

        $this->get('/designs')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Profile/DesignMediaArchive')
                ->has('items', 3)
                ->where('items.0.title', 'Brochure Design')
                ->where('items.0.media_type', 'pdf')
            );
    }
    public function test_complete_portfolio_seeders_are_idempotent_and_exclude_private_certificate_files(): void
    {
        $this->seed();
        $this->seed();

        $this->assertDatabaseCount('profiles', 1);
        $this->assertDatabaseCount('achievements', 4);
        $this->assertDatabaseCount('certificates', 51);
        $this->assertDatabaseCount('credential_badges', 29);
        $this->assertDatabaseCount('education', 3);
        $this->assertDatabaseCount('work_experiences', 5);
        $this->assertDatabaseCount('published_works', 2);
        $this->assertDatabaseCount('projects', 5);
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
    public function test_guestbook_entries_remain_private_until_the_admin_approves_them(): void
    {
        $this->withoutVite();
        $this->seed();

        $this->post('/guestbook', [
            'name' => 'Portfolio Reviewer',
            'email' => 'reviewer@example.test',
            'role_or_organization' => 'Research partner',
            'body' => 'The portfolio presents the research and systems work clearly.',
            'rating' => 5,
            'website' => '',
        ])->assertRedirect()
            ->assertSessionHas('success');

        $entry = GuestbookEntry::query()->firstOrFail();

        $this->assertSame('pending', $entry->status);
        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->has('guestbookEntries', 0));

        $this->withSession(['portfolio_admin_authenticated' => true])
            ->put("/admin/guestbook/{$entry->id}", [
                'status' => 'approved',
                'admin_reply' => 'Thank you for taking the time to leave this note.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('guestbookEntries', 1)
                ->where('guestbookEntries.0.name', 'Portfolio Reviewer')
                ->where('guestbookEntries.0.admin_reply', 'Thank you for taking the time to leave this note.')
                ->missing('guestbookEntries.0.email')
            );
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
                ->has('items', 29)
                ->where('items.0.title', 'Networking Basics')
                ->where('items.0.category', 'networking')
            );

        $this->get('/certifications')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Profile/CredentialArchive')
                ->where('profile.avatar_url', 'https://avatars.githubusercontent.com/u/52160082?v=4')
                ->where('archiveType', 'certificates')
                ->where('alternate.url', '/badges')
                ->has('items', 52)
                ->where('items.0.title', 'Lean Six Sigma: Yellow Belt')
                ->where('items.0.category', 'project_management')
                ->where('items.0.date', 'Jul 2026')
                ->where('items.0.image_url', 'https://cdn01.alison-static.net/public/html/vendor/img/favicon/apple-touch-icon.png')
                ->where('items.0.verify_url', 'https://alison.com/verify/31557408f8')
                ->where('items.1.title', 'Sangfor Network Security and Endpoint Secure Technical Training')
                ->where('items.1.category', 'cybersecurity')
                ->where('items.1.date', 'Mar 2026')
                ->where('items.1.image_url', '/storage/portfolio/organizations/sangfor.png')
                ->where('items.1.verify_url', null)
                ->where('items.2.title', 'Applied Cybersecurity Operations')
                ->where('items.2.category', 'cybersecurity')
                ->where('items.2.verify_url', 'https://example.test/certificates/cybersecurity')
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
