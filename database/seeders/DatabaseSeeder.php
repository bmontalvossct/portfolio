<?php

namespace Database\Seeders;

use App\Models\Achievement;
use App\Models\Education;
use App\Models\Profile;
use App\Models\Project;
use App\Models\PublishedWork;
use App\Models\WorkExperience;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        Project::query()->where('title', 'Portfolio submission package')->delete();
        PublishedWork::query()->where('title', 'Scopus research publication')->delete();
        PublishedWork::query()->where('title', 'Digital magazine collection')->delete();

        Profile::query()->updateOrCreate(
            ['slug' => 'britt-m'],
            [
                'display_name' => 'Britt Kristoff B. Montalvo, MSIT',
                'headline' => 'Lead Digitalization Expert, information systems analyst, researcher, and multidisciplinary digital maker.',
                'availability' => 'Open to research, systems integration, systems development, and digital publication collaborations.',
                'location' => 'Surigao del Norte, Philippines',
                'email' => 'inquiries@brittmontalvo.dev',
                'bio' => 'I work across information systems, health technology implementation, data operations, research, and visual publishing. I also provide MikroTik network consulting and serve as a GPTZero Ambassador, promoting responsible and transparent AI use.',
                'avatar_url' => 'https://avatars.githubusercontent.com/u/52160082?v=4',
                'credly_username' => 'brittm',
                'github_username' => 'bmontalvossct',
                'external_links' => [
                    ['label' => 'Credly', 'url' => 'https://www.credly.com/users/brittm'],
                    ['label' => 'GitHub', 'url' => 'https://github.com/bmontalvossct'],
                    ['label' => 'LinkedIn', 'url' => 'https://www.linkedin.com/in/britt-kristoff-montalvo/'],
                ],
                'highlights' => [
                    'Information Systems Analyst I at Surigao del Norte State University',
                    'MikroTik Consultant for network design, configuration, and troubleshooting',
                    'GPTZero Ambassador promoting responsible AI use and academic integrity',
                    'Civil Service Eligibility: CSE Professional',
                    'Experienced in electronic medical record implementation and health facility systems support',
                    'Languages: English, Tagalog, and Visayan',
                ],
                'skills' => [
                    'Systems analysis',
                    'Data management',
                    'Electronic medical records',
                    'Technical support',
                    'Network administration',
                    'MikroTik consulting',
                    'Research and reporting',
                    'Hugging Face',
                    'Google Workspace',
                    'Laravel and PHP',
                    'Adobe Creative Suite',
                ],
            ],
        );

        $this->call(PortfolioToolSeeder::class);

        $this->call(CompleteCredentialBadgeSeeder::class);

        Achievement::query()->updateOrCreate(
            ['title' => 'Civil Service Eligibility - Professional'],
            [
                'issuer' => 'Civil Service Commission',
                'summary' => 'Listed among the passers of the 19 June 2022 Career Service Examination - Professional Level administered in the Caraga Region.',
                'achieved_on' => '2022-06-19',
                'image_url' => '/storage/portfolio/organizations/civil-service-commission.png',
                'external_url' => 'https://www.scribd.com/document/589242525/Caraga-06192022-PRO',
                'tags' => ['Civil Service Commission', 'CSE Professional', 'Public service'],
                'sort_order' => 1,
                'is_featured' => true,
            ],
        );

        Achievement::query()->updateOrCreate(
            ['title' => 'SAP Certified - Implementation Consultant'],
            [
                'issuer' => 'SAP',
                'summary' => 'Certified in end-to-end business processes for SAP Business Suite.',
                'achieved_on' => '2026-06-02',
                'image_url' => '/images/brands/sap.svg',
                'external_url' => 'https://www.credly.com/users/brittm',
                'tags' => ['SAP', 'Business processes'],
                'sort_order' => 2,
                'is_featured' => true,
            ],
        );

        Achievement::query()->updateOrCreate(
            ['title' => 'MikroTik Consultant'],
            [
                'issuer' => 'MikroTik Consultant Directory',
                'summary' => 'Listed in the official MikroTik consultant directory for the Philippines with active MTCNA, MTCRE, and MTCUME certifications.',
                'image_url' => 'https://cdn.simpleicons.org/mikrotik?viewbox=auto',
                'external_url' => 'https://mikrotik.com/consultants?category=consultants&f[0]=cert%3AMTCNA&f[1]=cert%3AMTCRE&f[2]=cert%3AMTCUME&region=Philippines',
                'tags' => ['MikroTik', 'MTCNA', 'MTCRE', 'MTCUME', 'Network consulting'],
                'sort_order' => 3,
                'is_featured' => true,
            ],
        );

        Achievement::query()->updateOrCreate(
            ['title' => 'GPTZero Ambassador'],
            [
                'issuer' => 'GPTZero',
                'summary' => 'Certificate-backed GPTZero Ambassador promoting responsible AI use, AI literacy, transparent authorship, and academic integrity.',
                'image_url' => 'https://gptzero.me/favicon.ico',
                'external_url' => null,
                'tags' => ['Responsible AI', 'AI literacy', 'Academic integrity'],
                'sort_order' => 4,
                'is_featured' => true,
            ],
        );

        Education::query()->updateOrCreate(
            ['institution' => 'Caraga State University', 'program' => 'Master of Science in Information Technology'],
            [
                'logo_url' => '/storage/portfolio/organizations/caraga-state-university.png',
                'level' => 'Master\'s degree',
                'end_date' => 'June 2026',
                'description' => 'Graduated in June 2026.',
                'sort_order' => 1,
            ],
        );

        Education::query()->updateOrCreate(
            ['institution' => 'University of San Carlos', 'program' => 'Bachelor of Science in Information Communications Technology'],
            [
                'logo_url' => '/storage/portfolio/organizations/university-of-san-carlos.png',
                'level' => 'Bachelor\'s degree',
                'start_date' => 'June 2015',
                'end_date' => 'December 2019',
                'activities' => [
                    'Vice President of Finance, June 2018 - June 2019',
                    'Assistant to the Vice President of Finance, June 2017 - June 2018',
                ],
                'sort_order' => 2,
            ],
        );

        WorkExperience::query()
            ->where('organization', 'TMB Tax Bureau')
            ->where('position', 'Virtual Assistant (Part-time)')
            ->update(['position' => 'Virtual Assistant']);

        $experiences = [
            [
                'organization' => 'Surigao del Norte State University',
                'logo_url' => '/storage/portfolio/organizations/snsu.png',
                'position' => 'Information Systems Analyst I',
                'start_date' => 'November 25, 2024',
                'end_date' => '2026',
                'summary' => 'Systems analysis, data operations, technical support, network administration, documentation, and user training for university services.',
                'responsibilities' => ['Systems analysis and design', 'Data management and reporting', 'Technical and network support', 'User training and documentation'],
                'sort_order' => 1,
                'is_current' => false,
            ],
            [
                'organization' => 'TMB Tax Bureau',
                'logo_url' => '/storage/portfolio/organizations/tmb-tax-bureau.png',
                'position' => 'Virtual Assistant',
                'start_date' => '2024',
                'end_date' => '2026',
                'summary' => 'Created courses, e-books, and visual assets while managing GoHighLevel and supporting a range of graphic design requirements.',
                'responsibilities' => ['Course creation', 'Graphic design', 'GoHighLevel management', 'E-book creation', 'Other graphic design work'],
                'sort_order' => 2,
                'is_current' => false,
            ],
            [
                'organization' => 'Department of Health',
                'logo_url' => '/storage/portfolio/organizations/department-of-health.png',
                'position' => 'Computer Programmer II',
                'start_date' => 'April 30, 2024',
                'end_date' => 'November 25, 2024',
                'summary' => 'Implemented and supported electronic medical record systems across health facilities, including readiness assessment, training, monitoring, and technical reporting.',
                'responsibilities' => ['EMR implementation', 'Health facility readiness assessment', 'Training and technical support', 'Compliance monitoring and reporting'],
                'sort_order' => 3,
                'is_current' => false,
            ],
            [
                'organization' => 'Department of Health',
                'logo_url' => '/storage/portfolio/organizations/department-of-health.png',
                'position' => 'Computer Programmer I',
                'start_date' => 'February 18, 2021',
                'end_date' => 'April 30, 2024',
                'summary' => 'Supported health information systems, user accounts, data reports, facility visits, and troubleshooting for regional health programs.',
                'responsibilities' => ['Health information system support', 'Data and service statistics', 'User account validation', 'Facility monitoring and troubleshooting'],
                'sort_order' => 4,
                'is_current' => false,
            ],
            [
                'organization' => 'Cebupot',
                'logo_url' => '/storage/portfolio/organizations/cebupot.png',
                'position' => 'Intern',
                'start_date' => 'March 2018',
                'end_date' => 'June 2018',
                'summary' => 'Supported database updates, administrative work, image editing, documentation, and ICT equipment setup.',
                'responsibilities' => ['Excel database updates', 'Photo editing and enhancement', 'Administrative support', 'ICT equipment assistance'],
                'sort_order' => 5,
                'is_current' => false,
            ],
        ];

        foreach ($experiences as $experience) {
            WorkExperience::query()->updateOrCreate(
                [
                    'organization' => $experience['organization'],
                    'position' => $experience['position'],
                    'start_date' => $experience['start_date'],
                ],
                $experience,
            );
        }

        PublishedWork::query()->updateOrCreate(
            ['type' => 'digital_magazine', 'title' => 'SURe-Health: PDOHO-SDN Official Publication'],
            [
                'publication' => 'Issue 01, Volume 1 (January-September 2024)',
                'role' => 'Publisher / Contributor',
                'summary' => 'The official digital publication of the Provincial DOH Office Surigao del Norte, presenting public health programs, field activities, institutional updates, and regional health stories.',
                'cover_url' => '/storage/portfolio/publications/sure-health-cover.webp',
                'external_url' => 'https://online.fliphtml5.com/utpit/pzyy/',
                'tags' => ['Digital magazine', 'Health communication', 'Editorial design', 'Public health'],
                'sort_order' => 2,
                'is_featured' => true,
            ],
        );

        PublishedWork::query()->updateOrCreate(
            [
                'type' => 'scopus_paper',
                'title' => 'A Hybrid Time-Series Forecasting and Underperformance Risk Prediction System for Child Immunization Coverage in Surigao del Norte, Philippines: Evidence from FHSIS Quarterly Data',
            ],
            [
                'publication' => 'Approved for Scopus publication',
                'role' => 'Researcher / System Developer',
                'summary' => 'Approved for Scopus publication, with final publication date and DOI pending. The study prospectively validates a governed hybrid pipeline that selects among ARIMA, Holt-Winters ETS, and Prophet for LGU-level FIC and CIC forecasting, then identifies municipalities at risk of missing the 95% coverage target.',
                'tags' => ['FHSIS', 'Child immunization', 'Time-series forecasting', 'Risk prediction', 'Scopus approved'],
                'sort_order' => 1,
                'is_featured' => true,
            ],
        );

        Project::query()->updateOrCreate(
            ['title' => 'Governed MCH Forecasting and Risk Prediction DSS'],
            [
                'category' => 'Health informatics DSS',
                'summary' => 'A working thesis decision-support system for provincial maternal and child health planning. It governs per-series model selection across ARIMA, Holt-Winters ETS, Prophet, and baseline methods, pairs forecast intervals with underperformance risk assessment, and translates projected service gaps into actionable needed counts. The local build is complete and deployment is pending.',
                'year' => '2026',
                'repo_url' => 'https://github.com/bmontalvossct/Forecasting-and-Risk-Prediction',
                'thumbnail_url' => '/storage/portfolio/projects/mch-forecasting-risk-dss.png',
                'tags' => ['Laravel', 'Vue.js', 'Python', 'FHSIS', 'Forecasting', 'Risk prediction', 'Governed DSS', 'Deployment pending'],
                'sort_order' => 1,
                'is_featured' => true,
            ],
        );

        Project::query()->updateOrCreate(
            ['title' => 'SDN Electronic Medical Records Dashboard'],
            [
                'category' => 'Power BI dashboard',
                'summary' => 'An interactive health informatics dashboard for monitoring Surigao del Norte facilities, electronic medical record status, patient activity, accreditation, training schedules, and trained personnel.',
                'year' => '2026',
                'url' => 'https://app.powerbi.com/view?r=eyJrIjoiMDBmNTFkOTYtMGQ1ZC00MTUzLWIxMmEtMzgwMTUwMmE5ODUzIiwidCI6IjE5NWQzN2JlLTllMGEtNDIwNS1hZGY0LWEyNTk5ZTllMWNjYSIsImMiOjEwfQ%3D%3D&pageName=ReportSection',
                'tags' => ['Power BI', 'Health informatics', 'Data visualization', 'Electronic medical records'],
                'sort_order' => 2,
                'is_featured' => true,
            ],
        );

        Project::query()->updateOrCreate(
            ['title' => 'Surigao del Norte State University Public Website'],
            [
                'category' => 'Web system',
                'summary' => 'A responsive public university portal developed with the SNSU KMIC Office to bring academics, admissions, research, news, events, procurement, careers, library services, and institutional information into one accessible experience.',
                'year' => '2026',
                'url' => 'https://gamma.snsu.edu.ph',
                'thumbnail_url' => '/storage/portfolio/projects/snsu-gamma-home.png',
                'tags' => ['University website', 'Public information', 'Responsive design', 'Content management'],
                'sort_order' => 3,
                'is_featured' => true,
            ],
        );

        Project::query()->updateOrCreate(
            ['title' => 'Amplifier thesis project'],
            [
                'category' => 'Web system',
                'summary' => 'An undergraduate thesis project developed as a web application and preserved in the public GitHub archive.',
                'year' => '2019',
                'repo_url' => 'https://github.com/kristoffmontalvo218/Amplifier-repo',
                'tags' => ['PHP', 'HTML', 'CSS', 'Thesis'],
                'sort_order' => 4,
                'is_featured' => true,
            ],
        );

        Project::query()->updateOrCreate(
            ['title' => 'Health information systems implementation'],
            [
                'category' => 'Systems practice',
                'summary' => 'Implementation, training, monitoring, and technical support for electronic medical record systems used by health facilities.',
                'year' => '2021-2024',
                'tags' => ['EMR', 'Health technology', 'Training', 'Data'],
                'sort_order' => 5,
                'is_featured' => true,
            ],
        );

        $this->call(CompleteCertificateSeeder::class);
        $this->call(CredentialCategorySeeder::class);
    }
}
