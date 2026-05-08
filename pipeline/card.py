"""
card.py — Stage 4: Score gate, card type selection, social distribution.
Slack + Buffer hooks stubbed until pre-launch social accounts are configured.
"""

import logging
import os

from dotenv import load_dotenv

load_dotenv()

logging.basicConfig(level=logging.INFO, format="%(levelname)s %(message)s")
log = logging.getLogger(__name__)

# Score gate: cards fire at 8+, Bluesky at 6+
CARD_SCORE_GATE = 8

CARD_TYPES = [
    "THE PICK",
    "UPSET ALERT",
    "LOCK OF THE WEEK",
    "DON'T BET THIS",
    "THIS JUST SHIFTED",
    "BOTH SIDES ARE WRONG",
]


def score_prediction(prediction):
    """
    Derive a 0-10 card score from the confidence score.
    Confidence 80-100 -> 8-10 (card fires), 60-79 -> 6-7 (Bluesky only), <60 -> no card.
    """
    confidence = prediction.get("wtis_confidence_score", 0)
    score = round(confidence / 10)
    return max(0, min(10, score))


def select_card_type(prediction, score):
    """
    Select card type based on prediction characteristics.
    Card type protocol: in a live implementation, Claude Sonnet emits
    CARD_TYPE: {type} as the first line before JSON. This stub uses heuristics.
    """
    confidence = prediction.get("wtis_confidence_score", 0)
    winner = prediction.get("wtis_prediction_winner", "")
    home = prediction.get("team_home", "")

    # Upset alert: predicted winner is away team with moderate confidence
    if winner != home and 60 <= confidence <= 74:
        return "UPSET ALERT"

    # Lock: very high confidence
    if confidence >= 85:
        return "LOCK OF THE WEEK"

    # Don't bet: low confidence that still clears the gate
    if confidence < 65:
        return "DON'T BET THIS"

    return "THE PICK"


def _slack_stub(card_type, prediction, post_url):
    """Stub: Slack #ai-feed notification. Not active pre-launch."""
    log.info("[STUB] Slack: would post %s card for %s to #ai-feed — not configured",
             card_type, prediction.get("matchup_title"))


def _buffer_stub(card_type, prediction, post_url):
    """Stub: Buffer/Facebook distribution. Not active pre-launch."""
    utm_url = f"{post_url}?utm_source=facebook&utm_medium=social&utm_campaign=wtis-card"
    log.info("[STUB] Buffer: would post %s card for %s — not configured",
             card_type, prediction.get("matchup_title"))
    log.info("[STUB] Buffer UTM URL: %s", utm_url)


def process_card(prediction, post_url):
    """
    Evaluate a prediction for card distribution.
    Returns card_type if card fires, None otherwise.
    """
    score = score_prediction(prediction)
    matchup = prediction.get("matchup_title", "")

    log.info("Card score for %s: %d/10", matchup, score)

    if score < CARD_SCORE_GATE:
        log.info("Score %d below gate %d — no card for %s", score, CARD_SCORE_GATE, matchup)
        return None

    card_type = select_card_type(prediction, score)
    log.info("Card type selected: %s (score %d)", card_type, score)

    _slack_stub(card_type, prediction, post_url)
    _buffer_stub(card_type, prediction, post_url)

    return card_type


def run(published_results):
    """
    published_results: list of {"matchup_title", "post_id", "url", "prediction"} dicts
    as returned by publish.run() with prediction data attached.
    """
    if not published_results:
        log.info("No published results to process for cards")
        return

    for result in published_results:
        prediction = result.get("prediction", {})
        post_url = result.get("url", "")
        if not prediction or not post_url:
            continue
        process_card(prediction, post_url)
