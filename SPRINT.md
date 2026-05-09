# SPRINT.md — Well This Is Sports
## Sprint 3 — Pre-Launch

> Live working surface. Updated every session. Current as of May 2026.

---

## Sprint Goal

Get the platform launch-ready for World Cup Day 1 (June 11, 2026). Social accounts connected, monitoring live, API-Football on paid tier, domain pointed, first real content published and reviewed by Director.

---

## Sprint 2 — Closed

All Sprint 2 tasks complete. Summary:

| Track | Outcome |
|---|---|
| Track A: Front-End | Done — full theme built by Cursor, reviewed and live |
| Track B: Pipeline | Done — ingest, predict, publish, card stubs, run.py, pipeline.yml all built and verified |
| Track C: WordPress Setup | Done — theme deployed via GitHub Actions, REST API endpoints live, Application Password configured |
| End-to-end test | Done — test_publish.py and test_predict.py both passing |
| deploy.yml | Rewritten — SSH clone + rsync pattern, fully reliable |

**Carried forward to Director QA:** Content quality review — Director should review a full pipeline run output (analysis depth, prediction rationale, image quality) before Sprint 3 distribution work begins.

---

## Sprint 3 Tasks

### Track A: Social Accounts and Distribution
**Owner:** Director + Shop Hand
**Status:** Ready

| Task | Detail | Status |
|---|---|---|
| Create Facebook Page for WTIS | Page name, bio, profile image | Ready |
| Create Bluesky account for WTIS | Handle, bio, profile image | Ready |
| Create Mailchimp audience for WTIS | Audience name, from address, welcome email | Ready |
| Create Slack channel for WTIS in The Collective | Get channel ID for pipeline logging | Ready |
| Connect Facebook Page to Buffer | Get Buffer channel ID | Ready |
| Add all social secrets to GitHub | BLUESKY_HANDLE, BLUESKY_APP_PASSWORD, BUFFER_ACCESS_TOKEN, BUFFER_CHANNEL_ID, MAILCHIMP_API_KEY, MAILCHIMP_AUDIENCE_ID, MAILCHIMP_DC, SLACK_BOT_TOKEN, SLACK_CARD_CHANNEL, SLACK_CARD_CHANNEL_ID | Ready |
| Enable social distribution in pipeline | Activate Bluesky, Mailchimp, Buffer, Slack — remove stubs | Ready |
| Test social distribution end-to-end | Full pipeline run with real social accounts, verify posts appear | Ready |

---

### Track B: API-Football Upgrade and Season Switch
**Owner:** Director + Shop Hand
**Status:** Ready

| Task | Detail | Status |
|---|---|---|
| Upgrade API-Football to paid tier | $19/month, 7,500 requests/day — evaluate vs free tier usage data from testing | Ready |
| Set APIFOOTBALL_SEASON=2026 in GitHub Secret | Switches pipeline from 2024 test data to live World Cup 2026 fixtures | Ready |
| Run ingest.py against 2026 season | Verify World Cup 2026 fixtures return correctly | Ready |
| Full pipeline run against 2026 data | Ingest → predict → publish with real World Cup matchups | Ready |

---

### Track C: Monitoring
**Owner:** Shop Hand
**Status:** Ready

| Task | Detail | Status |
|---|---|---|
| Set up Checkly monitoring | Monitor: REST API status endpoint, homepage load, matchup post creation | Ready |
| Set up Checkly alert routing | Route alerts to #ai-feed Slack or email | Ready |
| Set pipeline.yml schedule | Define cadence around World Cup match schedule — run day before each match | Ready |

---

### Track D: Content and Domain
**Owner:** Director + Shop Hand
**Status:** Ready

| Task | Detail | Status |
|---|---|---|
| Director QA of pipeline content quality | Review full prediction output: analysis depth, confidence rationale, image quality | Ready |
| World Cup group stage preview article | Published before June 11 soft launch | Ready |
| Verify ledger widget live on homepage | Accuracy ledger displaying correctly with real data | Ready |
| Point wellthisiissports.com to Cloudways app | DNS cutover from temp URL | Ready |
| Update WTIS_SITE_URL in GitHub Secret | Switch from Cloudways temp URL to wellthisiissports.com | Ready |
| Update WTIS_SITE_URL in pipeline/.env.lando | Local dev also points to live domain | Ready |
| SSL verify after domain cutover | Confirm HTTPS works on wellthisiissports.com | Ready |
| Soft launch June 11 | World Cup Day 1 | Ready |

---

## Sprint 3 Definition of Done

- [ ] All social accounts created and connected
- [ ] Full pipeline run end-to-end with social distribution firing
- [ ] API-Football on 2026 season, live World Cup fixtures ingesting
- [ ] Checkly monitoring live with alerts configured
- [ ] pipeline.yml on schedule matching World Cup match cadence
- [ ] Director has QA'd content quality and approved
- [ ] World Cup group stage preview published
- [ ] Domain pointed, SSL verified, temp URL retired
- [ ] Soft launch June 11 with at least one real matchup prediction live

---

## Blockers and Decisions Needed

| Blocker | Owner | Notes |
|---|---|---|
| Social account setup | Director | Facebook, Bluesky, Mailchimp, Slack all need Director to create before Shop Hand can wire up |
| API-Football paid tier decision | Director | Evaluate free tier usage logs from testing, confirm $19/month before upgrading |
| Pipeline schedule | Shop Hand + Director | Set pipeline.yml cron once World Cup group stage schedule is confirmed |
| Director content QA | Director | Must happen before social distribution is enabled |

---

## Session Log

| Date | Session Summary | Next Session |
|---|---|---|
| May 2026 | Project scoped, WTIS overview, CLAUDE.md, AGENT.md, SPRINT.md produced | Director completes Cloudways and GitHub todos, Architect proposes design tokens, Scout researches APIs |
| May 2026 | Sprint 1 closed. Tokens approved, API selected, scaffold verified, core secrets in | Sprint 2 kickoff, Cursor on front-end, Shop Hand on pipeline, Director on server setup |
| May 2026 | Sprint 2 closed. Full pipeline built and verified, deploy.yml fixed, test suite passing | Sprint 3: social setup, API-Football upgrade, monitoring, domain cutover, soft launch June 11 |

---

*Well This Is Sports — SPRINT.md — Sprint 3*
*Updated every session by The Architect. Owned by The Shop Hand during execution.*
