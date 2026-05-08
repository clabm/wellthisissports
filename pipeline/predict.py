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

load_dotenv()

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

Produce your response in EXACTLY this format (use these exact section headers):

WINNER: [team name]
CONFIDENCE: [number 1-100]
ANALYSIS: [500-600 words analyzing this matchup. Name specific stats, form, history. No em dashes. No hedging openers like "In this matchup" or "When it comes to". Get straight to the point.]
FACTORS FOR: [top 3 reasons the predicted winner wins, pipe-separated, e.g. factor one|factor two|factor three]
FACTORS AGAINST: [top 3 risks for the predicted winner, pipe-separated]
WHAT NOBODY IS SAYING: [one paragraph, the angle everyone is missing]
IMAGE BRIEF: [one sentence describing a dramatic, cinematic visual scene for this matchup's hero image. No team logos, no text overlay. Pure visual scene.]

Rules:
- Confidence score must be justified by the analysis
- Name specific stats and facts, not vague claims
- No em dashes anywhere in your response
- No hedging language
- Analysis must be 500-600 words
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
            pattern = rf"^{label}:\s*(.+?)(?=\n[A-Z ]+:|$)"
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
        max_tokens=2048,
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
