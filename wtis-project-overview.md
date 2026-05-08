# Well This Is Sports — Project Overview

## wellthisiissports.com

> The Pick. The Prediction. The Record.

---

## What It Is

**Well This Is Sports** is a fully automated AI-powered sports prediction platform. Every matchup gets a full analytical breakdown, a winner prediction with a confidence score, and a public accuracy ledger that tracks whether the AI was right. The ledger is the product, not a feature.

This is Bootstrap Experiment #2 in the Well This Is Media umbrella, built and operated by a solo founder using the same multi-agent AI workflow as Well This Is News.

---

## The Umbrella

**Well This Is Media** (wellthisismedia.com) is the parent brand.

| Property | URL | Engine | Status |
|---|---|---|---|
| Well This Is News | wellthisisnews.com | Perspective Engine | Live |
| Well This Is Sports | wellthisiissports.com | Predictive Engine | In Build |

---

## Current State

In development. Target soft launch: **World Cup 2026 (June 11, 2026).**

---

## The Predictive Engine

Different from WTIN's perspective toggle. The product here is the prediction itself, the confidence behind it, and the public record of whether the AI got it right.

Every matchup article includes:

- Team A vs Team B breakdown
- Strengths and weaknesses per side
- Key matchup factors (form, injuries, head-to-head, home/away)
- AI prediction with confidence percentage
- The Pick, winner + score range

---

## The Article Lifecycle

Every matchup follows a three-stage publishing rhythm:

**Stage 1: The Weekly Preview**
Published at the start of the week. One article covering the full slate for the sport. Sets context, storylines, and what to watch.

**Stage 2: The Matchup**
Published a few days before the game. Individual article per matchup. Full team breakdown, key factors, and the official prediction. Logged to the accuracy ledger at publish time.

**Stage 3: The Urgent Update**
Published the day before if something material changes, injury, suspension, weather, lineup news. Same article updated, not a new one. Republished with an URGENT UPDATE badge and a fresh social push.

---

## The Accuracy Ledger

The core differentiating feature. No equivalent exists in WTIN.

The ledger tracks per sport:
- Total predictions made
- Correct predictions
- Accuracy percentage
- Running streak
- Best performing sport

Displayed prominently on the homepage and sport archive pages. Updated post-game when the pipeline ingests the final result.

Example display: NFL 30-10 | NBA 52-18 | World Cup 14-6

---

## Launch Scope

**Soft launch sport: World Cup 2026**

| Tournament | Dates | Notes |
|---|---|---|
| FIFA World Cup 2026 | June 11 - July 19, 2026 | US/Canada/Mexico hosted, anchor launch event |

Full sport roster added post-World Cup for fall 2026 season:

| Sport | Season |
|---|---|
| NFL | Fall/Winter |
| NBA | Fall/Spring |
| MLB | Spring/Fall |
| NHL | Fall/Spring |
| MLS | Spring/Fall |

---

## The Pipeline

```
STAGE 1: INGEST
Sports data APIs (ESPN, The Odds API, etc.)
→ Upcoming matchup extraction → dedup

STAGE 2: PREDICT
Claude Haiku → predictive analysis per matchup
→ Winner prediction + confidence score
→ Key factors → What the data says vs what the narrative says
→ Image brief

STAGE 3: PUBLISH
WordPress REST API → OpenAI hero image
→ Bluesky (score 6+)
→ Mailchimp digest

STAGE 4: CARD + LEDGER
Claude Sonnet scores → if 8+:
→ Card type selection → OpenAI gpt-image-1
→ Slack #ai-feed → Buffer → Facebook
Post-game: ingest result → update ledger
```

---

## The Card System

Repurposed from WTIN for sports context. Six card type badges:

- THE PICK
- UPSET ALERT
- LOCK OF THE WEEK
- DON'T BET THIS
- THIS JUST SHIFTED
- BOTH SIDES ARE WRONG

---

## Tech Stack

| Layer | Tool |
|---|---|
| CMS | WordPress on Cloudways (DigitalOcean) |
| Theme | Custom child theme on Understrap |
| Pipeline | Python, GitHub Actions |
| AI Prediction | Claude Haiku |
| AI Scoring | Claude Sonnet |
| Image Generation | OpenAI gpt-image-1 |
| Deploy | Push to main, GitHub Actions, Cloudways API |
| Local Dev | Lando (Docker) |
| CSS | Dart Sass |

---

## Distribution

| Platform | Status | Trigger |
|---|---|---|
| WordPress | Planned | Every matchup |
| Bluesky | Planned | Score 6+ |
| Mailchimp | Planned | Daily digest |
| Facebook via Buffer | Planned | Score 8+, with card image |
| X | TBD | Post-launch |
| Threads | TBD | Post-launch |

---

## Design System

Built in-house by The Collective. No external design agency. Cursor executes from a token brief and reference set approved by The Director.

**Visual References**
- The Ringer — strong typographic hierarchy, editorial confidence, data alongside narrative
- Vox — bold color blocking, card-forward layout, structured information design
- The18 — sports energy, bold and colorful, bite-sized content, high contrast, viral-forward

**Direction**
- Bold, colorful, distinctly sports, not generic
- White/light as default, dark mode auto via OS device setting
- Card-forward layout with color-blocked sections
- Confidence meter as a hero UI element
- Accuracy ledger prominently placed on homepage and archive pages
- Distinct from WTIN's civic editorial aesthetic

**Design Token Workflow**
1. Architect proposes token set, colors, typography, spacing, in Sprint 1
2. Director approves
3. Tokens locked into CLAUDE.md and _tokens.scss
4. Cursor builds theme from tokens plus reference brief
5. Director reviews in browser, iterates

Specific token values defined in Sprint 1.

---

## Monetization

Build audience first. No ads at launch.

Future monetization path:
- Google AdSense
- Affiliate and betting partner deals
- Newsletter sponsorships
- Mediavine (long term, traffic threshold)

---

## The Collective — Multi-Agent Workflow

| Agent | Tool | Role |
|---|---|---|
| The Architect | Claude Pro | Strategy, decisions, prompts |
| The Shop Hand | Claude Code | Core implementation, orchestration |
| The Cursor | Cursor | Front-end, theme, rapid iteration |
| The Codex | Codex | Parallel tasks, boilerplate |
| The Scout | Perplexity | Real-time sports data research |
| The Heavy Lifter | Gemini | Synthesis, QA, analysis |
| The Sentry | Checkly | Monitoring |

All agents report to #ai-feed in The Collective Slack workspace. The human director approves, directs, and decides.

**CLAUDE.md** is the shared brain. **AGENT.md** is the delegation layer owned by The Shop Hand. **SPRINT.md** is the live working surface updated every session.

---

## Key URLs

| URL | Purpose |
|---|---|
| wellthisiissports.com | Production site |
| wellthisiissports.com/wp-admin | WordPress admin |
| github.com/clabm/wellthisiissports | Codebase |
| wellthisismedia.com | Umbrella brand |

---

## Spend Alerts

- Anthropic: alert at $8
- OpenAI: alert at $8

---

*Well This Is Sports — Project Overview — May 2026*
*Prepared by The Architect (Claude Pro)*
