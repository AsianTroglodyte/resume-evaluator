"""Enrichment analyze — structured resume feedback via LLM."""

import logging
from typing import Any

from llm import complete_json
from prompts.enrichment import ANALYZE_RESUME_PROMPT
from schemas.enrichment import AnalysisResponse, EnrichmentItem, EnrichmentQuestion

logger = logging.getLogger(__name__)


async def analyze_resume_enrichment(
    resume_text: str,
    output_language: str = "English",
) -> dict[str, Any]:
    """Analyze resume text for weak experience/project bullets and follow-up questions.

    Adapted from Resume-Matcher POST /enrichment/analyze (analyze step only).
    """
    prompt = ANALYZE_RESUME_PROMPT.format(
        resume_text=resume_text,
        output_language=output_language,
    )

    result = await complete_json(
        prompt=prompt,
        system_prompt="You are a professional resume analyst.",
        max_tokens=8192,
        schema_type="enrichment",
        retries=3,
    )

    items_to_enrich = [
        EnrichmentItem(
            item_id=item.get("item_id", f"item_{i}"),
            item_type=item.get("item_type", "experience"),
            title=item.get("title", ""),
            subtitle=item.get("subtitle"),
            current_description=item.get("current_description", []),
            weakness_reason=item.get("weakness_reason", ""),
        )
        for i, item in enumerate(result.get("items_to_enrich", []))
    ]

    questions = [
        EnrichmentQuestion(
            question_id=q.get("question_id", f"q_{i}"),
            item_id=q.get("item_id", ""),
            question=q.get("question", ""),
            placeholder=q.get("placeholder", ""),
        )
        for i, q in enumerate(result.get("questions", []))
    ]

    validated = AnalysisResponse(
        items_to_enrich=items_to_enrich,
        questions=questions,
        analysis_summary=result.get("analysis_summary"),
    )
    return validated.model_dump()
