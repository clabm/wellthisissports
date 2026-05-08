"""
test_publish.py — Verify WP REST API connection and post creation.
Writes a DRAFT post (not published) and reports meta fields.
Run: python pipeline/test_publish.py
"""

import json
import os
import sys

import requests
from dotenv import load_dotenv

load_dotenv()

required = [
    "WTIS_SITE_URL", "WTIS_PIPELINE_API_KEY",
    "WTIS_WP_USERNAME", "WTIS_WP_APP_PASSWORD",
]
missing = [k for k in required if not os.environ.get(k)]
if missing:
    print(f"ERROR: Missing env vars: {', '.join(missing)}")
    sys.exit(1)

WP_BASE_URL = os.environ["WTIS_SITE_URL"].rstrip("/")
PIPELINE_API_KEY = os.environ["WTIS_PIPELINE_API_KEY"]

print("=== test_publish.py ===\n")

# Step 1: Health check
print("Step 1: Pipeline status check...")
resp = requests.get(
    f"{WP_BASE_URL}/wp-json/wtis/v1/status",
    headers={"X-WTIS-Key": PIPELINE_API_KEY},
    timeout=15,
)
print(f"  Status: {resp.status_code}")
if resp.status_code == 200:
    print(f"  Response: {resp.json()}")
else:
    print(f"  WARN: Status endpoint returned {resp.status_code} — {resp.text[:200]}")

# Step 2: Create a test draft post
print("\nStep 2: Creating test DRAFT post...")
test_payload = {
    "title": "[TEST] Brazil vs Argentina — DELETE ME",
    "status": "draft",
    "wtis_team_home": "Brazil",
    "wtis_team_away": "Argentina",
    "wtis_matchup_title": "Brazil vs Argentina",
    "wtis_sport": "Soccer",
    "wtis_league": "FIFA World Cup",
    "wtis_matchup_date": "2026-07-14T20:00:00+00:00",
    "wtis_prediction_winner": "Brazil",
    "wtis_confidence_score": 72,
    "wtis_analysis": "Test analysis content.",
    "wtis_factors_for": "Strong home support|Superior squad depth|Recent form",
    "wtis_factors_against": "Argentina's Messi factor|High pressure tournament|Brazil's recent draws",
    "wtis_ai_generated": True,
    "wtis_article_stage": "matchup",
}

create_resp = requests.post(
    f"{WP_BASE_URL}/wp-json/wtis/v1/matchups",
    headers={"X-WTIS-Key": PIPELINE_API_KEY},
    json=test_payload,
    timeout=30,
)
print(f"  Status: {create_resp.status_code}")

if create_resp.status_code not in (200, 201):
    print(f"  FAIL: {create_resp.text[:500]}")
    sys.exit(1)

post_data = create_resp.json()
post_id = post_data.get("id")
print(f"  Created post ID: {post_id}")
print(f"  URL: {post_data.get('link', 'N/A')}")

# Step 3: Verify post exists and meta is set
print(f"\nStep 3: Verifying post meta via WP REST API...")
verify_resp = requests.get(
    f"{WP_BASE_URL}/wp-json/wp/v2/posts/{post_id}",
    headers={"X-WTIS-Key": PIPELINE_API_KEY},
    params={"_fields": "id,title,status,meta"},
    timeout=15,
)
verify_resp.raise_for_status()
post = verify_resp.json()
meta = post.get("meta", {})

print(f"  Post status: {post.get('status')}")
print(f"  wtis_team_home: {meta.get('wtis_team_home')}")
print(f"  wtis_prediction_winner: {meta.get('wtis_prediction_winner')}")
print(f"  wtis_confidence_score: {meta.get('wtis_confidence_score')}")
print(f"  wtis_factors_for: {meta.get('wtis_factors_for', '')[:80]}")

# Step 4: Delete the test post
print(f"\nStep 4: Deleting test post {post_id}...")
del_resp = requests.delete(
    f"{WP_BASE_URL}/wp-json/wp/v2/posts/{post_id}",
    headers={"Authorization": "Basic " + __import__("base64").b64encode(
        f"{os.environ['WTIS_WP_USERNAME']}:{os.environ['WTIS_WP_APP_PASSWORD']}".encode()
    ).decode()},
    params={"force": True},
    timeout=15,
)
if del_resp.status_code in (200, 410):
    print(f"  Test post deleted: OK")
else:
    print(f"  WARN: Could not delete test post ({del_resp.status_code}) — delete it manually")

print("\n=== test_publish.py PASSED ===")
