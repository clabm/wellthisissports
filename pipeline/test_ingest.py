"""
test_ingest.py — Verify API-Football connection and candidates.json output.
Run: python pipeline/test_ingest.py
"""

import json
import os
import sys

from dotenv import load_dotenv

load_dotenv()

# Minimal env check before importing ingest
required = ["APIFOOTBALL_API_KEY", "WTIS_WP_BASE_URL", "WTIS_PIPELINE_API_KEY"]
missing = [k for k in required if not os.environ.get(k)]
if missing:
    print(f"ERROR: Missing env vars: {', '.join(missing)}")
    print("Copy pipeline/.env.lando.example to .env.lando and fill in values.")
    sys.exit(1)

import ingest

print("=== test_ingest.py ===\n")

# 1. Raw fixture fetch
print("Step 1: Fetching fixtures from API-Football...")
fixtures = ingest.fetch_upcoming_fixtures()
print(f"  Got {len(fixtures)} fixture(s)")

if fixtures:
    sample = fixtures[0]
    home = sample.get("teams", {}).get("home", {}).get("name")
    away = sample.get("teams", {}).get("away", {}).get("name")
    date = sample.get("fixture", {}).get("date")
    print(f"  Sample: {home} vs {away} on {date}")
else:
    print("  No fixtures returned — check league/season/date range.")

# 2. Full run with dedup
print("\nStep 2: Running full ingest (with WP dedup)...")
candidates = ingest.run()
print(f"  Candidates written: {len(candidates)}")

# 3. Verify candidates.json
candidates_path = os.path.join(os.path.dirname(__file__), "candidates.json")
with open(candidates_path) as fh:
    saved = json.load(fh)

print(f"\nStep 3: candidates.json sanity check")
print(f"  File contains {len(saved)} candidate(s)")

if saved:
    c = saved[0]
    required_keys = [
        "fixture_id", "matchup_title", "team_home", "team_away",
        "sport", "league", "matchup_date",
    ]
    missing_keys = [k for k in required_keys if k not in c]
    if missing_keys:
        print(f"  FAIL: Missing keys: {missing_keys}")
        sys.exit(1)
    print(f"  First candidate: {c['matchup_title']} ({c['league']}) on {c['matchup_date']}")
    print(f"  All required keys present: OK")
else:
    print("  No candidates (may be expected if WC fixtures not yet scheduled or already ingested)")

print("\n=== test_ingest.py PASSED ===")
