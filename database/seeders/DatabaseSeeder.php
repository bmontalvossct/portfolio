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
                'headline' => 'Registered Cybersecurity Professional (DICT Intermediate Level 2) | Systems Analyst, Researcher, & Developer',
                'availability' => 'Open to systems integration, cybersecurity consulting, research, and digital services.',
                'location' => 'Surigao del Norte, Philippines',
                'email' => 'inquiries@brittmontalvo.dev',
                'bio' => 'Specializing in full-stack web development, cybersecurity, network infrastructure, AI-powered solutions, data analytics, and workflow automation. Helping businesses, educational institutions, and organizations build secure, scalable, and user-friendly digital solutions that streamline operations, improve efficiency, and support digital transformation.',
                'avatar_url' => '/images/profile-avatar.jpg',
                'credly_username' => 'brittm',
                'github_username' => 'bmontalvossct',
                'external_links' => [
                    ['label' => 'Credly', 'url' => 'https://www.credly.com/users/brittm'],
                    ['label' => 'GitHub', 'url' => 'https://github.com/bmontalvossct'],
                    ['label' => 'LinkedIn', 'url' => 'https://www.linkedin.com/in/britt-kristoff-montalvo/'],
                ],
                'highlights' => [
                    'DICT Registered Cybersecurity Professional - Intermediate Level 2',
                    'MikroTik Consultant for network design, routing, and security',
                    'GPTZero Ambassador promoting responsible AI use and academic integrity',
                ],
                'skills' => [
                    'Cybersecurity',
                    'Vulnerability Assessment',
                    'Systems analysis',
                    'Data management',
                    'Electronic medical records',
                    'Technical support',
                    'Network administration',
                    'MikroTik consulting',
                    'Research and reporting',
                    'Laravel and PHP',
                    'Vue.js & Inertia.js',
                    'Adobe Creative Suite',
                ],
            ],
        );

        $this->call(PortfolioToolSeeder::class);

        $this->call(CompleteCredentialBadgeSeeder::class);

        Achievement::query()->updateOrCreate(
            ['title' => 'DICT Registered Cybersecurity Professional - Intermediate Level 2'],
            [
                'issuer' => 'Department of Information and Communications Technology (DICT)',
                'summary' => 'Registered Cybersecurity Professional under the DICT Cybersecurity Competency Framework at Intermediate Level 2.',
                'achieved_on' => '2026-01-15',
                'image_url' => '/storage/portfolio/organizations/dict.svg',
                'external_url' => 'https://dict.gov.ph',
                'tags' => ['DICT', 'Cybersecurity Professional', 'Intermediate Level 2', 'Information Security'],
                'sort_order' => 0,
                'is_featured' => true,
            ],
        );

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

        Achievement::query()->firstOrCreate(
            ['title' => 'Scam Ready ASEAN Master Trainer', 'issuer' => 'SmartCT / ASEAN Foundation'],
            [
                'summary' => 'Recognized and authorized as a Master Trainer for the Scam Ready ASEAN Programme under SmartCT, delivering digital literacy and safety training and Be Scam Ready gameplay activities. Valid until December 2027.',
                'achieved_on' => null,
                'image_url' => '/images/recognitions/scam-ready-asean-logo.png',
                'external_url' => 'https://aseanfoundation.org/programme/scam-ready-asean/',
                'tags' => ['Master Trainer', 'Digital literacy', 'Online safety'],
                'sort_order' => 5,
                'is_featured' => true,
            ],
        );

        Education::query()->updateOrCreate(
            ['institution' => 'Caraga State University', 'program' => 'Master of Science in Information Technology'],
            [
                'logo_url' => '/storage/portfolio/organizations/caraga-state-university.png',
                'level' => 'Master\'s degree',
                'end_date' => 'June 2026',
                'description' => null,
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
            ->delete();

        $experiences = [
            [
                'organization' => 'Self-employed',
                'position' => 'Freelancer',
                'start_date' => '2026',
                'end_date' => null,
                'summary' => 'Provide freelance services specializing in graphic design, video editing, AI-powered automation, and Customer Relationship Management (CRM) solutions. Help businesses, entrepreneurs, and organizations improve their digital presence, streamline operations, optimize workflows, and enhance productivity through creative content and technology-driven solutions.',
                'responsibilities' => [
                    '1. Graphic Design and Digital Content Creation',
                    'Design professional graphics, social media materials, marketing assets, presentations, and digital promotional content.',
                    'Develop visually engaging materials aligned with client branding, communication goals, and target audiences.',
                    '2. Video Editing and Multimedia Production',
                    'Edit and produce videos for social media, promotional campaigns, educational materials, and other digital platforms.',
                    'Enhance multimedia content through transitions, captions, audio editing, visual effects, and storytelling techniques.',
                    '3. AI Automation and Workflow Development',
                    'Design and implement AI-powered workflows to automate repetitive business tasks and improve operational efficiency.',
                    'Integrate AI tools, third-party applications, APIs, and automation platforms to streamline business processes.',
                    'Develop automated systems for data processing, notifications, document management, and information retrieval.',
                    '4. CRM Management and Business Process Optimization',
                    'Organize, maintain, and update customer records, contact databases, and CRM workflows.',
                    'Configure automated lead tracking, customer follow-ups, task assignments, and communication processes.',
                    'Improve customer engagement and operational efficiency through structured data management and workflow automation.',
                    '5. Client Collaboration and Technical Support',
                    'Collaborate with clients to understand project requirements and develop tailored creative and technical solutions.',
                    'Manage project deliverables, implement revisions, troubleshoot workflow issues, and provide ongoing technical assistance.',
                    'Ensure the quality, consistency, and timely delivery of freelance projects.',
                ],
                'sort_order' => 0,
                'is_current' => true,
            ],
            [
                'organization' => 'Surigao del Norte State University',
                'logo_url' => '/storage/portfolio/organizations/snsu.png',
                'position' => 'Information Systems Analyst I',
                'start_date' => 'November 25, 2024',
                'end_date' => 'June 18, 2026',
                'summary' => 'Systems analysis, data operations, technical support, network administration, documentation, and user training for university services.',
                'responsibilities' => [
                    'Systems Analysis and Design: Analyze existing information systems to identify areas for improvement and assist in designing new IT solutions or modifying existing systems to enhance user experience and operational efficiency.',
                    'Data Management and Analysis: Gather, validate, and document system information and needs; identify and interpret data patterns and trends; and analyze results using statistical techniques or tools.',
                    'Technical Support: Provide technical assistance to business owners and end-users of information systems, including troubleshooting hardware and software issues, and ensuring smooth operation of various information systems.',
                    'Network Administration: Understand network concepts and be capable of administering and operating various information systems, including system administration and infrastructure development towards digitalization.',
                    'Documentation and Reporting: Prepare and maintain technical documents, user manuals, and system processes; document system changes and enhancements; and produce accomplishment reports periodically.',
                    'User Training: Conduct user training sessions to ensure proper utilization of information systems and best practices.',
                    'Research and Market Analysis: Conduct research and market analysis to stay updated with industry trends and advancements in information systems.',
                ],
                'sort_order' => 1,
                'is_current' => false,
            ],
            [
                'organization' => 'Department of Health',
                'logo_url' => '/storage/portfolio/organizations/department-of-health.png',
                'position' => 'Computer Programmer II',
                'start_date' => 'April 30, 2024',
                'end_date' => 'November 25, 2024',
                'summary' => 'Implemented and supported electronic medical record systems across health facilities, including readiness assessment, training, monitoring, and technical reporting.',
                'responsibilities' => [
                    'Oversee the overall implementation of EMR Records.',
                    'Assist in Assessment of hospital readiness for system implementation.',
                    'Orient and train personnel of local health facilities on how to use DOH implement systems.',
                    'Install Relevant systems needed in respective health facilities.',
                    'Conduct regular visits, troubleshooting, and monitoring in all health facilities.',
                    'Validate request for username and password for particular systems.',
                    'Monitor health facility systems for proper compliance.',
                    'Answer phone calls and update FAQs encountered during system implementation.',
                    'Submit monthly reports/transcriptions/summaries of all queries and actions made to the regional office.',
                    'Generate service statistics or other relevant reports requested by relevant and authorized offices.',
                    'Provide other forms of technical assistance connected to the implemented systems.',
                    'Perform other tasks that may be assigned related to EMR implementation.',
                    'Conduct training regarding ICLINICSYS to Surigao del Norte Health Facilities.',
                    'Create Statistics/Analytical reports.',
                    'Create Formulated Excels.',
                    'Attend meetings if necessary.',
                    'Handle and provide troubleshooting assistance to health facilities regarding DOH-implemented websites.',
                    'Email advisories/administrative orders to health facilities.',
                ],
                'sort_order' => 2,
                'is_current' => false,
            ],
            [
                'organization' => 'Department of Health',
                'logo_url' => '/storage/portfolio/organizations/department-of-health.png',
                'position' => 'Computer Programmer I',
                'start_date' => 'February 18, 2021',
                'end_date' => 'April 30, 2024',
                'summary' => 'Supported health information systems, user accounts, data reports, facility visits, and troubleshooting for regional health programs.',
                'responsibilities' => [
                    'Oversee the overall implementation of EMR Records.',
                    'Assist in Assessment of hospital readiness for system implementation.',
                    'Orient and train personnel of local health facilities on how to use DOH implement systems.',
                    'Install Relevant systems needed in respective health facilities.',
                    'Conduct regular visits, troubleshooting, and monitoring in all health facilities.',
                    'Validate request for username and password for particular systems.',
                    'Monitor health facility systems for proper compliance.',
                    'Answer phone calls and update FAQs encountered during system implementation.',
                    'Submit monthly reports/transcriptions/summaries of all queries and actions made to the regional office.',
                    'Generate service statistics or other relevant reports requested by relevant and authorized offices.',
                    'Provide other forms of technical assistance connected to the implemented systems.',
                    'Perform other tasks that may be assigned related to EMR implementation.',
                    'Conduct training regarding ICLINICSYS to Surigao del Norte Health Facilities.',
                    'Create Statistics/Analytical reports.',
                    'Create Formulated Excels.',
                    'Attend meetings if necessary.',
                    'Handle and provide troubleshooting assistance to health facilities regarding DOH-implemented websites.',
                    'Email advisories/administrative orders to health facilities.',
                ],
                'sort_order' => 3,
                'is_current' => false,
            ],
            [
                'organization' => 'Cebupot',
                'logo_url' => '/storage/portfolio/organizations/cebupot.png',
                'position' => 'Intern',
                'start_date' => 'March 2018',
                'end_date' => 'June 2018',
                'summary' => 'Supported database updates, administrative work, image editing, documentation, and ICT equipment setup.',
                'responsibilities' => [
                    'Assist in updating and maintaining Excel databases, ensuring accurate and organized data storage for operational needs.',
                    'Provide administrative assistance to supervisors, including scheduling appointments, organizing documents, and handling correspondence to support daily office operations.',
                    'Participate in coordinating and conducting photo ops for company units, assisting with setup, image capture, and post-production editing as directed by supervisors.',
                    'Support supervisors in editing and enhancing photos using editing software, following provided guidelines and instructions to maintain consistency and quality.',
                    'Assist in processing and issuing checks when required, ensuring accurate documentation and adherence to financial procedures under supervision.',
                    'Attend company meetings as directed, actively observing and contributing as appropriate, gaining insights into organizational processes and decision-making.',
                    'Provide technical assistance for company ICT equipment under supervision, learning troubleshooting techniques and maintenance procedures to ensure equipment functionality.',
                ],
                'sort_order' => 4,
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
                'sort_order' => 3,
                'is_featured' => true,
            ],
        );

        PublishedWork::query()->updateOrCreate(
            [
                'type' => 'scopus_paper',
                'title' => 'Forecasting Child Immunization Coverage and Predicting Underperformance Risk Using a Hybrid Time-Series Framework: Evidence from FHSIS Data',
            ],
            [
                'publication' => 'Accepted by IDAP (International Data & Analytics Proceedings)',
                'external_url' => 'https://ieeexplore.ieee.org/document/11727904',
                'role' => 'Author / Primary Researcher',
                'summary' => 'Fully Immunized Child (FIC) coverage among children in the Philippines is an essential public health indicator, but the routinely utilized health information systems in the Philippines do not have prospective forecasting capabilities to inform public health decision-making. This paper introduces a temporally evaluated adaptive framework for forecasting and underperformance risk prediction of FIC coverage using quarterly Field Health Services Information System (FHSIS) data from Surigao del Norte, Philippines. Historical data (2021-2024) was used for expanding window cross-validation model selection, with 2025 held out for temporal evaluation. Models evaluated include ARIMA, Holt-Winters, Prophet, Moving Average, Naïve, Seasonal Naïve, Annual Profile, and Random Forest classifier (sensitivity = 1.0000, ROC-AUC = 0.9753).',
                'tags' => ['Scopus Paper', 'Accepted by IDAP', 'FHSIS', 'Child Immunization', 'Time-Series Forecasting', 'Random Forest'],
                'sort_order' => 1,
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
                'tags' => ['Scopus Paper', 'FHSIS', 'Child Immunization', 'Time-Series Forecasting', 'Risk Prediction'],
                'sort_order' => 2,
                'is_featured' => true,
            ],
        );

        Project::query()->updateOrCreate(
            ['title' => 'Governed MCH Forecasting and Risk Prediction DSS'],
            [
                'category' => 'Health informatics DSS',
                'summary' => 'A working decision-support system for provincial maternal and child health planning. It governs per-series model selection across ARIMA, Holt-Winters ETS, Prophet, and baseline methods, pairs forecast intervals with underperformance risk assessment, and translates projected service gaps into actionable needed counts.',
                'year' => '2026',
                'repo_url' => 'https://github.com/bmontalvossct/Forecasting-and-Risk-Prediction',
                'thumbnail_url' => '/storage/portfolio/projects/mch-forecasting-risk-dss.png',
                'tags' => ['Laravel', 'Vue.js', 'Python', 'DOH', 'FHSIS', 'Power BI'],
                'sort_order' => 1,
                'is_featured' => true,
            ],
        );

        Project::query()->updateOrCreate(
            ['title' => 'ClassCheck'],
            [
                'category' => 'Web system',
                'summary' => 'A full-stack open-source classroom management, real-time attendance tracking, interactive seating layout designer, recitation scoring, and weighted gradebook reporting system.',
                'year' => '2026',
                'url' => 'https://classcheck.brittmontalvo.dev',
                'repo_url' => 'https://github.com/bmontalvossct/classcheck',
                'gallery_urls' => ['/images/projects/classcheck-home.jpg', '/images/projects/classcheck-features.jpg'],
                'tags' => ['Laravel', 'Vue.js', 'Inertia.js', 'Tailwind CSS', 'MySQL', 'Git'],
                'sort_order' => 2,
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
                'tags' => ['Power BI', 'DOH', 'Python', 'SQL', 'MySQL'],
                'sort_order' => 3,
                'is_featured' => true,
            ],
        );

        // Supply the dashboard preview only when no custom thumbnail has been saved.
        Project::query()
            ->where('title', 'SDN Electronic Medical Records Dashboard')
            ->where(fn ($query) => $query->whereNull('thumbnail_url')->orWhere('thumbnail_url', ''))
            ->update(['thumbnail_url' => '/images/projects/sdn-emr-dashboard-preview.png']);

        Project::query()->updateOrCreate(
            ['title' => 'Surigao del Norte State University Public Website'],
            [
                'category' => 'Web system',
                'summary' => 'A responsive public university portal developed with the SNSU KMIC Office to bring academics, admissions, research, news, events, procurement, careers, library services, and institutional information into one accessible experience.',
                'year' => '2026',
                'url' => 'https://gamma.snsu.edu.ph',
                'thumbnail_url' => '/storage/portfolio/projects/snsu-gamma-home.png',
                'tags' => ['Laravel', 'Vue.js', 'Inertia.js', 'Tailwind CSS', 'MySQL', 'SNSU'],
                'sort_order' => 4,
                'is_featured' => true,
            ],
        );

        Project::query()->updateOrCreate(
            ['title' => 'Amplifier publication & design system'],
            [
                'category' => 'Web system',
                'summary' => 'A publication and design asset management web system built using CodeIgniter PHP framework, MySQL database, and Bootstrap styling.',
                'year' => '2019',
                'repo_url' => 'https://github.com/kristoffmontalvo218/Amplifier-repo',
                'tags' => ['CodeIgniter', 'PHP', 'MySQL', 'Bootstrap'],
                'sort_order' => 5,
                'is_featured' => true,
            ],
        );

        Project::query()->updateOrCreate(
            ['title' => 'Health information systems implementation'],
            [
                'category' => 'Systems practice',
                'summary' => 'Implementation, training, monitoring, and technical support for electronic medical record systems used by health facilities.',
                'year' => '2021-2024',
                'tags' => ['DOH', 'FHSIS', 'Python', 'SQL'],
                'sort_order' => 6,
                'is_featured' => true,
            ],
        );

        $this->call(CompleteCertificateSeeder::class);
        $this->call(CredentialCategorySeeder::class);
    }
}
