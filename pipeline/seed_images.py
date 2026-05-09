"""
seed_images.py — One-time script: generate and attach hero images to seeded posts.
Calls OpenAI gpt-image-1, uploads to WP media library, sets as featured image.

Usage: python3 pipeline/seed_images.py

API notes (updated):
- gpt-image-1 does not accept response_format param; returns b64_json by default
- Supported landscape size: 1536x1024 (not 1792x1024)
- Player names in prompts trigger content policy errors; use stadium/atmosphere briefs only
"""

import base64
import logging
import os
import time

import requests
from dotenv import load_dotenv

load_dotenv(os.path.join(os.path.dirname(__file__), ".env.lando"))

logging.basicConfig(level=logging.INFO, format="%(levelname)s %(message)s")
log = logging.getLogger(__name__)

WP_BASE_URL = os.environ["WTIS_SITE_URL"].rstrip("/")
PIPELINE_API_KEY = os.environ["WTIS_PIPELINE_API_KEY"]
WP_USERNAME = os.environ["WTIS_WP_USERNAME"]
WP_APP_PASSWORD = os.environ["WTIS_WP_APP_PASSWORD"]
OPENAI_API_KEY = os.environ["OPENAI_API_KEY"]

TEMP_DIR = os.path.join(os.path.dirname(__file__), "temp_images")
os.makedirs(TEMP_DIR, exist_ok=True)

# Stadium/atmosphere-only briefs — no player names (triggers OpenAI content policy)
BRIEFS = {
    14: "Empty SoFi Stadium at dusk bathed in golden floodlight, pristine green pitch, American and Portuguese flags draped over empty seats, dramatic sky",
    15: "Packed night stadium with Argentine sky-blue-and-white and Moroccan red-and-green crowd sections facing off, electric atmosphere under bright floodlights, confetti in the air",
    16: "Massive stadium at night under storm clouds, green pitch glowing under floodlights, Brazilian green and German black crowd sections, scoreboard lit up",
    17: "Sun-drenched stadium at golden hour, French blue and English red crowd sections in full voice, shadows stretching across the pitch, flags waving",
    18: "Packed Mediterranean-feeling stadium in blazing afternoon light, Spanish red and Dutch orange sections, pristine pitch, dramatic low-angle shot",
    19: "Estadio Azteca under brilliant sunshine, packed with Mexican green and Uruguayan sky-blue sections, confetti mid-air, dramatic wide-angle perspective",
    20: "Sold-out MetLife Stadium at night, American and Argentine crowd sections under blazing floodlights, vast stadium atmosphere, fireworks above upper deck",
    21: "Quarter-final atmosphere in a colossal stadium, Brazilian green and French blue crowd sections, floodlit pitch at night, giant screen showing score",
    22: "Semi-final atmosphere in a vast stadium at dusk, Argentine blue-and-white and English red-and-white fan sections, dramatic clouds, flags and scarves raised",
    23: "World Cup final atmosphere in a packed MetLife Stadium, Brazilian green and American red-white-blue sections, trophy silhouette on jumbotron, fireworks, confetti storm",
}

POST_IDS = [14, 15, 16, 17, 18, 19, 20, 21, 22, 23]


def _basic_auth_headers():
    credentials = base64.b64encode(
        f"{WP_USERNAME}:{WP_APP_PASSWORD}".encode()
    ).decode()
    return {"Authorization": f"Basic {credentials}"}


def _pipeline_headers():
    return {"X-WTIS-Key": PIPELINE_API_KEY}


def fetch_post_title(post_id):
    resp = requests.get(
        f"{WP_BASE_URL}/wp-json/wp/v2/posts/{post_id}",
        headers=_basic_auth_headers(),
        params={"_fields": "id,title"},
        timeout=15,
    )
    resp.raise_for_status()
    return resp.json().get("title", {}).get("rendered", f"Post {post_id}")


def generate_image(brief, post_id):
    """Call gpt-image-1, decode base64, save to temp file."""
    prompt = (
        f"Photorealistic sports atmosphere. {brief} "
        "Cinematic landscape composition. No people, no faces, no text, no logos, no overlays."
    )

    log.info("  Generating image...")

    resp = requests.post(
        "https://api.openai.com/v1/images/generations",
        headers={
            "Authorization": f"Bearer {OPENAI_API_KEY}",
            "Content-Type": "application/json",
        },
        json={
            "model": "gpt-image-1",
            "prompt": prompt,
            "n": 1,
            "size": "1536x1024",
        },
        timeout=120,
    )
    resp.raise_for_status()

    b64_data = resp.json()["data"][0]["b64_json"]
    image_bytes = base64.b64decode(b64_data)

    filename = f"wtis-post-{post_id}-{int(time.time())}.png"
    local_path = os.path.join(TEMP_DIR, filename)
    with open(local_path, "wb") as fh:
        fh.write(image_bytes)

    log.info("  Saved: %s (%d bytes)", filename, len(image_bytes))
    return local_path, filename


def upload_and_attach(local_path, filename, post_id):
    """Upload to WP media library, set as featured image via wp/v2/posts, verify, clean up."""
    with open(local_path, "rb") as fh:
        image_bytes = fh.read()

    upload_resp = requests.post(
        f"{WP_BASE_URL}/wp-json/wp/v2/media",
        headers={
            **_basic_auth_headers(),
            "Content-Disposition": f'attachment; filename="{filename}"',
            "Content-Type": "image/png",
        },
        data=image_bytes,
        timeout=60,
    )
    upload_resp.raise_for_status()
    media_id = upload_resp.json()["id"]
    log.info("  Uploaded media ID: %d", media_id)

    # Set featured_media via standard WP REST API (Basic auth)
    # Note: the custom /image endpoint expects a multipart file upload, not a media ID
    attach_resp = requests.post(
        f"{WP_BASE_URL}/wp-json/wp/v2/posts/{post_id}",
        headers={**_basic_auth_headers(), "Content-Type": "application/json"},
        json={"featured_media": media_id},
        timeout=30,
    )
    attach_resp.raise_for_status()
    confirmed_id = attach_resp.json().get("featured_media")

    os.remove(local_path)
    return media_id, confirmed_id


def run():
    results = []

    for i, post_id in enumerate(POST_IDS, 1):
        log.info("=== [%d/10] Post ID %d ===", i, post_id)

        try:
            title = fetch_post_title(post_id)
            log.info("  %s", title)

            brief = BRIEFS[post_id]
            local_path, filename = generate_image(brief, post_id)
            media_id, confirmed_id = upload_and_attach(local_path, filename, post_id)

            verified = confirmed_id == media_id
            results.append({
                "post_id": post_id,
                "title": title,
                "media_id": media_id,
                "verified": verified,
                "status": "OK",
            })
            log.info("  Done: media_id=%d verified=%s", media_id, verified)

        except Exception as exc:
            log.error("  FAILED: %s", exc)
            results.append({
                "post_id": post_id,
                "title": f"Post {post_id}",
                "status": f"FAILED: {exc}",
            })

        if i < len(POST_IDS):
            time.sleep(4)

    print("\n=== IMAGE SEED RESULTS ===")
    for r in results:
        if r["status"] == "OK":
            print(f"  OK   [post {r['post_id']:>2}] media={r['media_id']} verified={r['verified']}  {r['title']}")
        else:
            print(f"  FAIL [post {r['post_id']:>2}] {r['status']}")

    ok = sum(1 for r in results if r["status"] == "OK")
    print(f"\n{ok}/{len(results)} images attached successfully")


if __name__ == "__main__":
    run()
