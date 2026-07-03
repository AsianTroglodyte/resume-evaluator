import json
import logging
import re
from typing import Any

from llm import complete_json
from prompts import EXTRACT_KEYWORDS_PROMPT

logger = logging.getLogger(__name__)

_INJECTION_PATTERNS = [
    r"ignore\s+(all\s+)?previous\s+instructions",
    r"disregard\s+(all\s+)?above",
    r"forget\s+(everything|all)",
    r"new\s+instructions?:",
    r"system\s*:",
    r"<\s*/?\s*system\s*>",
    r"\[\s*INST\s*\]",
    r"\[\s*/\s*INST\s*\]",
]


def resume_from_plain_text(text: str) -> dict:
    return {"summary": text}


def _collect_jd_keywords(jd_keywords: dict[str, Any]) -> list[str]:
    """Collect unique JD keywords in stable order (required, preferred, general)."""
    seen: set[str] = set()
    ordered: list[str] = []
    for field in ("required_skills", "preferred_skills", "keywords"):
        values = jd_keywords.get(field, [])
        if not isinstance(values, list):
            continue
        for keyword in values:
            if not isinstance(keyword, str):
                continue
            cleaned = keyword.strip()
            if not cleaned:
                continue
            fold = cleaned.casefold()
            if fold in seen:
                continue
            seen.add(fold)
            ordered.append(cleaned)
    return ordered


def analyze_keyword_gaps(
    resume: dict[str, Any],
    jd_keywords: dict[str, Any],
) -> dict[str, Any]:
    """Analyze which JD keywords appear in the resume.

    Adapted from Resume-Matcher refiner.analyze_keyword_gaps (single resume,
    no master/tailored injectable split).

    Returns:
        match_percent, matched_keywords, missing_keywords
    """
    resume_text = _extract_all_text(resume).lower()
    all_keywords = _collect_jd_keywords(jd_keywords)

    if not all_keywords:
        logger.warning("No keywords found in job description")
        return {
            "match_percent": 0.0,
            "matched_keywords": [],
            "missing_keywords": [],
        }

    matched = [kw for kw in all_keywords if _keyword_in_text(kw, resume_text)]
    missing = [kw for kw in all_keywords if not _keyword_in_text(kw, resume_text)]
    match_percent = (len(matched) / len(all_keywords)) * 100

    return {
        "match_percent": match_percent,
        "matched_keywords": matched,
        "missing_keywords": missing,
    }


def calculate_keyword_match(
    resume: dict[str, Any],
    jd_keywords: dict[str, Any],
) -> float:
    """Calculate keyword match percentage.

    Args:
        resume: Resume data dictionary
        jd_keywords: Extracted job keywords

    Returns:
        Match percentage (0.0 to 100.0)
    """
    return analyze_keyword_gaps(resume, jd_keywords)["match_percent"]


def _keyword_in_text(keyword: str, text: str) -> bool:
    """Check if keyword exists as a whole term in text.

    SVC-010: Uses term boundaries instead of substring matching to avoid
    false positives like 'python' matching 'pythonic' or 'go' matching 'going'.
    """
    escaped = re.escape(keyword.strip().lower())
    if not escaped:
        return False
    pattern = rf"(?<!\w){escaped}(?!\w)"
    return bool(re.search(pattern, text.lower()))


def _extract_all_text(data: dict[str, Any]) -> str:
    """Extract all text content from resume data for keyword matching.

    SVC-011: Uses caching to avoid repeated extraction on same resume data.

    Args:
        data: Resume data dictionary

    Returns:
        Concatenated text from all resume sections
    """
    # Create a cache key from the data
    data_json = json.dumps(data, sort_keys=True, default=str)
    return _extract_all_text_cached(data_json)


def _extract_all_text_cached(data_json: str) -> str:
    """Cached implementation of text extraction.

    SVC-011: LRU cache avoids re-extracting text from the same resume
    multiple times during a single refinement pass.
    """
    data = json.loads(data_json)
    parts: list[str] = []

    # Summary
    if data.get("summary"):
        parts.append(str(data["summary"]))

    # Work experience
    for exp in data.get("workExperience", []):
        if isinstance(exp, dict):
            parts.append(str(exp.get("title", "")))
            parts.append(str(exp.get("company", "")))
            desc = exp.get("description", [])
            if isinstance(desc, list):
                parts.extend(str(d) for d in desc)

    # Education
    for edu in data.get("education", []):
        if isinstance(edu, dict):
            parts.append(str(edu.get("degree", "")))
            parts.append(str(edu.get("institution", "")))
            if edu.get("description"):
                parts.append(str(edu["description"]))

    # Projects
    for proj in data.get("personalProjects", []):
        if isinstance(proj, dict):
            parts.append(str(proj.get("name", "")))
            parts.append(str(proj.get("role", "")))
            desc = proj.get("description", [])
            if isinstance(desc, list):
                parts.extend(str(d) for d in desc)

    # Additional
    additional = data.get("additional", {})
    if isinstance(additional, dict):
        skills = additional.get("technicalSkills", [])
        if isinstance(skills, list):
            parts.extend(str(s) for s in skills)
        certs = additional.get("certificationsTraining", [])
        if isinstance(certs, list):
            parts.extend(str(c) for c in certs)
        languages = additional.get("languages", [])
        if isinstance(languages, list):
            parts.extend(str(lang) for lang in languages)
        awards = additional.get("awards", [])
        if isinstance(awards, list):
            parts.extend(str(a) for a in awards)

    # Custom sections
    custom_sections = data.get("customSections", {})
    if isinstance(custom_sections, dict):
        for section in custom_sections.values():
            if not isinstance(section, dict):
                continue
            section_type = section.get("sectionType", "")
            if section_type == "itemList":
                for item in section.get("items", []):
                    if isinstance(item, dict):
                        parts.append(str(item.get("title", "")))
                        parts.append(str(item.get("subtitle", "")))
                        desc = item.get("description", [])
                        if isinstance(desc, list):
                            parts.extend(str(d) for d in desc)
                        elif isinstance(desc, str):
                            parts.append(desc)
            elif section_type == "text":
                text = section.get("text", "")
                if isinstance(text, str):
                    parts.append(text)
            elif section_type == "stringList":
                items = section.get("strings", [])
                if isinstance(items, list):
                    parts.extend(str(i) for i in items)

    return " ".join(p for p in parts if p)


async def extract_job_keywords(job_description: str) -> dict[str, Any]:
    """Extract keywords and requirements from job description.

    Args:
        job_description: Raw job description text

    Returns:
        Structured keywords and requirements
    """
    # LLM-011: Sanitize job description before using in prompt
    sanitized_jd = _sanitize_user_input(job_description)
    prompt = EXTRACT_KEYWORDS_PROMPT.format(job_description=sanitized_jd)

    return await complete_json(
        prompt=prompt,
        system_prompt="You are an expert job description analyzer.",
        schema_type="keywords",
    )

def _sanitize_user_input(text: str) -> str:
    """Sanitize user input to prevent prompt injection."""
    sanitized = text
    for pattern in _INJECTION_PATTERNS:
        sanitized = re.sub(pattern, "[REDACTED]", sanitized, flags=re.IGNORECASE)
    return sanitized

