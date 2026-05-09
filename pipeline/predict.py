"""
predict.py — Stage 2: Run Claude Haiku predictions on candidates.json,
write framed.json with full prediction output per matchup.
"""

import json
import logging
import os
import re

import anthropic
from dotenv import load_dotenv

load_dotenv(os.path.join(os.path.dirname(__file__), ".env.lando"))

logging.basicConfig(level=logging.INFO, format="%(levelname)s %(message)s")
log = logging.getLogger(__name__)

client = anthropic.Anthropic(api_key=os.environ["ANTHROPIC_API_KEY"])

CANDIDATES_PATH = os.path.join(os.path.dirname(__file__), "candidates.json")
FRAMED_PATH = os.path.join(os.path.dirname(__file__), "framed.json")

PREDICTION_PROMPT = """\
You are a neutral AI sports analyst. Generate a complete prediction for this matchup.

MATCHUP: {home} vs {away}
COMPETITION: {league}
DATE: {date}
ROUND: {round}

CRITICAL REQUIREMENT: The ANALYSIS section MUST be 500-600 words. Count your words carefully before responding. A short analysis is a failure. Write the full 500-600 words even if it feels long.

Produce your response in EXACTLY this format (use these exact section headers):

WINNER: [team name]
CONFIDENCE: [number 1-100]
ANALYSIS: [Your analysis MUST be 500-600 words. Count carefully before responding. Cover: recent form (last 5 matches with specific results), head-to-head history (last 3-5 meetings with scores), key players and their current form, tactical matchup, injuries or suspensions, home/away record, motivation and stakes. Name specific stats, scores, and player names. No em dashes. No hedging openers like "In this matchup" or "When it comes to". Start directly with a substantive observation about the teams.]
FACTORS FOR: [top 3 reasons the predicted winner wins, pipe-separated, e.g. factor one|factor two|factor three]
FACTORS AGAINST: [top 3 risks for the predicted winner, pipe-separated]
WHAT NOBODY IS SAYING: [one substantive paragraph of 3-5 sentences, the angle everyone is missing. Must be a specific, non-obvious insight, not a generic observation.]
IMAGE BRIEF: [one sentence describing a dramatic, cinematic visual scene for this matchup's hero image. No team logos, no text overlay. Pure visual scene.]

Rules:
- ANALYSIS must be exactly 500-600 words. This is not optional.
- Confidence score must be justified by the analysis
- Name specific stats, scores, and player names — not vague claims
- No em dashes anywhere in your response
- No hedging language
- Pipe-separate the factors, do not number them
"""


def build_prompt(candidate):
    return PREDICTION_PROMPT.format(
        home=candidate["team_home"],
        away=candidate["team_away"],
        league=candidate["league"],
        date=candidate["matchup_date"],
        round=candidate.get("round", ""),
    )


def parse_prediction(text, candidate):
    """Parse structured prediction output into a dict."""

    def extract(label, multiline=False):
        if multiline:
            # \Z = end of string; $ in MULTILINE matches end of every line (too greedy)
            pattern = rf"^{label}:\s*(.+?)(?=\n[A-Z ]+:|\Z)"
            m = re.search(pattern, text, re.MULTILINE | re.DOTALL)
        else:
            pattern = rf"^{label}:\s*(.+)$"
            m = re.search(pattern, text, re.MULTILINE)
        return m.group(1).strip() if m else ""

    winner = extract("WINNER")
    confidence_raw = extract("CONFIDENCE")
    analysis = extract("ANALYSIS", multiline=True)
    factors_for_raw = extract("FACTORS FOR")
    factors_against_raw = extract("FACTORS AGAINST")
    what_nobody_saying = extract("WHAT NOBODY IS SAYING", multiline=True)
    image_brief = extract("IMAGE BRIEF")

    # Parse confidence as int
    try:
        confidence = int(re.sub(r"[^\d]", "", confidence_raw))
        confidence = max(1, min(100, confidence))
    except (ValueError, TypeError):
        confidence = 60
        log.warning("Could not parse confidence for %s, defaulting to 60", candidate["matchup_title"])

    return {
        **candidate,
        "wtis_prediction_winner": winner,
        "wtis_confidence_score": confidence,
        "wtis_analysis": analysis,
        "wtis_factors_for": factors_for_raw,
        "wtis_factors_against": factors_against_raw,
        "wtis_what_nobody_saying": what_nobody_saying,
        "wtis_image_brief_scene": image_brief,
        "wtis_ai_generated": True,
        "wtis_article_stage": "matchup",
    }


def predict_one(candidate):
    log.info("Predicting: %s", candidate["matchup_title"])

    message = client.messages.create(
        model="claude-haiku-4-5-20251001",
        max_tokens=3000,
        messages=[
            {"role": "user", "content": build_prompt(candidate)}
        ],
    )

    text = message.content[0].text
    prediction = parse_prediction(text, candidate)

    log.info(
        "  -> %s (confidence: %d)",
        prediction["wtis_prediction_winner"],
        prediction["wtis_confidence_score"],
    )
    return prediction


def run():
    if not os.path.exists(CANDIDATES_PATH):
        raise FileNotFoundError(f"candidates.json not found at {CANDIDATES_PATH} — run ingest.py first")

    with open(CANDIDATES_PATH) as fh:
        candidates = json.load(fh)

    if not candidates:
        log.warning("candidates.json is empty — nothing to predict")
        with open(FRAMED_PATH, "w") as fh:
            json.dump([], fh, indent=2)
        return []

    framed = []
    for candidate in candidates:
        try:
            prediction = predict_one(candidate)
            framed.append(prediction)
        except Exception as exc:
            log.error("Failed to predict %s: %s", candidate.get("matchup_title"), exc)

    log.info("Writing %d predictions to %s", len(framed), FRAMED_PATH)
    with open(FRAMED_PATH, "w") as fh:
        json.dump(framed, fh, indent=2)

    return framed


if __name__ == "__main__":
    run()
