# CLAUDE.md — Well This Is Sports
## The Shared Brain

> Read this at the start of every session. Update it in the same commit as any structural change.

---

## What This Project Is

**Well This Is Sports** (`wellthisiissports.com`) is a fully automated AI-powered sports prediction platform. Every matchup gets a full analytical breakdown, a winner prediction with a confidence score, and a public accuracy ledger that tracks whether the AI was right.

This is the second property under the **Well This Is Media** umbrella (`wellthisismedia.com`). It runs the same multi-agent workflow as Well This Is News but with a fundamentally different engine. The product is the prediction, the confidence score, and the public ledger of whether the AI got it right.

---

## Property Context

| Property | URL | Engine | Status |
|---|---|---|---|
| Well This Is News | wellthisisnews.com | Perspective Engine | Live |
| Well This Is Sports | wellthisiissports.com | Predictive Engine | In Build |

---

## The Collective

| Agent | Tool | Role |
|---|---|---|
| The Architect | Claude Pro | Strategy, decisions, prompts, planning |
| The Shop Hand | Claude Code | Core implementation, orchestration, AGENT.md ownership |
| The Cursor | Cursor | Front-end, theme, rapid iteration |
| The Codex | Codex | Parallel tasks, boilerplate |
| The Scout | Perplexity | Real-time sports data research |
| The Heavy Lifter | Gemini | Synthesis, QA, analysis |
| The Sentry | Checkly | Monitoring |

All agents report to #ai-feed in The Collective Slack workspace. The Director approves, directs, and decides. The Architect never executes code directly. All implementation goes through The Shop Hand.

**Three-file system:**
- `CLAUDE.md` — shared brain, architecture, decisions, constraints (this file)
- `AGENT.md` — delegation layer, owned by The Shop Hand
- `SPRINT.md` — live task board, updated every session

---

## Repo and Deployment

**GitHub repo:** `github.com/clabm/wellthisissports`

**Repo root IS the child theme.** No `wordpress/` subfolder. Everything in the repo root deploys directly into:
`public_html/wp-content/themes/wellthisiissports-child`

**Deploy flow:**
```
git push origin main
  → GitHub Actions (deploy.yml)
  → SSH into server
  → Clone repo to /tmp/wtis-deploy
  → Rsync theme files to theme directory
  → Live on server (~60 seconds)
```

Deploy uses direct SSH clone + rsync via GitHub Actions. On every push to main, the workflow:
1. SSHs into the server using CLOUDWAYS_SSH_PRIVATE_KEY
2. Clones the repo to /tmp/wtis-deploy on the server
3. Rsyncs theme files to the theme directory
4. Cleans up /tmp/wtis-deploy

SSH credentials required:
- CLOUDWAYS_HOST: 104.131.188.232
- CLOUDWAYS_USER: master_gxcccyduvu
- CLOUDWAYS_SSH_PRIVATE_KEY: deploy key ~/.ssh/wellthisissports_deploy

**Manual emergency deploy:** Cloudways dashboard → Application → Deployment via GIT → Pull.

**Server details:**
| Item | Value |
|---|---|
| Host | Cloudways Flexible (DigitalOcean) |
| Server | caleb-main, New York |
| Server IP | 104.131.188.232 (shared with WTIN) |
| Cloudways server ID | 1609033 |
| Cloudways app ID | 6404529 |
| App user | djxudszeqq |
| Theme path | /home/master/applications/djxudszeqq/public_html/wp-content/themes/wellthisissports-child |
| WP root | /home/master/applications/djxudszeqq/public_html |

---

## GitHub Actions Workflows

**deploy.yml** — triggers on push to main, deploys theme via Cloudways API.

**pipeline.yml** — triggers pipeline runs. Schedule TBD, driven by sports data cadence and World Cup match schedule.

**weekly_qa.yml** — Sunday QA run. Carry forward from WTIN pattern.

**Critical rule:** Every secret must be explicitly listed in the `env:` block of the workflow. Having a secret in repo settings is not enough. If a secret doesn't reach Python, check pipeline.yml first.

**GitHub Secrets required:**
```
CLOUDWAYS_EMAIL
CLOUDWAYS_API_KEY
CLOUDWAYS_SERVER_ID
CLOUDWAYS_APP_ID
APIFOOTBALL_SEASON
WTIS_SITE_URL
WTIS_API_URL
WTIS_PIPELINE_API_KEY
WTIS_WP_USERNAME
WTIS_WP_APP_PASSWORD
ANTHROPIC_API_KEY
OPENAI_API_KEY
PEXELS_API_KEY
SLACK_BOT_TOKEN
SLACK_CARD_CHANNEL
SLACK_CARD_CHANNEL_ID
BUFFER_ACCESS_TOKEN
BUFFER_CHANNEL_ID
MAILCHIMP_API_KEY
MAILCHIMP_AUDIENCE_ID
MAILCHIMP_DC
BLUESKY_HANDLE
BLUESKY_APP_PASSWORD
```

---

## WordPress Theme Structure

```
repo-root/                          ← child theme root (wellthisiissports-child)
├── CLAUDE.md                       ← this file
├── AGENT.md                        ← delegation layer
├── SPRINT.md                       ← live task board
├── .lando.yml                      ← local dev config
├── .env.lando.example              ← env var template
├── style.css                       ← theme declaration + design tokens
├── wtis-logo.png                   ← masthead logo (child theme root)
├── functions.php                   ← enqueue, meta registration, REST API, AJAX
├── inc/
│   ├── pipeline-api.php            ← all custom REST endpoints
│   ├── custom-fields.php           ← editorial meta box
│   ├── masthead.php                ← site header: logo img + nav include
│   ├── matchup-hero.php            ← shared 50/50 matchup hero (single only)
│   ├── homepage-payload.php        ← homepage card data + media/badge helpers
│   └── footer-content.php         ← footer include
├── templates/
│   ├── front-page.php              ← Ringer homepage: dark rails (#111), hero + compact, mid row, wide + ledger (1400px max)
│   ├── single.php                  ← prediction detail page
│   ├── archive.php                 ← sport/league archive
│   └── page.php                    ← generic page template
├── js/
│   └── prediction.js               ← prediction component (vanilla JS, IIFE)
├── sass/
│   ├── style.scss                  ← entry point, imports all partials
│   ├── _tokens.scss                ← design tokens: colors, type, spacing
│   ├── _masthead.scss              ← dark masthead bar, logo, nav
│   ├── _homepage.scss              ← matchup grid, sport sections
│   ├── _matchup.scss               ← prediction display, confidence meter, ledger
│   ├── _takeaways.scss             ← The Edge component (key factors for/against)
│   ├── _ads.scss                   ← ad unit positions
│   ├── _footer.scss                ← footer styles
│   ├── _pages.scss                 ← generic page styles
│   └── _mobile.scss                ← responsive breakpoints
└── css/
    └── style.min.css               ← compiled output (NEVER edit directly)
```

**Parent theme:** Understrap (Bootstrap 5 grid). Lives on server only, not in repo. Never modify it.

---

## Sass Compilation

Dart Sass (not node-sass). Two paths:
- Via Lando: `lando sass` or `lando sass-watch`
- Direct: `sass sass/style.scss:css/style.min.css --style=compressed`

`style.min.css` is committed to the repo and deployed. The server does not compile Sass.

**Rules:**
- Import `_tokens` first in every partial
- Never hardcode values, always use tokens
- Use mixins for repeated patterns
- `_mobile.scss` handles all breakpoints

---

## Design System

**Visual references:** The Ringer, Vox, The18

**Direction:**
- Bold, colorful, distinctly sports
- White/light as default theme
- Dark mode: auto via OS device setting
- Card-forward layout with color-blocked sections
- Confidence meter as hero UI element
- Accuracy ledger prominently placed on homepage and archive pages
- Distinct from WTIN's civic editorial aesthetic

**Design tokens:** Approved by The Director, Sprint 1. Locked below and in `_tokens.scss`.

**Colors**

| Token | Hex | Role |
|---|---|---|
| `--wtis-gold-400` | `#F5A623` | Primary CTA, confidence meter, highlights |
| `--wtis-gold-600` | `#C47D0E` | Hover states, borders |
| `--wtis-gold-50` | `#FEF3DC` | Card backgrounds, fills |
| `--wtis-blue-500` | `#0057FF` | Links, secondary actions, sport tags |
| `--wtis-blue-700` | `#003DBF` | Hover, dark mode primary |
| `--wtis-blue-50` | `#E0EAFF` | Info backgrounds, tag fills |
| `--wtis-red-500` | `#E8192C` | Urgent update badge, upset alert, loss indicator |
| `--wtis-red-50` | `#FDE8EA` | Alert card backgrounds |
| `--wtis-green-500` | `#00A651` | Correct prediction, win indicator, ledger positive |
| `--wtis-green-50` | `#E0F5EB` | Win background, ledger fill |
| `--wtis-ink` | `#111111` | Primary text, headings |
| `--wtis-off-white` | `#F7F7F5` | Page background default |
| `--wtis-white` | `#FFFFFF` | Card surfaces, content areas |
| `--wtis-gray-500` | `#6B6B6B` | Secondary text, metadata |
| `--wtis-masthead-bg` | `#1a1a1a` | Sticky masthead / logo bar background |
| `--wtis-hero-surface` | `#111111` | Full-bleed matchup hero panel (fixed dark; does not follow inverted `--wtis-ink`) |
| `--wtis-hero-on-surface` | `#FFFFFF` | Primary type on hero panel |
| `--wtis-hero-muted` | `#9B9B9B` | Hero panel metadata |

**Typography**

| Token | Value | Role |
|---|---|---|
| Display font | Barlow Condensed Bold | Matchup titles, hero headings |
| Headline font | Barlow SemiBold | Section headings, card titles |
| Body font | Inter Regular | Analysis text, summaries |
| UI font | Inter Medium | Labels, tags, metadata, nav |
| Confidence score | Barlow Condensed Bold, 48px | Card / compact meter numerals |
| Confidence score (large) | Barlow Condensed Bold, 64px | Sidebar meter + hero pick score numeral (`--wtis-text-6xl`) |

**Usage rules**
- Gold owns the confidence meter and every primary CTA
- Red is reserved for URGENT UPDATE badge and upset alerts only
- Green is reserved for correct predictions and positive ledger values
- Barlow Condensed at large sizes for all matchup and score displays
- Dark mode: **disabled** (May 2026 Director); `prefers-color-scheme` / `[data-theme="dark"]` hooks are commented out in `_tokens.scss` until re-enabled. Matchup hero panel on single posts still uses fixed `--wtis-hero-surface` for editorial contrast.

---

## Custom Post Meta

All prediction content lives in post meta, not post content.

| Meta key | Purpose |
|---|---|
| `wtis_team_home` | Home team name |
| `wtis_team_away` | Away team name |
| `wtis_matchup_title` | Display title for the matchup |
| `wtis_headline_personality` | Article H1 hero headline (falls back to matchup title) |
| `wtis_headline_seo` | Article H2 SEO subhead (falls back to “Home vs Away Prediction”) |
| `wtis_sport` | Sport (e.g., World Cup, NFL, NBA) |
| `wtis_league` | League or tournament |
| `wtis_matchup_date` | Scheduled game date (ISO timestamp) |
| `wtis_prediction_winner` | Predicted winning team |
| `wtis_confidence_score` | Confidence score 1-100 |
| `wtis_analysis` | Full AI analysis (500-600 words) |
| `wtis_prediction_grade` | Internal scoring 1-100 |
| `wtis_ai_generated` | Boolean |
| `wtis_ingested_at` | Pipeline ingest timestamp (ISO) |
| `wtis_actual_result` | Post-game actual result |
| `wtis_prediction_correct` | Boolean, set post-game |
| `wtis_factors_for` | Pipe-separated factors favoring predicted winner |
| `wtis_factors_against` | Pipe-separated risk factors |
| `wtis_article_stage` | preview / matchup / urgent_update |
| `wtis_image_brief_scene` | Scene description for OpenAI image generation |

---

## WordPress REST API

**Custom endpoint — inc/pipeline-api.php:**
- `POST /wp-json/wtis/v1/matchups` — create matchup with all prediction meta
- `PATCH /wp-json/wtis/v1/matchups/{id}` — update matchup fields
- `POST /wp-json/wtis/v1/matchups/{id}/image` — upload featured image
- `PATCH /wp-json/wtis/v1/matchups/{id}/status` — set draft/publish
- `PATCH /wp-json/wtis/v1/matchups/{id}/result` — post-game result update
- `GET /wp-json/wtis/v1/status` — pipeline health check
- `GET /wp-json/wtis/v1/ledger` — accuracy ledger per sport
- Auth: `X-WTIS-Key` header, key stored in WP option `wtis_pipeline_api_key`

**Standard WP endpoints used:**
- `GET /wp-json/wp/v2/posts` — dedup fetch
- `GET /wp-json/wp/v2/media` — media library fetch for image reuse
- `POST /wp-json/wp/v2/media` — card image upload for Buffer URL (Basic auth)
- `POST /wp-json/breeze/v1/clear-all-cache` — cache flush

---

## The Accuracy Ledger

Core differentiating feature. No equivalent in WTIN.

Tracks per sport:
- Total predictions made
- Correct predictions
- Accuracy percentage
- Running streak

**Implementation:**
- WP options table for aggregate stats per sport
- Custom post type `wtis_ledger` for per-prediction records
- Displayed as a live widget on homepage and sport archive pages

**Post-game update flow:**
1. Pipeline ingests game result from sports API
2. Compares result to `wtis_prediction_winner`
3. Sets `wtis_prediction_correct` true/false on matchup post
4. Updates ledger aggregate for that sport via `PATCH /wp-json/wtis/v1/matchups/{id}/result`

---

## Sports Data API

**Primary:** API-Football (api-sports.io)
- World Cup 2026 coverage confirmed, schedule already live
- Provides fixtures, teams, players, injuries, live scores, final results, stats
- Auth: API key in header
- Free tier: 100 requests/day, sufficient for pipeline development and testing
- Paid tier: $19/month for 7,500 requests/day, evaluate upgrade before World Cup launch
- Python integration: plain REST with requests library
- World Cup params: `league=1, season=2026`

**Fallback:** football-data.org
- Free tier: 10 calls/minute, delayed scores, fixtures and schedules
- Paid tier: €12/month for live scores
- Use for fixtures and final scores if API-Football has issues
- Auth: X-Auth-Token header

**Not used at launch:**
- The Odds API: no roster/injury data, add-on only if odds become a feature later
- SportsRadar: enterprise pricing, not bootstrap-friendly
- ESPN unofficial: fragile for automated pipeline, emergency fallback only

**Cost tracking:** Start on API-Football free tier. Monitor request volume during development. Evaluate $19/month upgrade against actual usage before World Cup launch. Track all third-party API costs separately from Anthropic and OpenAI spend alerts.

---

## The Pipeline

```
STAGE 1: INGEST (ingest.py)
Sports data APIs → upcoming matchup extraction → dedup → candidates.json

STAGE 2: PREDICT (predict.py)
Claude Haiku → predictive analysis per matchup
→ Winner prediction + confidence score
→ The Breakdown → The Edge → The Blind Side → Image brief → framed.json
→ Image brief → framed.json

STAGE 3: PUBLISH (publish.py)
WordPress REST API → OpenAI hero image
→ Bluesky (score 6+) → Mailchimp digest

STAGE 4: CARD + LEDGER (card.py)
Claude Sonnet scores → if 8+:
→ Card type selection → OpenAI gpt-image-1
→ Slack #ai-feed → Buffer → Facebook
Post-game: ingest result → update ledger
```

**Pipeline schedule:** TBD. Driven by World Cup match schedule at launch. Not a fixed 6x daily cadence like WTIN.

**Runner:** ubuntu-latest, Python 3.11

---

## The Article Lifecycle

Every matchup follows a three-stage publishing rhythm:

| Stage | Timing | Trigger |
|---|---|---|
| Stage 1: Weekly Preview | Start of week | Scheduled |
| Stage 2: The Matchup | Few days before game | Scheduled |
| Stage 3: Urgent Update | Day before, if material news | TBD, manual or automated |

Stage 3 updates the existing article, does not create a new one. Adds URGENT UPDATE badge, triggers fresh social push.

---

## The Card System

Six card type badges:

- THE PICK
- UPSET ALERT
- LOCK OF THE WEEK
- DON'T BET THIS
- THIS JUST SHIFTED
- BOTH SIDES ARE WRONG

Score gate: Claude Sonnet scores 0-10. Cards fire at 8+. Bluesky at 6+. Buffer/Facebook at 8+.

Card type protocol: Sonnet must emit `CARD_TYPE: {type}` as the literal first line before JSON. Parsed as authoritative override.

---

## Distribution

| Platform | Trigger |
|---|---|
| WordPress | Every matchup |
| Bluesky | Score 6+ |
| Mailchimp | Daily digest |
| Facebook via Buffer | Score 8+, with card image |
| X | TBD post-launch |
| Threads | TBD post-launch |

**UTM parameters:**
- Facebook: `utm_source=facebook&utm_medium=social&utm_campaign=wtis-card`
- Bluesky: `utm_source=bluesky&utm_medium=social&utm_campaign=wtis-daily`
- Mailchimp: `utm_source=mailchimp&utm_medium=email&utm_campaign=wtis-digest`

---

## AI Prompt Pattern

**Prediction prompt structure (Claude Haiku):**
```
You are a neutral AI sports analyst.
Given the following matchup data, generate a prediction:

PREDICTION: [winner + confidence score 1-100]
THE BREAKDOWN: [500-600 word analysis, what the data says vs the narrative]
THE EDGE — FACTORS FOR: [top 3 reasons this team wins]
THE EDGE — FACTORS AGAINST: [top 3 risks for this team]
THE BLIND SIDE: [the angle everyone is missing]

Rules:
- Confidence score must be justified with specific data
- Name specific stats, not vague claims
- No em dashes
- No hedging openers
- 500-600 words for analysis
```

---

## Local Dev

```
lando start          # spins up WordPress + MariaDB + MailHog + phpMyAdmin
lando wp [cmd]       # WP-CLI passthrough
lando sass           # compile Sass once
lando sass-watch     # watch mode
lando build-theme    # Sass + cache flush
lando db-pull        # pull prod DB to local
```

Local URLs:
- `https://wellthisiissports.lndo.site`
- `http://pma.wellthisiissports.lndo.site`
- `http://mail.wellthisiissports.lndo.site`

---

## Coding Conventions

- **PHP:** WordPress coding standards, escape all output, nonce all forms, prefix everything `wtis_`
- **Sass:** Import `_tokens` first, never hardcode values, use mixins
- **JS:** Vanilla JS only, no jQuery, IIFE pattern
- **Deploy:** Never rsync manually. Always deploy via GitHub Actions SSH workflow. The workflow handles rsync automatically on every push to main.
- **Secrets:** Never commit credentials, use GitHub Secrets + env block in workflow
- **CLAUDE.md:** Update in the same commit as any structural change

---

## Known Gotchas (Inherited from WTIN)

- **GitHub Actions env block:** Secrets must be explicitly mapped in `env:`. Repo settings alone is not enough.
- **GitHub Actions log masking:** If a secret value appears literally in code (like a Slack channel ID), GitHub redacts it to `***`. Hardcode channel IDs as fallback constants.
- **WordPress 415:** Never set `Content-Type: application/json` at the session level. Let the `json=` kwarg handle it per-request.
- **Buffer API is GraphQL only:** Endpoint is `api.buffer.com`. `firstComment` lives inside `metadata.facebook`, not at top level.
- **Dedup requires `date_gmt`:** Always request `date_gmt` from WP REST API, not `date`, to avoid timezone offset errors.
- **OpenAI gpt-image-1 returns base64:** Decode and save locally before doing anything else. File gets deleted on upload, read bytes before calling upload if needed elsewhere.
- **wp/v2/media needs Application Password auth:** Not the pipeline API key. Use `Authorization: Basic base64(username:app_password)`.
- **Always append UTM URL explicitly:** `text = f"{caption}\n\n{utm_url}"`. Don't rely on the LLM to embed it.
- **Test integrations with a test script first:** Never wait for a live pipeline run to verify a new integration.

---

## Spend Alerts

- Anthropic: alert at $8
- OpenAI: alert at $8

---

## Launch Target

**Soft launch: World Cup 2026 — June 11, 2026**

Full sport roster (NFL, NBA, MLB, NHL, MLS) added post-World Cup for fall 2026 season.

---

*Well This Is Sports — CLAUDE.md — May 2026*
*Maintained by The Collective. Update in every structural change commit.*
