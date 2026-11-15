# Portfolio team operation

The main assistant is the orchestrator. Six reusable specialists provide assessment, implementation, and review through the host's delegation tools. The repository-wide rules and routing table are in [AGENTS.md](../AGENTS.md). The user has requested this team on every portfolio improvement prompt and follow-up; no extra invocation phrase is required.

## Team and ownership

| Role | Responsibility | Typical files |
| --- | --- | --- |
| Main orchestrator | Understand the request, assign tasks, agree contracts, integrate changes, and report the verified result | Cross-cutting decisions and explicitly reserved shared files |
| [Portfolio assessor](../.codex/agents/portfolio_assessor.toml) | Read-only assessment, professional credibility, user journeys, evidence-backed priorities, and acceptance criteria | Requested public pages and their source; [professional context](../PERSON_PROFILE.md) and [design direction](../DESIGN.md) |
| [Frontend](../.codex/agents/frontend.toml) | Vue/Inertia interfaces, CSS, interaction, accessibility, responsiveness, motion | [frontend source](../resources/js/), [styles](../resources/css/) |
| [Backend](../.codex/agents/backend.toml) | Laravel behavior, data, validation, authorization implementation, route/prop contracts, sync logic | [application](../app/), [routes](../routes/), [database source](../database/), assigned [configuration](../config/) |
| [Quality assurance](../.codex/agents/quality_assurance.toml) | Reproduce issues, validate acceptance criteria, review regressions, own assigned tests | [tests](../tests/), browser evidence, scoped checks |
| [DevOps](../.codex/agents/devops.toml) | Runtime, builds, Docker, delivery configuration, workers, scheduler, deployment preparation | [Dockerfile](../Dockerfile), [Docker configuration](../docker/), assigned build/CI files |
| [Security manager](../.codex/agents/security_manager.toml) | Review relevant trust boundaries and evidence; recommend or implement assigned fixes | Read access to the affected flow; edits only to explicitly assigned files |

Ownership is assigned per task. A role's typical files do not authorize broad changes, and reviewers should not race implementation workers to patch the same file.

## Queue and orchestration

1. Identify the requested outcome and acceptance checks. For every improvement iteration, ask the portfolio assessor for a focused read-only assessment of the requested area. A small follow-up should receive a small assessment; ordinary explanations need no team cycle.
2. Assign the relevant implementing specialist and independent QA for each implementation iteration. Involve backend, DevOps, or security when their surfaces are affected; the whole roster need not run for every request. For assessment-only requests, keep the team read-only. Split features into bounded tasks after agreeing shared contracts.
3. Track task ID, role, objective, dependencies, allowed files, and status in the active conversation or available task tools. Useful statuses are `queued`, `running`, `waiting_on_dependency`, `review`, and `done`.
4. Start independent work within available host capacity. This session has four total concurrent slots, including the orchestrator; run at most three specialists together here. Other hosts may impose different limits.
5. Collect concise handoffs. Check each returned diff and contract, then route any defects to the owning implementer. QA/security review can run in parallel once the affected implementation is stable and files are not being changed under the checks.
6. Return concrete defects to the implementing owner and recheck the affected criteria after fixes. Integrate and verify the combined flow. Mark work done only when its acceptance checks are satisfied or its remaining limitations are explicitly reported. Do not grow the iteration into unrelated improvements.

The queue is a coordination convention inside agent work. These files do not create a daemon, recurring automation, or always-running background workers.

## Default improvement loop

```text
User improvement prompt or follow-up
  -> Orchestrator: scope and existing changes
  -> Portfolio assessor: evidence, priorities, acceptance criteria (read-only)
  -> Relevant specialist(s): scoped implementation with one owner per file
  -> QA: independent acceptance checks; security review when relevant
  -> Implementing owner: resolve concrete defects, then targeted recheck
  -> Orchestrator: integrated result, validation, and remaining limits
```

For a broad request, assess the public portfolio and implement a small coherent batch of justified improvements within the user's authorization. For a precise request, follow that direction and assess only what could affect its success. Do not require the user to approve routine implementation choices or repeat the team request. An explicit assessment-only request stays read-only, and an explicit request to skip delegation overrides this default for that task.

Use the current conversation and Git diff as the iteration record: capture the requested outcome, selected findings, owners, acceptance checks, actual results, and any deferred items. Before acting on a deferred finding in a later prompt, confirm it is still relevant to the current source and request. Do not create an unsolicited report, backlog file, or scheduled task for this record.

## Delegation brief

Send enough context for the agent to work without guessing:

```text
Task: short ID and objective
Role: exact agent name and definition path
Mode: investigate / implement / review / validate
Context: relevant user requirements, known findings, current worktree changes
Allowed files: explicit files or narrow directories; identify the designated writer
Contract: routes, request/response props, error shape, data exposure, or no interface change
Dependencies: what must finish first and which other agent owns it
Acceptance: observable success criteria and appropriate checks
Return: result, files/evidence, validation outcomes, unresolved issues, next owner
```

Before spawning, the orchestrator reads the role definition. Use native custom role selection where supported. With generic delegation tools, explicitly tell the worker to read the definition and [AGENTS.md](../AGENTS.md). Loading a TOML instruction file manually is a fallback, not proof of native auto-discovery.

## Routing examples

| User request | Suggested assignment |
| --- | --- |
| “Fix mobile layout and keyboard navigation.” | Frontend implements; QA validates relevant widths, focus, and interaction |
| “Add a filter to certifications.” | Backend and frontend agree query/prop contract, then work in disjoint files; QA checks results and empty states |
| “Why is the sync not updating?” | Backend investigates the sync/data path; DevOps checks scheduler/worker state if needed; avoid invoking live sync as an inspection shortcut |
| “Review the admin login security.” | Security manager performs scoped review; backend implements any requested fix; QA checks allowed/rejected access |
| “Prepare Docker deployment.” | DevOps prepares configuration; backend checks data/migration needs; QA validates applicable build/startup behavior; deployment follows existing task authorization |
| “What does this controller do?” | Backend returns a source-backed explanation; no edit assignment is needed |

These examples describe implementation ownership. Every improvement iteration also uses the portfolio assessor and independent QA; only the relevant specialists need to participate.

## Reuse and runtime limits

The project agent definitions use the standalone TOML format documented in [official OpenAI subagent documentation](https://learn.chatgpt.com/docs/agent-configuration/subagents). They provide names, routing descriptions, and developer instructions; model settings are omitted so they inherit the parent. [AGENTS.md](../AGENTS.md) supplies shared project policy and makes delegation intent explicit.

Native discovery depends on the installed client and project loading; it has not been asserted from file creation alone. In a new session, open this repository so [AGENTS.md](../AGENTS.md) supplies the standing team workflow. Use exact native role names if available; otherwise delegate to a normal subagent with the relevant definition explicitly included or read. Agent permissions remain those enforced by the parent runtime; a role name does not create a separate sandbox.

Example request:

> Improve the services page so the offerings and inquiry action are easier to understand.

This automatically routes to the portfolio assessor, frontend, and QA, with backend, security, or DevOps added only when needed. The main assistant coordinates the work and final response.

Only the role definitions under [.codex/agents](../.codex/agents/) are intended to be shared. Other local Codex configuration remains ignored by Git. No global user configuration, API keys, model choices, or recurring tasks are required by this setup.
