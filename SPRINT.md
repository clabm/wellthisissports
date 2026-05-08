# SPRINT.md — Well This Is Sports
## Sprint 1 — Foundation

> Live working surface. Updated every session. Current as of May 2026.

---

## Sprint Goal

Get the foundation in place for a World Cup 2026 soft launch on June 11. Two parallel tracks this sprint: WordPress theme scaffold and design token definition. Pipeline work begins Sprint 2 once the sports data API is selected and Cloudways/GitHub are provisioned.

---

## Director Todos — Blocking Sprint Progress

These must be completed before Sprint 2 can begin. Nothing below is blocked on these for Sprint 1 except where noted.

| Todo | Blocks | Status |
|---|---|---|
| Create Cloudways app for wellthisiissports.com | Shop Hand deploy config, CLAUDE.md server details | Done, App ID 6404529 |
| Create GitHub repo: `clabm/wellthisissports` | Shop Hand scaffold commit | Done |
| Add GitHub Secrets to new repo | Pipeline work, deploy workflow | Not started |

Once Cloudways app is created, update CLAUDE.md with:
- Cloudways server ID — 1609033
- Cloudways app ID — 6404529
- App user — djxudszeqq
- Theme path — /home/master/applications/djxudszeqq/public_html/wp-content/themes/wellthisissports-child

---

## Sprint 1 Tasks

### Track A: WordPress Theme Scaffold
**Owner:** The Shop Hand  
**Trigger:** GitHub repo exists  
**Status:** Complete — committed c7e4d4f — May 8, 2026

| Task | Detail | Status |
|---|---|---|
| Fork WTIN repo structure into new repo | Repo root = child theme root, rename all files and slugs | Done |
| Rename all prefixes `wtin_` to `wtis_` | functions.php, pipeline-api.php, custom-fields.php, style.css | Done |
| Strip perspective meta fields | Remove left/right/neutral fields, replace with prediction fields per CLAUDE.md | Done |
| Stub new REST endpoint | `/wp-json/wtis/v1/matchups` — POST/PATCH/image/status/result + status + ledger | Done |
| Replace toggle.js with prediction.js stub | IIFE with mobile nav, newsletter, share — confidence meter ready for Sprint 2 | Done |
| Update Sass structure | `_story.scss` → `_matchup.scss`, full prediction scaffold, updated style.scss imports | Done |
| Update .lando.yml | wellthiissports project, wellthisiissports.lndo.site proxy, port 3308 | Done |
| Update .env.lando.example | Renamed WTIN vars to WTIS throughout | Done |
| Add deploy.yml workflow | Copied from WTIN, deploy-path: wellthiissports-child, WTIS Cloudways app paths | Done |
| Verify lando start works | Pending — Director runs `lando start` locally to confirm theme activates | Pending Director |
| Update CLAUDE.md with server details | Server details already present in CLAUDE.md | Done |

---

### Track B: Design Token Definition
**Owner:** The Architect + Director  
**Trigger:** None, can start immediately  
**Status:** Ready

| Task | Detail | Status |
|---|---|---|
| Architect proposes color palette | Bold, colorful, not generic sports red/blue, works in light and dark mode | Ready |
| Architect proposes typography stack | Display font, body font, UI font, referencing The Ringer/Vox/The18 direction | Ready |
| Architect proposes spacing and scale | Base unit, type scale, breakpoints | Ready |
| Director reviews and approves token set | One focused session, decisions locked | Pending Architect proposal |
| Lock tokens into CLAUDE.md | Design tokens table updated with approved values | Pending approval |
| Create `_tokens.scss` with approved values | CSS custom properties and Sass variables | Pending approval |
| Brief Cursor for theme build | Reference sites plus token brief, scoped to Sprint 2 front-end tasks | Pending tokens |

---

### Track C: Sports Data API Research
**Owner:** The Scout  
**Trigger:** None, can start immediately  
**Status:** Ready

| Task | Detail | Status |
|---|---|---|
| Research World Cup 2026 data API options | ESPN API, The Odds API, SportsRadar, API-Football, others | Ready |
| Evaluate each on: fixture data, team data, live results, free vs paid tier, rate limits | Produce comparison brief for Director decision | Ready |
| Identify which APIs cover post-game results | Required for ledger update pipeline | Ready |
| Deliver research brief to Architect | Structured comparison, recommendation included | Ready |
| Director selects API | Unlocks Pipeline Stage 1 work in Sprint 2 | Pending research brief |

**Scout prompt to use:**
> Research the best sports data APIs for World Cup 2026 coverage. I need: upcoming fixture data, team and player data, live match results, and post-game final scores. Evaluate ESPN API, The Odds API, SportsRadar, API-Football, and any others worth considering. Compare on data coverage, free vs paid tiers, rate limits, and ease of integration with a Python pipeline. Produce a structured comparison with a recommendation.

---

## Sprint 1 Definition of Done

- [ ] GitHub repo exists at `clabm/wellthisiissports`
- [ ] Cloudways app provisioned, IDs in CLAUDE.md
- [ ] Theme scaffold committed, lando start works, child theme activates
- [ ] Design tokens approved by Director and locked in CLAUDE.md and `_tokens.scss`
- [ ] Sports data API selected by Director
- [ ] SPRINT.md updated with Sprint 2 tasks

---

## Sprint 2 Preview (Do Not Start Yet)

Once Sprint 1 is done:

- Cursor builds front-end from token brief, matchup grid homepage, prediction detail page, confidence meter component, ledger widget
- Shop Hand builds Pipeline Stage 1, ingest.py, sports data API integration, matchup extraction, dedup
- Shop Hand builds Pipeline Stage 2, predict.py, Claude Haiku prediction prompt, confidence scoring, image brief
- Shop Hand builds Pipeline Stage 3, publish.py, WordPress REST API integration, OpenAI hero image
- Shop Hand stubs ledger update endpoint and post-game flow

---

## Blockers and Decisions Needed

| Blocker | Owner | Notes |
|---|---|---|
| GitHub Secrets not added | Director | Blocks pipeline and deploy workflow |
| Sports data API not selected | Director, after Scout research | Blocks all pipeline work |
| Design tokens not approved | Director | Blocks all Cursor front-end work |

---

## Session Log

| Date | Session Summary | Next Session |
|---|---|---|
| May 2026 | Project scoped, WTIS overview, CLAUDE.md, AGENT.md, SPRINT.md produced | Director completes Cloudways and GitHub todos, Architect proposes design tokens, Scout researches APIs |
| May 8, 2026 | Track A: WordPress theme scaffold complete — 29 files committed (c7e4d4f). All 10 tasks done. Pending: Director runs `lando start` to verify theme activates. | Director approves tokens (Track B), Scout delivers API research (Track C), Director adds GitHub Secrets |

---

*Well This Is Sports — SPRINT.md — Sprint 1*
*Updated every session by The Architect. Owned by The Shop Hand during execution.*
