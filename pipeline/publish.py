"""
publish.py — Stage 3: Publish predictions from framed.json to WordPress,
generate hero image via OpenAI gpt-image-1, stub social distribution.
"""

import base64
import json
import logging
import os
import time

import requests
from dotenv import load_dotenv

load_dotenv()

logging.basicConfig(level=logging.INFO, format="%(levelname)s %(message)s")
log = logging.getLogger(__name__)

WP_BASE_URL = os.environ["WTIS_WP_BASE_URL"].rstrip("/")
PIPELINE_API_KEY = os.environ["WTIS_PIPELINE_API_KEY"]
WP_USERNAME = os.environ["WTIS_WP_USERNAME"]
WP_APP_PASSWORD = os.environ["WTIS_WP_APP_PASSWORD"]
OPENAI_API_KEY = os.environ["OPENAI_API_KEY"]

FRAMED_PATH = os.path.join(os.path.dirname(__file__), "framed.json")
TEMP_IMAGES_DIR = os.path.join(os.path.dirname(__file__), "temp_images")

os.makedirs(TEMP_IMAGES_DIR, exist_ok=True)

OPENAI_IMAGE_ENDPOINT = "https://api.openai.com/v1/images/generations"


def _pipeline_headers():
    return {"X-WTIS-Key": PIPELINE_API_KEY}


def _basic_auth_headers():
    """Application Password auth — required for wp/v2/media uploads."""
    credentials = base64.b64encode(
        f"{WP_USERNAME}:{WP_APP_PASSWORD}".encode()
    ).decode()
    return {"Authorization": f"Basic {credentials}"}


def generate_hero_image(prediction):
    """
    Call OpenAI gpt-image-1, decode base64, save to disk.
    Returns local file path.
    """
    brief = prediction.get("wtis_image_brief_scene", "")
    matchup = prediction.get("matchup_title", "matchup")

    prompt = (
        f"Dramatic, cinematic sports photography. {brief} "
        "High resolution, stadium atmosphere, vivid colors. "
        "No text, no logos, no overlays."
    )

    log.info("Generating image for: %s", matchup)

    resp = requests.post(
        OPENAI_IMAGE_ENDPOINT,
        headers={
            "Authorization": f"Bearer {OPENAI_API_KEY}",
            "Content-Type": "application/json",
        },
        json={
            "model": "gpt-image-1",
            "prompt": prompt,
            "n": 1,
            "size": "1792x1024",
            "response_format": "b64_json",
        },
        timeout=120,
    )
    resp.raise_for_status()

    data = resp.json()
    b64_data = data["data"][0]["b64_json"]

    # Decode immediately — file is only on disk until uploaded
    image_bytes = base64.b64decode(b64_data)

    safe_name = matchup.lower().replace(" ", "-").replace("/", "-")[:60]
    filename = f"{safe_name}-{int(time.time())}.png"
    local_path = os.path.join(TEMP_IMAGES_DIR, filename)

    with open(local_path, "wb") as fh:
        fh.write(image_bytes)

    log.info("Image saved: %s (%d bytes)", local_path, len(image_bytes))
    return local_path, filename


def upload_hero_image(local_path, filename, post_id):
    """
    Upload image to WP media library and set as featured image.
    Uses Application Password auth (not pipeline API key).
    """
    log.info("Uploading image to WP media: %s", filename)

    with open(local_path, "rb") as fh:
        image_bytes = fh.read()

    headers = {
        **_basic_auth_headers(),
        "Content-Disposition": f'attachment; filename="{filename}"',
        "Content-Type": "image/png",
    }

    resp = requests.post(
        f"{WP_BASE_URL}/wp-json/wp/v2/media",
        headers=headers,
        data=image_bytes,
        timeout=60,
    )
    resp.raise_for_status()

    media_id = resp.json()["id"]
    log.info("Uploaded media ID: %d", media_id)

    # Set as featured image via pipeline endpoint
    patch_resp = requests.post(
        f"{WP_BASE_URL}/wp-json/wtis/v1/matchups/{post_id}/image",
        headers=_pipeline_headers(),
        json={"featured_media": media_id},
        timeout=30,
    )
    patch_resp.raise_for_status()

    # Clean up local file
    os.remove(local_path)
    log.info("Temp image deleted: %s", local_path)

    return media_id


def create_matchup_post(prediction):
    """POST to /wp-json/wtis/v1/matchups to create the post."""
    payload = {
        "title": prediction["matchup_title"],
        "status": "publish",
        "wtis_team_home": prediction["team_home"],
        "wtis_team_away": prediction["team_away"],
        "wtis_matchup_title": prediction["matchup_title"],
        "wtis_sport": prediction.get("sport", "Soccer"),
        "wtis_league": prediction["league"],
        "wtis_matchup_date": prediction["matchup_date"],
        "wtis_prediction_winner": prediction["wtis_prediction_winner"],
        "wtis_confidence_score": prediction["wtis_confidence_score"],
        "wtis_analysis": prediction["wtis_analysis"],
        "wtis_factors_for": prediction["wtis_factors_for"],
        "wtis_factors_against": prediction["wtis_factors_against"],
        "what_nobody_saying": prediction.get("wtis_what_nobody_saying", ""),
        "wtis_image_brief_scene": prediction.get("wtis_image_brief_scene", ""),
        "wtis_ai_generated": True,
        "wtis_article_stage": prediction.get("wtis_article_stage", "matchup"),
        "wtis_ingested_at": prediction.get("matchup_date", ""),
    }

    log.info("Creating WP post: %s", prediction["matchup_title"])

    resp = requests.post(
        f"{WP_BASE_URL}/wp-json/wtis/v1/matchups",
        headers=_pipeline_headers(),
        json=payload,
        timeout=30,
    )
    resp.raise_for_status()

    post_id = resp.json().get("id")
    post_url = resp.json().get("link", "")
    log.info("Created post ID: %d — %s", post_id, post_url)
    return post_id, post_url


def _bluesky_stub(prediction, post_url):
    """Stub: Bluesky distribution fires at confidence score 6+ (scaled to 60+). Not active pre-launch."""
    confidence = prediction.get("wtis_confidence_score", 0)
    if confidence >= 60:
        log.info("[STUB] Bluesky: would post for %s (confidence %d) — social accounts not yet configured",
                 prediction["matchup_title"], confidence)
    return None


def _mailchimp_stub(prediction, post_url):
    """Stub: Mailchimp digest — not active pre-launch."""
    log.info("[STUB] Mailchimp: would queue %s for digest — not yet configured",
             prediction["matchup_title"])
    return None


def publish_one(prediction):
    """Full publish flow for a single prediction."""
    matchup = prediction["matchup_title"]

    # 1. Create WP post
    post_id, post_url = create_matchup_post(prediction)

    # 2. Generate and upload hero image
    try:
        local_path, filename = generate_hero_image(prediction)
        upload_hero_image(local_path, filename, post_id)
    except Exception as exc:
        log.error("Image generation/upload failed for %s: %s", matchup, exc)

    # 3. UTM URL for social — always explicit, never rely on LLM
    utm_url = f"{post_url}?utm_source=bluesky&utm_medium=social&utm_campaign=wtis-daily"

    # 4. Social stubs
    _bluesky_stub(prediction, utm_url)
    _mailchimp_stub(prediction, post_url)

    return post_id, post_url


def run():
    if not os.path.exists(FRAMED_PATH):
        raise FileNotFoundError(f"framed.json not found at {FRAMED_PATH} — run predict.py first")

    with open(FRAMED_PATH) as fh:
        framed = json.load(fh)

    if not framed:
        log.warning("framed.json is empty — nothing to publish")
        return []

    published = []
    for prediction in framed:
        try:
            post_id, post_url = publish_one(prediction)
            published.append({"matchup_title": prediction["matchup_title"], "post_id": post_id, "url": post_url})
        except Exception as exc:
            log.error("Failed to publish %s: %s", prediction.get("matchup_title"), exc)

    log.info("Published %d/%d matchups", len(published), len(framed))
    return published


if __name__ == "__main__":
    run()
