"""
test_predict.py — Verify Claude Haiku prediction output and framed.json schema.
Run: python pipeline/test_predict.py
"""

import json
import os
import sys

from dotenv import load_dotenv

load_dotenv(os.path.join(os.path.dirname(__file__), ".env.lando"))

required = ["ANTHROPIC_API_KEY"]
missing = [k for k in required if not os.environ.get(k)]
if missing:
    print(f"ERROR: Missing env vars: {', '.join(missing)}")
    sys.exit(1)

import predict

print("=== test_predict.py ===\n")

# Use a single mock candidate so we don't burn API quota on every test
mock_candidates_path = os.path.join(os.path.dirname(__file__), "candidates.json")

# If no candidates.json, create a test fixture
if not os.path.exists(mock_candidates_path):
    test_candidate = [{
        "fixture_id": 999999,
        "matchup_title": "Brazil vs Argentina",
        "team_home": "Brazil",
        "team_away": "Argentina",
        "sport": "Soccer",
        "league": "FIFA World Cup",
        "matchup_date": "2026-07-14T20:00:00+00:00",
        "venue": "MetLife Stadium",
        "home_logo": "",
        "away_logo": "",
        "round": "Final",
    }]
    with open(mock_candidates_path, "w") as fh:
        json.dump(test_candidate, fh, indent=2)
    print("No candidates.json found — created test fixture (Brazil vs Argentina)")
else:
    with open(mock_candidates_path) as fh:
        existing = json.load(fh)
    if not existing:
        print("candidates.json is empty — creating test fixture")
        test_candidate = [{
            "fixture_id": 999999,
            "matchup_title": "Brazil vs Argentina",
            "team_home": "Brazil",
            "team_away": "Argentina",
            "sport": "Soccer",
            "league": "FIFA World Cup",
            "matchup_date": "2026-07-14T20:00:00+00:00",
            "venue": "MetLife Stadium",
            "home_logo": "",
            "away_logo": "",
            "round": "Final",
        }]
        with open(mock_candidates_path, "w") as fh:
            json.dump(test_candidate, fh, indent=2)
    else:
        # Limit to 1 for cost
        with open(mock_candidates_path, "w") as fh:
            json.dump(existing[:1], fh, indent=2)
        print(f"Limited to 1 candidate for test (was {len(existing)})")

print("Step 1: Running predict.run()...")
framed = predict.run()
print(f"  Got {len(framed)} prediction(s)")

if not framed:
    print("FAIL: No predictions returned")
    sys.exit(1)

p = framed[0]

print(f"\nStep 2: Schema validation")
required_keys = [
    "matchup_title", "team_home", "team_away", "sport", "league",
    "matchup_date", "wtis_prediction_winner", "wtis_confidence_score",
    "wtis_analysis", "wtis_factors_for", "wtis_factors_against",
    "wtis_image_brief_scene", "wtis_ai_generated", "wtis_article_stage",
]
missing_keys = [k for k in required_keys if k not in p]
if missing_keys:
    print(f"  FAIL: Missing keys: {missing_keys}")
    sys.exit(1)
print(f"  All required keys present: OK")

print(f"\nStep 3: Quality checks")
print(f"  Matchup: {p['matchup_title']}")
print(f"  Winner: {p['wtis_prediction_winner']}")
print(f"  Confidence: {p['wtis_confidence_score']}")

# Confidence range
if not (1 <= p["wtis_confidence_score"] <= 100):
    print(f"  FAIL: Confidence out of range: {p['wtis_confidence_score']}")
    sys.exit(1)
print(f"  Confidence in range [1-100]: OK")

# Analysis word count
word_count = len(p["wtis_analysis"].split())
print(f"  Analysis word count: {word_count}")
if word_count < 300:
    print(f"  WARN: Analysis seems short ({word_count} words)")

# No em dashes
if "\u2014" in p["wtis_analysis"]:
    print(f"  FAIL: Em dash found in analysis")
    sys.exit(1)
print(f"  No em dashes in analysis: OK")

# Factors pipe-separated
for_factors = p["wtis_factors_for"].split("|")
against_factors = p["wtis_factors_against"].split("|")
print(f"  Factors for ({len(for_factors)}): {p['wtis_factors_for'][:80]}...")
print(f"  Factors against ({len(against_factors)}): {p['wtis_factors_against'][:80]}...")

print(f"  Image brief: {p['wtis_image_brief_scene'][:100]}...")

print("\n=== test_predict.py PASSED ===")
