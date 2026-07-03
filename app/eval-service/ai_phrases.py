"""Detect AI-sounding phrases in resume text (feedback only; no mutation)."""

from typing import Any

from prompts.refinement import AI_PHRASE_BLACKLIST, AI_PHRASE_REPLACEMENTS


def detect_ai_phrases(
    text: str,
    job_description: str = "",
) -> list[dict[str, Any]]:
    """Flag blacklisted phrases present in resume text.

    Phrases that also appear in the job description are skipped (RM parity:
    the JD may legitimately use terms like "stakeholder" or "scalable").

    Overlapping phrases (e.g. "paradigm" and "paradigm shift") are both
    reported when present — intentional for a lightweight heuristic.

    Returns:
        List of {"phrase": str, "suggestion": str} dicts. suggestion may be empty.
    """
    text_lower = text.lower()
    jd_lower = job_description.lower()

    jd_protected = {
        phrase.lower()
        for phrase in AI_PHRASE_BLACKLIST
        if phrase.lower() in jd_lower
    }

    found: list[dict[str, Any]] = []
    seen: set[str] = set()

    for phrase in AI_PHRASE_BLACKLIST:
        phrase_lower = phrase.lower()
        if phrase_lower in jd_protected:
            continue
        if phrase_lower not in text_lower:
            continue
        if phrase_lower in seen:
            continue
        seen.add(phrase_lower)
        found.append({
            "phrase": phrase,
            "suggestion": AI_PHRASE_REPLACEMENTS.get(phrase_lower, ""),
        })

    return found
