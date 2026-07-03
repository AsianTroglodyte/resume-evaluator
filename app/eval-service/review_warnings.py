"""Deterministic completeness warnings for resumes (ported from RM resume wizard)."""

import re

from schemas import ResumeData

_EMAIL_RE = re.compile(r"[\w.+-]+@[\w.-]+\.\w+", re.IGNORECASE)
_PHONE_RE = re.compile(r"(?:\+?\d{1,3}[-.\s]?)?\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}")
_LINK_RE = re.compile(r"(linkedin\.com|github\.com|https?://)", re.IGNORECASE)
_SECTION_HEADER_RE = re.compile(
    r"^\s*(?:experience|work experience|employment|professional experience|"
    r"projects?(?:\s*&|\s+and)?|education|skills?|technical skills|summary|contact)\s*:?\s*$",
    re.IGNORECASE,
)
_EDUCATION_RE = re.compile(
    r"\b(?:education|university|college|institute|school|b\.?s\.?|m\.?s\.?|"
    r"bachelor|master|ph\.?d|degree|gpa)\b",
    re.IGNORECASE,
)
_EXPERIENCE_RE = re.compile(
    r"\b(?:experience|employment|internship|work history|professional experience|"
    r"work experience)\b",
    re.IGNORECASE,
)
_PROJECTS_RE = re.compile(
    r"\b(?:personal projects?|projects?|portfolio)\b",
    re.IGNORECASE,
)
_SKILLS_HEADER_RE = re.compile(
    r"\b(?:skills?|technical skills?|technologies|competencies|tools)\b",
    re.IGNORECASE,
)
_SKILL_LIST_RE = re.compile(
    r"(?:python|java|javascript|typescript|sql|git|aws|docker|react|linux|"
    r"c\+\+|c#|ruby|go\b|rust|kotlin|swift|html|css|node\.?js)",
    re.IGNORECASE,
)


def build_review_warnings(data: ResumeData) -> list[str]:
    """Gentle notes about useful resume facts that are missing (structured resume)."""
    warnings: list[str] = []
    info = data.personalInfo

    if not info.name.strip():
        warnings.append("Add your name — it's required to create your resume.")

    contact = [
        info.email,
        info.phone,
        info.linkedin or "",
        info.github or "",
        info.website or "",
    ]
    if not any(value.strip() for value in contact):
        warnings.append("Add at least one contact method (email, phone, or a link).")

    if not data.workExperience and not data.personalProjects:
        warnings.append("Add at least one experience, internship, or project.")

    if not data.education:
        warnings.append("Education is empty — skip only if that's intentional.")

    if not data.additional.technicalSkills:
        warnings.append("Skills are empty — add tools or technologies you've used.")

    return warnings


def build_review_warnings_from_text(resume_text: str) -> list[str]:
    """Same checks as build_review_warnings, inferred from pasted plain text."""
    text = resume_text.strip()
    if not text:
        return [
            "Add your name — it's required to create your resume.",
            "Add at least one contact method (email, phone, or a link).",
            "Add at least one experience, internship, or project.",
            "Education is empty — skip only if that's intentional.",
            "Skills are empty — add tools or technologies you've used.",
        ]

    warnings: list[str] = []

    if not _likely_has_name(text):
        warnings.append("Add your name — it's required to create your resume.")

    if not _has_contact(text):
        warnings.append("Add at least one contact method (email, phone, or a link).")

    if not (_has_experience_or_projects(text)):
        warnings.append("Add at least one experience, internship, or project.")

    if not _EDUCATION_RE.search(text):
        warnings.append("Education is empty — skip only if that's intentional.")

    if not _has_skills(text):
        warnings.append("Skills are empty — add tools or technologies you've used.")

    return warnings


def _likely_has_name(text: str) -> bool:
    for line in text.splitlines():
        stripped = line.strip()
        if not stripped or stripped[0] in "-•*#":
            continue
        if _SECTION_HEADER_RE.match(stripped):
            continue
        if _EMAIL_RE.search(stripped) or _PHONE_RE.search(stripped):
            continue
        if len(stripped) > 60:
            continue
        words = stripped.split()
        if 1 <= len(words) <= 6 and re.match(r"^[\w\s.'-]+$", stripped, re.IGNORECASE):
            return True
    return False


def _has_contact(text: str) -> bool:
    return bool(
        _EMAIL_RE.search(text) or _PHONE_RE.search(text) or _LINK_RE.search(text)
    )


def _has_experience_or_projects(text: str) -> bool:
    if _EXPERIENCE_RE.search(text) or _PROJECTS_RE.search(text):
        return True
    # Job-like lines: title at company, or date ranges with role context
    if re.search(r"\b(?:19|20)\d{2}\s*[-–—]\s*(?:present|current|(?:19|20)\d{2})\b", text, re.I):
        return True
    return False


def _has_skills(text: str) -> bool:
    if _SKILLS_HEADER_RE.search(text):
        return True
    for line in text.splitlines():
        if "," in line and len(_SKILL_LIST_RE.findall(line)) >= 2:
            return True
    return len(_SKILL_LIST_RE.findall(text)) >= 3
