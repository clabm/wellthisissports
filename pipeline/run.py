"""
run.py — Pipeline orchestrator. Runs all stages in sequence with error handling.
Usage: python pipeline/run.py
"""

import logging
import sys
import time

from dotenv import load_dotenv

load_dotenv()

logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s %(levelname)s %(message)s",
    datefmt="%Y-%m-%dT%H:%M:%S",
)
log = logging.getLogger(__name__)


def run():
    start = time.time()
    log.info("=== WTIS Pipeline starting ===")

    # Stage 1: Ingest
    log.info("--- Stage 1: Ingest ---")
    try:
        import ingest
        candidates = ingest.run()
        log.info("Ingest complete: %d candidate(s)", len(candidates))
    except Exception as exc:
        log.error("Ingest FAILED: %s", exc, exc_info=True)
        sys.exit(1)

    if not candidates:
        log.info("No candidates — pipeline complete (nothing to predict or publish)")
        return

    # Stage 2: Predict
    log.info("--- Stage 2: Predict ---")
    try:
        import predict
        framed = predict.run()
        log.info("Predict complete: %d prediction(s)", len(framed))
    except Exception as exc:
        log.error("Predict FAILED: %s", exc, exc_info=True)
        sys.exit(1)

    if not framed:
        log.info("No predictions — stopping before publish")
        return

    # Stage 3: Publish
    log.info("--- Stage 3: Publish ---")
    try:
        import publish
        published = publish.run()
        log.info("Publish complete: %d post(s) published", len(published))
    except Exception as exc:
        log.error("Publish FAILED: %s", exc, exc_info=True)
        sys.exit(1)

    # Stage 4: Card + Ledger
    log.info("--- Stage 4: Card ---")
    try:
        import card

        # Attach prediction data to published results for card scoring
        framed_by_title = {p["matchup_title"]: p for p in framed}
        enriched = []
        for result in published:
            title = result.get("matchup_title", "")
            enriched.append({
                **result,
                "prediction": framed_by_title.get(title, {}),
            })

        card.run(enriched)
        log.info("Card stage complete")
    except Exception as exc:
        log.error("Card FAILED: %s", exc, exc_info=True)
        # Cards failing is non-fatal — posts are already published

    elapsed = time.time() - start
    log.info("=== WTIS Pipeline complete in %.1fs ===", elapsed)
    log.info("Published: %d matchup(s)", len(published))


if __name__ == "__main__":
    run()
