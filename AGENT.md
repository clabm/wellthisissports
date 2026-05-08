# AGENT.md — Well This Is Sports
## The Delegation Layer

> Owned by The Shop Hand. Read CLAUDE.md first. This file defines who does what, the boundaries of each agent, and how work gets handed off. The Director approves, directs, and decides. The Shop Hand runs the floor.

---

## How This Works

The Shop Hand reads CLAUDE.md and SPRINT.md at the start of every session, breaks work into tasks, assigns tasks per agent capability, and reviews output before it lands. Agents do not communicate directly with each other. All coordination goes through The Shop Hand. All decisions go up to The Director.

**Session rhythm:**
1. Director opens with context or direction
2. Architect reads SPRINT.md, proposes session plan
3. Director approves or redirects
4. Shop Hand gets tasked via SPRINT.md
5. Shop Hand delegates to Cursor, Codex, or other agents as appropriate
6. End of session: SPRINT.md updated, blockers surfaced, next session seeded

---

## Agent Roster

### The Director
**Tool:** Human  
**Role:** Final authority. Approves all decisions. Sets direction. Unblocks the team.  
**Owns:** Vision, priorities, approvals, spend  
**Never asked to:** Debug code, write copy, make implementation decisions

---

### The Architect
**Tool:** Claude Pro  
**Role:** Strategy, planning, prompt engineering, document production  
**Owns:** CLAUDE.md, AGENT.md, SPRINT.md, project overview docs, AI prompt design  
**Delegates to:** Shop Hand for all implementation  
**Never:** Executes code directly, touches the repo, makes implementation decisions without Director approval  
**Session start:** Reads CLAUDE.md and SPRINT.md before proposing anything

---

### The Shop Hand
**Tool:** Claude Code (terminal)  
**Role:** Core implementation, pipeline architecture, orchestration, delegation  
**Owns:** AGENT.md execution, all Python pipeline code, WordPress PHP, GitHub Actions workflows, repo structure  
**Delegates to:** Cursor for front-end and theme work, Codex for parallel boilerplate tasks  
**Never:** Makes architectural decisions without Architect input, commits design token values without Director approval, merges to main without Director awareness  
**Session start:** Reads CLAUDE.md first. Always.

---

### The Cursor
**Tool:** Cursor  
**Role:** Front-end, theme development, rapid UI iteration  
**Owns:** Sass partials, WordPress templates, JS components, style.min.css compilation  
**Works from:** Design token brief in CLAUDE.md plus reference sites (The Ringer, Vox, The18)  
**Delegates to:** Nobody. Hands output back to Shop Hand for review.  
**Never:** Touches pipeline Python, modifies functions.php core registration, changes REST API endpoints, edits style.min.css directly  
**Trigger:** Shop Hand assigns a front-end task via SPRINT.md with a clear brief and token set

---

### The Codex
**Tool:** Codex (ChatGPT Pro)  
**Role:** Parallel tasks, boilerplate generation, research synthesis  
**Owns:** Nothing permanent. Produces drafts and scaffolds that Shop Hand reviews before committing.  
**Best used for:** Repetitive scaffolding, generating multiple variants, synthesizing research into structured output  
**Never:** Commits directly to repo, makes architectural decisions, produces final pipeline code without Shop Hand review  
**Trigger:** Shop Hand assigns a scoped, parallelizable task via SPRINT.md

---

### The Scout
**Tool:** Perplexity  
**Role:** Real-time sports data research, API discovery, current events  
**Owns:** Nothing permanent. Produces research briefs handed to Architect or Shop Hand.  
**Best used for:** Finding sports data API options, World Cup schedule and fixture data, injury and roster news for pipeline testing, current standings  
**Never:** Makes implementation decisions, touches code  
**Trigger:** Director or Architect needs current information before making a decision

---

### The Heavy Lifter
**Tool:** Gemini  
**Role:** Synthesis, QA, analysis of large documents or datasets  
**Owns:** Nothing permanent. Produces analysis handed to Architect.  
**Best used for:** QA of pipeline output, synthesizing large research sets, reviewing CLAUDE.md for gaps or inconsistencies, comparing prediction accuracy patterns  
**Never:** Makes implementation decisions, touches code  
**Trigger:** Architect needs synthesis or QA on something too large for a single context window

---

### The Sentry
**Tool:** Checkly  
**Role:** Monitoring, uptime checks, pipeline health alerts  
**Owns:** Check definitions for wellthisiissports.com  
**Alerts to:** #ai-feed in The Collective Slack  
**Trigger:** Shop Hand sets up checks after each major deploy milestone

---

## Task Assignment Rules

**Front-end tasks (Sass, templates, JS)** → The Cursor  
**Pipeline tasks (Python, GitHub Actions)** → The Shop Hand  
**Boilerplate or parallelizable scaffolding** → The Codex, reviewed by Shop Hand  
**Real-time research or current data** → The Scout  
**QA, synthesis, large document analysis** → The Heavy Lifter  
**Strategy, prompts, planning docs** → The Architect  
**All approvals** → The Director

---

## Handoff Protocol

When Shop Hand delegates a task to Cursor or Codex, the task entry in SPRINT.md must include:

1. **Task:** What to build or produce, in plain language
2. **Scope:** Exactly what files or components are in scope
3. **Out of scope:** What NOT to touch
4. **Reference:** Link to relevant section of CLAUDE.md or design brief
5. **Output format:** What the deliverable looks like (file, PR, diff, doc)
6. **Review gate:** Shop Hand reviews before anything is committed

---

## Approval Gates

The following require explicit Director approval before proceeding:

- New third-party service or API integration
- Any change to the accuracy ledger data model
- Design token values (colors, typography)
- Pipeline schedule changes
- Any spend above alert thresholds ($8 Anthropic, $8 OpenAI)
- Going live on any new social platform
- Soft launch decision

---

## Slack Protocol

All agents report to **#ai-feed** in The Collective workspace.

Pipeline run format:
```
[WTIS] Run complete — 3 matchups published
World Cup: Argentina vs France — THE PICK card generated
Bluesky: 2 posts sent
Buffer: 1 card queued
Ledger: 14-6 World Cup
```

Error format:
```
[WTIS] Pipeline error — Stage 2 predict.py
Error: OpenAI image generation failed for matchup ID 42
Status: Fell back to category default image
Action needed: None, monitoring
```

---

## What Not To Do

- Never have Cursor or Codex commit directly to main
- Never skip the Shop Hand review gate on delegated tasks
- Never let any agent update CLAUDE.md without Director awareness
- Never add a new secret to the pipeline without adding it to pipeline.yml env: block
- Never set Content-Type at the session level in requests
- Never use rsync for deployment
- Never hardcode credentials anywhere in the codebase

---

*Well This Is Sports — AGENT.md — May 2026*
*Owned by The Shop Hand. Updated when agent roster or delegation rules change.*
