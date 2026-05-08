# SPRINT.md — Well This Is Sports
## Sprint 2 — Build

> Live working surface. Updated every session. Current as of May 2026.

---

## Sprint Goal

Build the core product. Two parallel tracks: Cursor builds the front-end from approved design tokens, Shop Hand builds the pipeline against API-Football and WordPress. Target: working prediction pipeline publishing World Cup matchups before June 11 launch.

---

## Sprint 1 — Closed

All Sprint 1 tasks complete. Summary:

| Track | Outcome |
|---|---|
| Track A: WordPress scaffold | Done, lando verified, child theme active |
| Track B: Design tokens | Done, approved and locked in CLAUDE.md and `_tokens.scss` |
| Track C: Sports API research | Done, API-Football selected as primary, football-data.org as fallback |
| GitHub repo | `clabm/wellthisissports`, SSH configured |
| Cloudways app | App ID 6404529, Server ID 1609033, App user djxudszeqq |
| Core GitHub Secrets | 10 secrets in, social secrets deferred to pre-launch |

**Social accounts deferred:** Facebook, Buffer, Mailchimp, Bluesky, Slack channel — set up at pre-launch, not blocking build.

---

## Sprint 2 Tasks

### Track A: Front-End
**Owner:** The Cursor
**Trigger:** Ready to start, tokens locked, scaffold in place
**Status:** Ready

| Task | Detail | Status |
|---|---|---|
| Update functions.php | Replace Oswald Google Font reference with Barlow Condensed + Barlow + Inter | Ready |
| Build `_masthead.scss` | Site wordmark, nav, sports identity header | Ready |
| Build `_homepage.scss` | Matchup grid layout, sport section blocks, ledger widget placement | Ready |
| Build `_matchup.scss` | Prediction detail page, confidence meter component, team breakdown layout | Ready |
| Build `_takeaways.scss` | Key factors component, factors for/against display | Ready |
| Build `_footer.scss` | Footer styles, social links, newsletter signup | Ready |
| Build `_mobile.scss` | Responsive breakpoints for all components | Ready |
| Update front-page.php | Matchup grid homepage, ledger widget, sport sections | Ready |
| Update single.php | Prediction detail page, confidence meter, team breakdown, key factors | Ready |
| Update archive.php | Sport and league archive pages | Ready |
| Build prediction.js | Confidence meter animation, prediction reveal component, IIFE pattern | Ready |
| Compile and verify | `lando sass`, confirm no errors, review in browser | Ready |

**Cursor brief:**
- Visual references: The Ringer, Vox, The18
- Token file: `sass/_tokens.scss`
- Direction: Bold, colorful, sports energy, white default, dark mode via OS setting
- Hero UI element: Confidence meter (gold, large Barlow Condensed number)
- Ledger: prominent on homepage and archive, green for correct, red for incorrect
- Card-forward layout with color-blocked sections
- No jQuery, vanilla JS only, IIFE pattern
- Never edit `css/style.min.css` directly

---

### Track B: Pipeline
**Owner:** The Shop Hand
**Trigger:** Ready to start
**Status:** Ready

| Task | Detail | Status |
|---|---|---|
| Set up pipeline/ directory structure | Mirrors WTIN: ingest.py, predict.py, publish.py, card.py, run.py | Ready |
| Create .env.lando with all current secrets | Local dev pipeline config | Ready |
| Build ingest.py | API-Football integration, World Cup fixture fetch, matchup extraction, dedup, candidates.json | Ready |
| Test ingest.py locally | Confirm World Cup fixtures returned, candidates.json populated | Ready |
| Build predict.py | Claude Haiku prediction prompt, winner + confidence score, key factors, image brief, framed.json | Ready |
| Test predict.py locally | Confirm prediction output matches schema, quality check | Ready |
| Build publish.py | WordPress REST API, matchup post creation, OpenAI hero image, ledger logging | Ready |
| Test publish.py locally | Confirm matchup posts appear in local WP, meta fields populated | Ready |
| Build card.py stub | Score gate, card type selection, Slack + Buffer hooks stubbed for post-launch | Ready |
| Build run.py | Orchestrates all stages in sequence, error handling, logging | Ready |
| Build pipeline.yml | GitHub Actions workflow, schedule TBD, all secrets mapped in env: block | Ready |
| End-to-end local test | Run full pipeline locally against lando WP, verify matchup published correctly | Ready |

**Pipeline rules inherited from WTIN:**
- Never set Content-Type at session level, use json= kwarg per request
- Always request date_gmt not date from WP REST API
- Always append UTM URL explicitly, never rely on LLM to embed it
- OpenAI gpt-image-1 returns base64, decode and save before doing anything else
- Every secret must be in pipeline.yml env: block explicitly
- Test with a local script before any live pipeline run

---

### Track C: WordPress Setup on Server
**Owner:** Director
**Trigger:** Ready to start
**Status:** Ready

| Task | Detail | Status |
|---|---|---|
| Point wellthisiissports.com domain to Cloudways app | Cloudways, App, Domain Management | Ready |
| Verify WordPress is installed and accessible | Visit temporary Cloudways URL | Ready |
| Install Understrap parent theme on server | Via WP admin or WP-CLI | Ready |
| Deploy child theme via GitHub Actions | Push to main, verify deploy.yml fires | Ready |
| Activate wellthisissports-child theme | WP admin, Appearance, Themes | Ready |
| Set WTIS pipeline API key in WP options | WP admin or WP-CLI | Ready |
| Verify REST API endpoint responds | GET /wp-json/wtis/v1/status | Ready |

---

## Sprint 2 Definition of Done

- [ ] Front-end built, reviewed in browser, approved by Director
- [ ] Pipeline runs end-to-end locally, matchup published to local WP
- [ ] WordPress live on server, theme deployed and active
- [ ] Pipeline runs against production WP, first real matchup published
- [ ] deploy.yml verified, push to main deploys theme correctly
- [ ] SPRINT.md updated with Sprint 3 tasks

---

## Blockers and Decisions Needed

| Blocker | Owner | Notes |
|---|---|---|
| Social secrets | Director | Deferred to pre-launch, not blocking build |
| Pipeline schedule | Shop Hand + Director | Decide cadence once pipeline is tested |
| API-Football paid tier decision | Director | Evaluate before World Cup launch based on request volume |

---

## Pre-Launch Checklist (Sprint 3 Preview)

- [ ] Create Facebook Page for WTIS
- [ ] Connect to Buffer, get channel ID
- [ ] Create Mailchimp audience for WTIS
- [ ] Create Bluesky account for WTIS
- [ ] Create Slack channel for WTIS, get channel ID
- [ ] Add all social secrets to GitHub
- [ ] Enable social distribution in pipeline
- [ ] Set up Checkly monitoring
- [ ] Evaluate API-Football paid tier upgrade
- [ ] World Cup group stage preview article published
- [ ] Ledger widget live on homepage
- [ ] Soft launch June 11, World Cup Day 1

---

## Session Log

| Date | Session Summary | Next Session |
|---|---|---|
| May 2026 | Project scoped, WTIS overview, CLAUDE.md, AGENT.md, SPRINT.md produced | Director completes Cloudways and GitHub todos, Architect proposes design tokens, Scout researches APIs |
| May 2026 | Sprint 1 closed. Tokens approved, API selected, scaffold verified, core secrets in | Sprint 2 kickoff, Cursor on front-end, Shop Hand on pipeline, Director on server setup |

---

*Well This Is Sports — SPRINT.md — Sprint 2*
*Updated every session by The Architect. Owned by The Shop Hand during execution.*
