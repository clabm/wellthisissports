"""
seed_matchups.py — One-time script: seed 10 World Cup 2026 matchups to live WP.
Calls Claude Haiku for each prediction, publishes via pipeline REST API.
No image generation. Run from pipeline/ directory.

Usage: python3 pipeline/seed_matchups.py
"""

import logging
import os
import sys
import time

from dotenv import load_dotenv

# Load env BEFORE importing pipeline modules — they read env at import time
load_dotenv(os.path.join(os.path.dirname(__file__), ".env.lando"))

import predict
import publish

logging.basicConfig(level=logging.INFO, format="%(levelname)s %(message)s")
log = logging.getLogger(__name__)

MATCHUPS = [
    {
        "fixture_id": 9001,
        "matchup_title": "USA vs Portugal",
        "team_home": "USA",
        "team_away": "Portugal",
        "sport": "World Cup",
        "league": "FIFA World Cup 2026",
        "matchup_date": "2026-06-15T20:00:00+00:00",
        "venue": "SoFi Stadium, Los Angeles",
        "home_logo": "",
        "away_logo": "",
        "round": "Group A",
    },
    {
        "fixture_id": 9002,
        "matchup_title": "Argentina vs Morocco",
        "team_home": "Argentina",
        "team_away": "Morocco",
        "sport": "World Cup",
        "league": "FIFA World Cup 2026",
        "matchup_date": "2026-06-16T20:00:00+00:00",
        "venue": "MetLife Stadium, New York",
        "home_logo": "",
        "away_logo": "",
        "round": "Group B",
    },
    {
        "fixture_id": 9003,
        "matchup_title": "Brazil vs Germany",
        "team_home": "Brazil",
        "team_away": "Germany",
        "sport": "World Cup",
        "league": "FIFA World Cup 2026",
        "matchup_date": "2026-06-17T20:00:00+00:00",
        "venue": "AT&T Stadium, Dallas",
        "home_logo": "",
        "away_logo": "",
        "round": "Group C",
    },
    {
        "fixture_id": 9004,
        "matchup_title": "France vs England",
        "team_home": "France",
        "team_away": "England",
        "sport": "World Cup",
        "league": "FIFA World Cup 2026",
        "matchup_date": "2026-06-18T20:00:00+00:00",
        "venue": "Levi's Stadium, San Francisco",
        "home_logo": "",
        "away_logo": "",
        "round": "Group D",
    },
    {
        "fixture_id": 9005,
        "matchup_title": "Spain vs Netherlands",
        "team_home": "Spain",
        "team_away": "Netherlands",
        "sport": "World Cup",
        "league": "FIFA World Cup 2026",
        "matchup_date": "2026-06-19T20:00:00+00:00",
        "venue": "Hard Rock Stadium, Miami",
        "home_logo": "",
        "away_logo": "",
        "round": "Group E",
    },
    {
        "fixture_id": 9006,
        "matchup_title": "Mexico vs Uruguay",
        "team_home": "Mexico",
        "team_away": "Uruguay",
        "sport": "World Cup",
        "league": "FIFA World Cup 2026",
        "matchup_date": "2026-06-20T20:00:00+00:00",
        "venue": "Estadio Azteca, Mexico City",
        "home_logo": "",
        "away_logo": "",
        "round": "Group F",
    },
    {
        "fixture_id": 9007,
        "matchup_title": "USA vs Argentina",
        "team_home": "USA",
        "team_away": "Argentina",
        "sport": "World Cup",
        "league": "FIFA World Cup 2026",
        "matchup_date": "2026-07-02T19:00:00+00:00",
        "venue": "MetLife Stadium, New York",
        "home_logo": "",
        "away_logo": "",
        "round": "Round of 16",
    },
    {
        "fixture_id": 9008,
        "matchup_title": "Brazil vs France",
        "team_home": "Brazil",
        "team_away": "France",
        "sport": "World Cup",
        "league": "FIFA World Cup 2026",
        "matchup_date": "2026-07-09T19:00:00+00:00",
        "venue": "SoFi Stadium, Los Angeles",
        "home_logo": "",
        "away_logo": "",
        "round": "Quarter Final",
    },
    {
        "fixture_id": 9009,
        "matchup_title": "Argentina vs England",
        "team_home": "Argentina",
        "team_away": "England",
        "sport": "World Cup",
        "league": "FIFA World Cup 2026",
        "matchup_date": "2026-07-14T19:00:00+00:00",
        "venue": "AT&T Stadium, Dallas",
        "home_logo": "",
        "away_logo": "",
        "round": "Semi Final",
    },
    {
        "fixture_id": 9010,
        "matchup_title": "Brazil vs USA",
        "team_home": "Brazil",
        "team_away": "USA",
        "sport": "World Cup",
        "league": "FIFA World Cup 2026",
        "matchup_date": "2026-07-19T19:00:00+00:00",
        "venue": "MetLife Stadium, New York",
        "home_logo": "",
        "away_logo": "",
        "round": "Final",
    },
]


def run():
    results = []

    for i, candidate in enumerate(MATCHUPS, 1):
        title = candidate["matchup_title"]
        log.info("=== [%d/10] %s ===", i, title)

        try:
            prediction = predict.predict_one(candidate)
            post_id, post_url = publish.create_matchup_post(prediction)

            results.append({
                "matchup_title": title,
                "post_id": post_id,
                "url": post_url,
                "winner": prediction["wtis_prediction_winner"],
                "confidence": prediction["wtis_confidence_score"],
                "status": "OK",
            })
            log.info("Published [%d]: %s — %s", post_id, title, post_url)

        except Exception as exc:
            log.error("FAILED: %s — %s", title, exc)
            results.append({
                "matchup_title": title,
                "status": "FAILED",
                "error": str(exc),
            })

        # Pause between calls to respect Anthropic rate limits
        if i < len(MATCHUPS):
            time.sleep(3)

    print("\n=== SEED RESULTS ===")
    for r in results:
        if r["status"] == "OK":
            print(f"  OK   [{r['post_id']:>5}] {r['matchup_title']:<28} → {r['winner']} ({r['confidence']}) — {r['url']}")
        else:
            print(f"  FAIL [     ] {r['matchup_title']:<28} — {r['error']}")

    ok = sum(1 for r in results if r["status"] == "OK")
    print(f"\n{ok}/{len(results)} published successfully")
    return results


if __name__ == "__main__":
    run()
