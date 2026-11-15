<?php

namespace Database\Seeders;

use App\Models\PortfolioTool;
use Illuminate\Database\Seeder;

class PortfolioToolSeeder extends Seeder
{
    public function run(): void
    {
        $brandIcons = [
            'PHP' => 'https://api.iconify.design/logos/php.svg',
            'Laravel' => 'https://api.iconify.design/logos/laravel.svg',
            'Vue.js' => 'https://api.iconify.design/logos/vue.svg',
            'Inertia.js' => 'https://api.iconify.design/simple-icons/inertia.svg',
            'Tailwind CSS' => 'https://api.iconify.design/logos/tailwindcss-icon.svg',
            'JavaScript' => 'https://api.iconify.design/logos/javascript.svg',
            'Microsoft Power BI' => 'https://api.iconify.design/logos/microsoft-power-bi.svg',
            'Microsoft Excel' => 'https://api.iconify.design/mdi/microsoft-excel.svg?color=%23217346',
            'Adobe Creative Suite' => 'https://api.iconify.design/cib/adobe-creative-cloud.svg?color=%23DA1F26',
            'Canva' => 'https://api.iconify.design/devicon/canva.svg',
            'Git' => 'https://api.iconify.design/logos/git-icon.svg',
            'GitHub' => 'https://api.iconify.design/logos/github-icon.svg',
            'Microsoft Office' => 'https://api.iconify.design/logos/microsoft-icon.svg',
            'Python' => 'https://api.iconify.design/logos/python.svg',
            'WordPress' => 'https://api.iconify.design/logos/wordpress-icon.svg',
            'GoHighLevel' => 'https://assets.cdn.filesafe.space/zELBHkVp0JPbbLvKIlF5/media/690a5f4a57ea175183408da2.png',
            'n8n' => 'https://api.iconify.design/simple-icons/n8n.svg',
            'Zapier' => 'https://api.iconify.design/logos/zapier-icon.svg',
            'Claude' => 'https://api.iconify.design/logos/anthropic-icon.svg',
            'ChatGPT' => 'https://api.iconify.design/logos/openai-icon.svg',
            'Gemini' => 'https://api.iconify.design/logos/google-gemini.svg',
            'NotebookLM' => 'https://api.iconify.design/logos/google-icon.svg',
            'Hugging Face' => 'https://api.iconify.design/logos/hugging-face-icon.svg',
            'GPTZero' => 'https://gptzero.me/favicon.ico',
            'Facebook Ads' => 'https://api.iconify.design/logos/facebook.svg',
            'MikroTik' => 'https://api.iconify.design/simple-icons/mikrotik.svg',
            'Cisco' => 'https://api.iconify.design/simple-icons/cisco.svg',
            'Shopify' => 'https://api.iconify.design/logos/shopify.svg',
            'Google BigQuery' => 'https://api.iconify.design/logos/google-cloud.svg',
            'SAP' => 'https://api.iconify.design/simple-icons/sap.svg',
            'Notion' => 'https://api.iconify.design/logos/notion-icon.svg',
            'Google Workspace' => 'https://api.iconify.design/logos/google-icon.svg',
            'cPanel' => 'https://api.iconify.design/logos/cpanel.svg',
            'GitLab' => 'https://api.iconify.design/logos/gitlab.svg',
            'Vercel' => 'https://api.iconify.design/logos/vercel-icon.svg',
        ];

        $tools = [
            ['category' => 'backend', 'name' => 'PHP', 'description' => 'Server-side application development and automation.', 'sort_order' => 1],
            ['category' => 'backend', 'name' => 'Laravel', 'description' => 'Database-backed web applications, APIs, and administration systems.', 'sort_order' => 2],
            ['category' => 'backend', 'name' => 'SQL databases', 'description' => 'Relational data modeling, querying, reporting, and application persistence.', 'sort_order' => 3],
            ['category' => 'backend', 'name' => 'Python', 'description' => 'Automation, data processing, scripting, and application development.', 'sort_order' => 4],
            ['category' => 'frontend', 'name' => 'Vue.js', 'description' => 'Responsive interfaces and component-based application experiences.', 'sort_order' => 1],
            ['category' => 'frontend', 'name' => 'Inertia.js', 'description' => 'Laravel-driven single-page application workflows.', 'sort_order' => 2],
            ['category' => 'frontend', 'name' => 'Tailwind CSS', 'description' => 'Responsive interface systems and production UI styling.', 'sort_order' => 3],
            ['category' => 'frontend', 'name' => 'JavaScript', 'description' => 'Interactive browser behavior and application logic.', 'sort_order' => 4],
            ['category' => 'automation', 'name' => 'n8n', 'description' => 'Visual workflow automation, API orchestration, and system integrations.', 'sort_order' => 1],
            ['category' => 'automation', 'name' => 'Zapier', 'description' => 'No-code automation between business applications and cloud services.', 'sort_order' => 2],
            ['category' => 'automation', 'name' => 'Claude', 'description' => 'Research, analysis, writing, and AI-assisted technical workflows.', 'sort_order' => 3],
            ['category' => 'automation', 'name' => 'ChatGPT', 'description' => 'Generative AI for planning, development, analysis, and content workflows.', 'sort_order' => 4],
            ['category' => 'automation', 'name' => 'Gemini', 'description' => 'Google AI assistance for research, productivity, and multimodal work.', 'sort_order' => 5],
            ['category' => 'automation', 'name' => 'NotebookLM', 'description' => 'Source-grounded research, document synthesis, study guides, and audio overviews.', 'sort_order' => 6],
            ['category' => 'automation', 'name' => 'Hugging Face', 'description' => 'Exploring and applying AI models, datasets, Spaces, and inference workflows.', 'sort_order' => 7],
            ['category' => 'automation', 'name' => 'GPTZero', 'description' => 'AI-content analysis, responsible AI literacy, and transparent authorship workflows.', 'sort_order' => 8],
            ['category' => 'data', 'name' => 'Microsoft Power BI', 'description' => 'Interactive dashboards, health informatics, and operational reporting.', 'sort_order' => 1],
            ['category' => 'data', 'name' => 'Microsoft Excel', 'description' => 'Data preparation, analysis, structured records, and reporting.', 'sort_order' => 2],
            ['category' => 'data', 'name' => 'Google BigQuery', 'description' => 'Cloud data warehousing, large-scale querying, and analytics workloads.', 'sort_order' => 3],
            ['category' => 'data', 'name' => 'SAP', 'description' => 'Enterprise process integration, business systems, and operational reporting.', 'sort_order' => 4],
            ['category' => 'design', 'name' => 'Adobe Creative Suite', 'description' => 'Publication design, image editing, graphics, and visual communication.', 'sort_order' => 1],
            ['category' => 'design', 'name' => 'Canva', 'description' => 'Digital publications, campaign graphics, presentations, and social media assets.', 'sort_order' => 2],
            ['category' => 'infrastructure', 'name' => 'MikroTik', 'description' => 'MikroTik consulting, RouterOS configuration, network design, troubleshooting, and connectivity management.', 'sort_order' => 1],
            ['category' => 'infrastructure', 'name' => 'Cisco', 'description' => 'Networking fundamentals, routing, switching, and infrastructure support.', 'sort_order' => 2],
            ['category' => 'infrastructure', 'name' => 'cPanel', 'description' => 'Web hosting administration, domains, DNS, SSL, databases, and production deployments.', 'sort_order' => 3],
            ['category' => 'platforms', 'name' => 'WordPress', 'description' => 'Content-managed websites, publishing workflows, and site administration.', 'sort_order' => 1],
            ['category' => 'platforms', 'name' => 'GoHighLevel', 'description' => 'CRM, marketing automation, funnels, campaigns, and lead management.', 'sort_order' => 2],
            ['category' => 'platforms', 'name' => 'Facebook Ads', 'description' => 'Paid social campaigns, audience targeting, and performance monitoring.', 'sort_order' => 3],
            ['category' => 'platforms', 'name' => 'Shopify', 'description' => 'E-commerce storefront management, products, content, and integrations.', 'sort_order' => 4],
            ['category' => 'platforms', 'name' => 'Notion', 'description' => 'Collaborative documentation, knowledge management, planning, and structured workspaces.', 'sort_order' => 5],
            ['category' => 'platforms', 'name' => 'Google Workspace', 'description' => 'Gmail, Drive, Docs, Sheets, Slides, Forms, Meet, Calendar, Sites, and collaborative workflows.', 'sort_order' => 6],
            ['category' => 'development', 'name' => 'Git', 'description' => 'Source control and change management for software projects.', 'sort_order' => 1],
            ['category' => 'development', 'name' => 'GitHub', 'description' => 'Code hosting, collaboration, and public project documentation.', 'sort_order' => 2],
            ['category' => 'development', 'name' => 'GitLab', 'description' => 'Git repositories, collaborative development, and CI/CD pipelines.', 'sort_order' => 3],
            ['category' => 'development', 'name' => 'Vercel', 'description' => 'Frontend deployments, preview environments, and managed web hosting.', 'sort_order' => 4],
            ['category' => 'other', 'name' => 'Microsoft Office', 'description' => 'Documentation, presentations, administrative work, and professional reporting.', 'sort_order' => 1],
            ['category' => 'other', 'name' => 'Electronic medical record systems', 'description' => 'Implementation, training, monitoring, support, and health-facility readiness work.', 'sort_order' => 2],
        ];

        foreach ($tools as $tool) {
            PortfolioTool::query()->updateOrCreate(
                ['category' => $tool['category'], 'name' => $tool['name']],
                [...$tool, 'icon_url' => $brandIcons[$tool['name']] ?? null, 'is_featured' => true],
            );
        }
    }
}
