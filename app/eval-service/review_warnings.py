"""Deterministic completeness warnings for resumes (ported from RM resume wizard)."""

from schemas import ResumeData


def build_review_warnings(data: ResumeData) -> list[str]:
    """Gentle notes about useful resume facts that are missing."""
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
