"""
ingest.py — Stage 1: Fetch upcoming World Cup fixtures from API-Football,
dedup against existing WP posts, write candidates.json.
"""

import json
import logging
import os
from datetime import datetime, timezone, timedelta

import requests
from dotenv import load_dotenv

load_dotenv(os.path.join(os.path.dirname(__file__), ".env.lando"))

logging.basicConfig(level=logging.INFO, format="%(levelname)s %(message)s")
log = logging.getLogger(__name__)

APIFOOTBALL_KEY = os.environ["APIFOOTBALL_API_KEY"]
WP_BASE_URL = os.environ["WTIS_SITE_URL"].rstrip("/")
PIPELINE_API_KEY = os.environ["WTIS_PIPELINE_API_KEY"]

# World Cup 2026
LEAGUE_ID = 1
SEASON = 2026

# Maps API-Football league IDs to wtis_sport display values.
# wtis_update_ledger slugifies these, so "World Cup" → "world-cup" in ledger
# and archive.php finds it via $term->slug.
LEAGUE_SPORT_MAP = {
    1: "World Cup",
}

# How far ahead to look for fixtures
LOOKAHEAD_DAYS = 14

APIFOOTBALL_BASE = "https://v3.football.api-sports.io"

CANDIDATES_PATH = os.path.join(os.path.dirname(__file__), "candidates.json")


def _apifootball_headers():
    return {"x-apisports-key": APIFOOTBALL_KEY}


def fetch_upcoming_fixtures():
    """Fetch fixtures for the next LOOKAHEAD_DAYS days."""
    today = datetime.now(timezone.utc).date()
    to_date = today + timedelta(days=LOOKAHEAD_DAYS)

    params = {
        "league": LEAGUE_ID,
        "season": SEASON,
        "from": today.isoformat(),
        "to": to_date.isoformat(),
        "status": "NS",  # Not Started
    }

    log.info("Fetching fixtures league=%s season=%s from=%s to=%s",
             LEAGUE_ID, SEASON, params["from"], params["to"])

    resp = requests.get(
        f"{APIFOOTBALL_BASE}/fixtures",
        headers=_apifootball_headers(),
        params=params,
        timeout=30,
    )
    resp.raise_for_status()

    data = resp.json()
    errors = data.get("errors", {})
    if errors:
        raise RuntimeError(f"API-Football error: {errors}")

    fixtures = data.get("response", [])
    log.info("Fetched %d fixtures", len(fixtures))
    return fixtures


def fetch_existing_matchup_titles():
    """
    Fetch recent WP posts and return a set of existing matchup titles.
    Uses date_gmt (not date) to avoid timezone offset errors.
    """
    titles = set()
    page = 1
    per_page = 100

    headers = {"X-WTIS-Key": PIPELINE_API_KEY}

    while True:
        resp = requests.get(
            f"{WP_BASE_URL}/wp-json/wp/v2/posts",
            headers=headers,
            params={
                "per_page": per_page,
                "page": page,
                "orderby": "date_gmt",
                "order": "desc",
                "_fields": "id,title,meta",
            },
            timeout=30,
        )

        if resp.status_code == 400:
            break  # no more pages

        resp.raise_for_status()
        posts = resp.json()

        if not posts:
            break

        for post in posts:
            # Dedup on matchup_title meta or post title
            meta = post.get("meta", {})
            matchup_title = meta.get("wtis_matchup_title") or post.get("title", {}).get("rendered", "")
            if matchup_title:
                titles.add(matchup_title.strip().lower())

        if len(posts) < per_page:
            break

        page += 1

    log.info("Found %d existing matchup titles in WP", len(titles))
    return titles


def extract_candidate(fixture):
    """Transform a raw API-Football fixture into a pipeline candidate."""
    f = fixture.get("fixture", {})
    teams = fixture.get("teams", {})
    league = fixture.get("league", {})

    home = teams.get("home", {}).get("name", "Unknown")
    away = teams.get("away", {}).get("name", "Unknown")
    matchup_title = f"{home} vs {away}"

    kickoff_utc = f.get("date", "")  # ISO 8601 with timezone

    league_id = league.get("id", LEAGUE_ID)
    sport = LEAGUE_SPORT_MAP.get(league_id, league.get("name", "Soccer"))

    return {
        "fixture_id": f.get("id"),
        "matchup_title": matchup_title,
        "team_home": home,
        "team_away": away,
        "sport": sport,
        "league": league.get("name", "FIFA World Cup"),
        "matchup_date": kickoff_utc,
        "venue": f.get("venue", {}).get("name", "") if isinstance(f.get("venue"), dict) else "",
        "home_logo": teams.get("home", {}).get("logo", ""),
        "away_logo": teams.get("away", {}).get("logo", ""),
        "round": league.get("round", ""),
    }


def run():
    fixtures = fetch_upcoming_fixtures()

    if not fixtures:
        log.warning("No upcoming fixtures found — nothing to ingest")
        with open(CANDIDATES_PATH, "w") as fh:
            json.dump([], fh, indent=2)
        return []

    existing_titles = fetch_existing_matchup_titles()

    candidates = []
    for fixture in fixtures:
        candidate = extract_candidate(fixture)
        title_key = candidate["matchup_title"].strip().lower()

        if title_key in existing_titles:
            log.info("SKIP (duplicate): %s", candidate["matchup_title"])
            continue

        candidates.append(candidate)
        log.info("CANDIDATE: %s on %s", candidate["matchup_title"], candidate["matchup_date"])

    log.info("Writing %d candidates to %s", len(candidates), CANDIDATES_PATH)
    with open(CANDIDATES_PATH, "w") as fh:
        json.dump(candidates, fh, indent=2)

    return candidates


if __name__ == "__main__":
    run()
