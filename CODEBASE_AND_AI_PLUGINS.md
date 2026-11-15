# Codebase and AI plugins study

Study date: 2 November 2026. Scope: application source, configuration, seed data, selected tests, local AI skill instructions and search implementation, and a read-only inspection of the local SQLite database. This is an orientation document, not a full security audit or proof of production behavior.

The person represented by this application is documented separately in [PERSON_PROFILE.md](./PERSON_PROFILE.md).

## Application purpose and stack

This is a database-backed professional portfolio for Britt Kristoff B. Montalvo, MSIT. It combines a public profile, service catalog, credentials, publications, projects, design/media archive, guestbook, and protected content administration.

| Layer | Declared stack | Source |
| --- | --- | --- |
| Backend | PHP `^8.3`, Laravel `^13.8`, Inertia Laravel `^3.1` | [composer.json](./composer.json) |
| Frontend | Vue `^3.5.39`, Inertia Vue `^3.6.1`, Vue single-file components | [package.json](./package.json), [frontend entry](./resources/js/app.js) |
| Build | Vite `^8.0.0`, Laravel Vite plugin, Vue plugin | [package.json](./package.json), [Vite config](./vite.config.js) |
| Visuals | Custom CSS, Lucide icons, GSAP, Motion | [stylesheet](./resources/css/app.css), [profile page](./resources/js/Pages/Profile/Show.vue), [dependencies](./package.json) |
| Data | Eloquent models and migrations; local SQLite database | [models](./app/Models/), [migrations](./database/migrations/), [database config](./config/database.php) |
| Verification | PHPUnit feature/unit tests | [tests](./tests/), [PHPUnit configuration](./phpunit.xml) |

Version ranges above are manifest requirements, not a claim about the exact packages installed in every environment. Tailwind appears in project portfolio descriptions, but this application's package manifest does not declare Tailwind. Its current UI styling should be understood from the actual stylesheet.

## Request and data flow

```text
Public request
  -> Laravel route and controller
  -> Eloquent records / supporting services / configuration
  -> Inertia page props
  -> Vue page

Admin form
  -> Token login and authenticated session
  -> Protected route and request validation
  -> Model update or media storage
  -> Public pages read the updated records

Scheduler or protected sync action
  -> Drive certificate / Credly badge / GitHub activity sync
  -> Database records or cached activity
  -> Public profile and credential archives
```

Sources: [web routes](./routes/web.php), [profile controller](./app/Http/Controllers/ProfileController.php), [admin controller](./app/Http/Controllers/Admin/AdminPortfolioController.php), [admin sync controller](./app/Http/Controllers/Admin/AdminDataSyncController.php), and [scheduled commands](./routes/console.php).

## Important application areas

| Area | Behavior observed in source | Main implementation |
| --- | --- | --- |
| Public profile | Loads profile, achievements, credentials, publications, projects, education, work history, tools, GitHub activity, media, and public reviews | [ProfileController](./app/Http/Controllers/ProfileController.php), [Show.vue](./resources/js/Pages/Profile/Show.vue) |
| Services | Nine offerings with audience, deliverables, tools, timeline labels, and proof links | [service catalog](./config/service_catalog.php), [Services.vue](./resources/js/Pages/Profile/Services.vue) |
| Credential archives | Badge/certificate presentation, category classification, provider metadata, and controlled certificate links | [archive controller](./app/Http/Controllers/CredentialArchiveController.php), [certificate exposure policy](./app/Support/Profile/PublicCertificateExposure.php) |
| Design and media | Combines managed database rows with supported files discovered in public storage, avoiding duplicate URLs | [media archive service](./app/Support/Profile/DesignMediaArchive.php), [archive page](./resources/js/Pages/Profile/DesignMediaArchive.vue) |
| Thumbnails | Serves generated portfolio image previews | [thumbnail controller](./app/Http/Controllers/PortfolioThumbnailController.php) |
| Guestbook | Validates submissions, uses a honeypot and throttling, publishes accepted submissions immediately, and sends a review notification | [guestbook controller](./app/Http/Controllers/GuestbookController.php), [notifier](./app/Support/Profile/GuestbookReviewNotifier.php) |
| Viewer presence | Tracks recent session identifiers with a 90-second window; displayed count adds a fluctuating baseline | [counter](./app/Support/Profile/ActiveViewerCounter.php), [presence endpoint](./app/Http/Controllers/ViewerPresenceController.php) |
| Admin | Token-based session login, content CRUD, uploads, sync actions, and guestbook management | [auth controller](./app/Http/Controllers/Admin/AdminAuthController.php), [admin middleware](./app/Http/Middleware/EnsurePortfolioAdmin.php), [admin page](./resources/js/Pages/Admin/Index.vue) |
| Search/agent discovery | Page metadata, structured data, sitemap, and a generated `/llms.txt` profile/service summary | [SEO helper](./app/Support/Seo/SeoMetadata.php), [discovery controller](./app/Http/Controllers/AgentDiscoveryController.php), [sitemap controller](./app/Http/Controllers/SitemapController.php) |

The viewer count is **not a raw audience measurement**: the implementation adds a baseline between 14 and 25 to the tracked active sessions. Do not use the displayed number as evidence of actual traffic or professional reach.

The guestbook's submission path currently sets `status = approved`; the existence of admin review controls does not mean every submission waits for moderation.

## Data ownership and maintenance

- The public page reads database records. The [main seeder](./database/seeders/DatabaseSeeder.php) is a reproducible content source, but admin edits can cause the database and seed data to differ.
- Many seed operations use `updateOrCreate`; re-running a seeder can replace edited values. The main seeder also deletes specific legacy records. Read it before running it against a maintained database.
- Credentials have separate [badge](./database/seeders/CompleteCredentialBadgeSeeder.php) and [certificate](./database/seeders/CompleteCertificateSeeder.php) snapshots, plus a [category seeder](./database/seeders/CredentialCategorySeeder.php).
- Service copy lives in [configuration](./config/service_catalog.php); it is not one of the admin CRUD resources listed in the routes.
- Design/media files can appear without managed database rows because the archive service scans supported storage folders. A `design_media` count of zero does not mean the archive is empty.
- [Certificate URL filtering](./app/Support/Profile/PublicCertificateExposure.php) excludes Google Drive links, non-HTTPS URLs, and direct document/image URLs from public certificate verification links. Preserve that distinction when reusing credential data.
- The [README](./README.md) combines project-specific Docker/sync instructions with Laravel starter documentation. Use application code and configuration to resolve ambiguous behavior.

The [scheduler](./routes/console.php) declares certificate sync every five minutes, Credly sync hourly at minute 10, and GitHub sync hourly at minute 20. These are configured schedules; this study did not verify a running worker or production cron. The [development command](./composer.json) includes the schedule worker, while the documented Docker startup requires separate migration and scheduling setup.

## AI plugins found in this repository

The project also has a specialist agent team, configured after this initial study: the main assistant orchestrates frontend, backend, quality assurance, DevOps, and security work. See [AGENTS.md](./AGENTS.md) for routing and [team operation](./.agents/TEAM.md) for responsibilities, handoffs, and runtime limits. The five definitions are in [.codex/agents](./.codex/agents/). This development workflow is separate from the two local skills studied below.

Two local agent skills are present under [.agents/skills](./.agents/skills/). They guide the coding assistant during development. They are distinct from features served by the Laravel application.

The [skill lockfile](./skills-lock.json) records these upstream origins:

| Local skill | Recorded upstream | Contents | Purpose |
| --- | --- | --- | --- |
| [frontend-design](./.agents/skills/frontend-design/SKILL.md) | `anthropics/skills` | Instructions and license | Establish visual direction, typography, palette, layout, motion, and interface copy; critique generic design choices |
| [ui-ux-pro-max](./.agents/skills/ui-ux-pro-max/SKILL.md) | `nextlevelbuilder/ui-ux-pro-max-skill` | Instructions, local datasets, references, Python search/generation code, and tests | Retrieve design recommendations and stack guidance, assemble design systems, and optionally persist design documentation |

The lockfile establishes recorded installation provenance. It does not establish which parts the user customized or authored. No custom MCP server, AI provider client, chatbot route, model-inference backend, or separate AI plugin manifest was located in the application source and local agent directory inspected. Other plugins may exist outside this repository; their names or source paths are needed to study them.

### frontend-design

The [instructions](./.agents/skills/frontend-design/SKILL.md) use a design-lead workflow: ground choices in the subject, draft a compact palette/type/layout plan, critique it against the brief, then implement and review. They emphasize deliberate typography, meaningful structure, restrained motion, accessible interaction, and clear interface language.

There is no executable search engine or model client in this skill's folder. Its effect depends on the assistant reading and following the instructions. When its general aesthetic suggestions conflict with an explicit project brief, its own text says the brief wins. Existing project direction is recorded in [DESIGN.md](./DESIGN.md).

### ui-ux-pro-max

The main execution path is:

```text
search.py: command-line arguments
  -> core.py: domain/stack selection and local CSV retrieval using BM25
  -> design_system.py: combined recommendations and formatted output
  -> reasoning_contract.py: parsing and application of structured rules
  -> optional explicit persistence of design documentation
```

Sources: [CLI](./.agents/skills/ui-ux-pro-max/scripts/search.py), [search engine](./.agents/skills/ui-ux-pro-max/scripts/core.py), [generator](./.agents/skills/ui-ux-pro-max/scripts/design_system.py), and [reasoning rules](./.agents/skills/ui-ux-pro-max/scripts/reasoning_contract.py).

This is local retrieval and rule-based generation. The inspected search/generator path does not call an LLM API. The assistant interprets its results and applies them to the project.

Actual local CSV counts were checked against the [catalog summary](./.agents/skills/ui-ux-pro-max/data/catalog-summary.json):

| Resource | Local count |
| --- | --- |
| Style rows | 88 total: 50 active, 29 supplemental, 9 deprecated |
| Searchable styles | 79 active/supplemental rows |
| Product profiles / palettes / reasoning profiles | 192 each |
| Font pairings / Google font rows | 74 / 1,934 |
| Curated icons | 105 |
| UX guidelines | 119 |
| Motion presets / chart types | 17 / 25 |
| Stack CSV files | 22 |

The summary records a verification date of 26 August 2026. Catalog presence and row counts do not independently establish that every recommendation or framework version is current today.

### Correct use in this project

- Use the existing [design direction](./DESIGN.md) as project context.
- Use a focused `--domain` query for one UX outcome, or an explicit `--stack vue` / `--stack laravel` query for implementation guidance.
- Inspect result relevance. A broad Vue query for `component props reactivity` returned testing advice; narrowing to `defineProps` returned the relevant props rule. A successful process exit does not prove the top result fits the question.
- Use `--design-system` when a new visual system is requested. Passing `--stack` in that mode prints a warning and ignores the stack; run a separate stack query.
- Optional persistence uses `--persist --output-dir <project-root>` and creates a project-specific master with page overrides. Existing files are preserved by default. The instructions require explicit authorization before using `--force` to overwrite them.
- The installed instructions contain a `CLAUDE_PLUGIN_ROOT` command template. In this checkout, use the actual local [search script](./.agents/skills/ui-ux-pro-max/scripts/search.py) path rather than assuming that environment variable is set.
- Neither skill is automatically imported by the Vue frontend. The `.use(plugin)` call in the [frontend entry](./resources/js/app.js) installs Inertia's Vue plugin, not an AI integration.
- The portfolio's Claude, ChatGPT, Gemini, NotebookLM, Hugging Face, GPTZero, n8n, and Zapier listings are [tool catalog content](./database/seeders/PortfolioToolSeeder.php), not implementations of those products or proof of custom plugin authorship.
- The `/llms.txt` endpoint is machine-readable discovery text. It does not execute an AI agent or call a model.

## Verification performed and limits

- Read the public/admin route structure, key controllers, models, supporting services, configuration, seed content, dependency manifests, selected tests, and local skill sources.
- Opened the local SQLite database with `mode=ro`; compared identity, education, work history, projects, publications, and achievements with the seed data.
- Confirmed local counts of 30 badges, 51 certificates, 37 portfolio tools, six projects, three publication records, and four work records. These are snapshots, not production counts.
- Checked local AI catalog row counts directly.
- Successfully ran non-persisting skill searches for `keyboard focus modal` in the UX domain and `defineProps` in the Vue stack. The initial broader Vue search was also inspected and narrowed for relevance.
- The app's [test configuration](./phpunit.xml) declares in-memory SQLite and a separate test configuration cache path, and a [safety test](./tests/Feature/TestEnvironmentSafetyTest.php) asserts isolation. The application test suite was not run for these documentation-only changes; existing test files are not evidence of a passing run.
- Did not run seeders, migrations, synchronization jobs, deployments, or external credential verification. Did not alter application behavior or inspect secrets to create these documents.
