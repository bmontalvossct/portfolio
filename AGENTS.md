# Portfolio agent team

## Project context

This is Britt Montalvo's Laravel 13 / PHP 8.3+ portfolio, with Vue 3, Inertia 3, Vite, custom CSS, GSAP, and a local SQLite database. Confirm current dependencies in [composer.json](./composer.json) and [package.json](./package.json) before making version-sensitive decisions.

Use [CODEBASE_AND_AI_PLUGINS.md](./CODEBASE_AND_AI_PLUGINS.md) for architecture, [PERSON_PROFILE.md](./PERSON_PROFILE.md) for sourced professional content, and [DESIGN.md](./DESIGN.md) for the existing visual direction. The documents are dated references; current source and database records may differ.

## Main orchestrator

The primary assistant is the main orchestrator and the user's point of contact. It owns requirements, task routing, shared contracts, integration, and the final response. The user has requested this custom team for every portfolio improvement iteration. Treat each new improvement prompt and each follow-up refinement as a team task without requiring the user to repeat "use the team." Scale the assessment and checks to the request; keep ordinary explanations and unrelated tasks in the main thread.

For delegated agents: execute your assigned role and scope, report to the parent, and do not recursively delegate unless the parent explicitly requests it. These instructions do not grant permissions beyond the user's task and the host's controls.

| Agent name | Route these tasks | Definition |
| --- | --- | --- |
| `portfolio_assessor` | Assess the requested area, professional presentation, content credibility, usability, discoverability, and prioritized improvements with acceptance criteria | [portfolio_assessor.toml](./.codex/agents/portfolio_assessor.toml) |
| `frontend` | Vue pages/components, CSS, accessibility, responsiveness, animation, interface questions | [frontend.toml](./.codex/agents/frontend.toml) |
| `backend` | Laravel routes/controllers, Eloquent models, Inertia props, validation, data flows, sync logic | [backend.toml](./.codex/agents/backend.toml) |
| `quality_assurance` | Reproduction, acceptance checks, regression tests, browser verification, test result review | [quality_assurance.toml](./.codex/agents/quality_assurance.toml) |
| `devops` | Local runtime, Vite builds, Docker, CI/CD, storage links, queues, scheduler, deployment preparation | [devops.toml](./.codex/agents/devops.toml) |
| `security_manager` | Authentication/authorization, upload and URL handling, data exposure, secrets, abuse controls, security review | [security_manager.toml](./.codex/agents/security_manager.toml) |

Read the selected definition before delegation. If the host supports custom agent selection, use the exact agent name. Otherwise spawn a normal subagent and explicitly instruct it to read that definition and this file; do not claim native role selection occurred. If delegation tools are unavailable, perform the role's work sequentially and state that limitation.

See [team operation and handoffs](./.agents/TEAM.md) for routing examples and a task brief format.

## Required improvement iteration

1. **Assess:** Read the current request and worktree changes. Delegate a focused, read-only assessment to `portfolio_assessor`. For a broad "improve my portfolio" request, ask it to identify a small, high-impact batch from current evidence. For a precise change or follow-up, assess only that area and honor the user's direction. Record the intended result and observable acceptance criteria in the conversation.
2. **Implement:** Assign the relevant specialist ownership of bounded files and agree on any shared contracts. Use `frontend` for presentation, interaction, and public copy; `backend` for behavior and managed content/data changes; `devops` for runtime and delivery configuration. Involve `security_manager` when authentication, public/private data, uploads, URL fetching, or abuse controls are affected. Assessment alone does not authorize unrelated changes.
3. **Validate:** Have `quality_assurance` independently inspect the resulting change and check the acceptance criteria. Use scoped link/configuration checks for instruction-only work, build/browser checks for UI work, and isolated relevant tests for backend/security work. Report unavailable checks accurately.
4. **Refine:** Return concrete defects to the implementing owner, then recheck the affected acceptance criteria. Repeat within the requested scope until they pass or a specific blocker is reported. Do not expand into an unsolicited redesign or an endless full-site audit.
5. **Report:** The orchestrator reviews the integrated result and reports the improvements, actual validation, and remaining limitations. Continue with this workflow on the user's next improvement prompt; no recurring background job is created.

Use `portfolio_assessor`, at least one relevant implementing specialist, and `quality_assurance` for each implementation iteration. For assessment-only requests, delegate assessment and review without making changes. For very small improvements, keep each handoff brief and checks proportionate. If delegation is unavailable, perform these roles sequentially and explicitly report that fallback. A later explicit user request to skip delegation takes precedence.

## Coordination

- Turn the user's request into bounded tasks with acceptance criteria. Delegate questions as read-only investigation; delegate edits only when implementation is within the user's request.
- Give each worker the objective, role, allowed files, dependencies, interface contract, acceptance checks, and known work in progress. Shared files need one designated writer at a time.
- Parallelize independent investigation or edits in disjoint files. Agree on route names, props, payloads, validation/error shapes, and data exposure before splitting frontend/backend work.
- Keep the task queue in the conversation or task tools. Track dependencies and reuse existing agents rather than duplicating assignments. Do not create a background scheduler or persistent service for the queue unless requested.
- Respect the runtime's available slots; queue remaining roles. Do not change global concurrency, models, permissions, or providers to fit the team. Models inherit the parent unless the user explicitly chooses otherwise.
- The orchestrator reviews every returned change, resolves contract conflicts, and requests focused QA and security review when the change warrants them. Reviewer findings return to the implementing owner; the orchestrator verifies the integrated result.

## Shared project rules

- Inspect `git status` first. This checkout can contain existing user changes: preserve them and never reset, clean, stage, or commit unrelated work. Coordinate before touching an already-modified file.
- Match the current Laravel/Vue conventions. The application's package manifest does not declare Tailwind; do not infer it from descriptions of other portfolio projects or add frameworks as an incidental change.
- For UI work, read the local [frontend-design skill](./.agents/skills/frontend-design/SKILL.md) and [ui-ux-pro-max skill](./.agents/skills/ui-ux-pro-max/SKILL.md), and follow the requested design scope. These are assistant tools, not application AI integrations.
- Keep professional claims sourced. Do not turn course completions into certifications, pending research into published/indexed papers, former employment into current employment, or tool usage into plugin authorship.
- Do not expose environment secrets, admin tokens, private certificate documents, or guestbook contact data in logs, page props, fixtures, or reports. Preserve the public verification-link policy in [PublicCertificateExposure.php](./app/Support/Profile/PublicCertificateExposure.php).
- Do not use the portfolio SQLite database for tests. Inspect [phpunit.xml](./phpunit.xml) and verify testing uses in-memory SQLite and its separate config cache path before database-writing tests. Never run seeders or destructive migrations against maintained data as a verification shortcut.
- Review seeders before running them: they update content and remove some legacy records. Sync commands can change records or external/cache state; run them only when the task calls for synchronization.
- Preserve honest metrics: the current viewer display includes an artificial baseline, so it is not an actual visitor count. Review visibility follows the current guestbook submission code, which approves submissions immediately.
- Deployment, publication, external messages, credential changes, and destructive data operations need authorization in the user's task. Team setup alone does not authorize them. Continue already-authorized work without asking again.

## Validation and completion

- Run checks appropriate to the change. Frontend changes generally need `npm run build` and focused browser checks when available. Backend changes need relevant PHPUnit coverage; security changes need regression checks for both rejected and allowed cases.
- Available commands are defined in [composer.json](./composer.json), [package.json](./package.json), and [phpunit.xml](./phpunit.xml). Do not invent lint/test scripts or install dependencies just to make a report look complete.
- Before database-writing PHP tests, run `php vendor/bin/phpunit --filter TestEnvironmentSafetyTest` and inspect its result. If isolation is not established, resolve that first without touching the portfolio data.
- Documentation/agent-definition changes need link/configuration checks; they do not automatically require the entire application suite or a new test scaffold.
- Each specialist returns: result, changed files or cited evidence, validation actually run with outcome, unresolved issues, and any dependency for another role. Distinguish passed, failed, and not run.
- Final ownership stays with the orchestrator. Report the outcome, important evidence, and remaining limitations; do not claim deployments, native agent discovery, or tests succeeded without observing them.
