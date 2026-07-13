import asyncio
import logging

from ai_phrases import detect_ai_phrases
from dotenv import load_dotenv
from enrichment_analyze import analyze_resume_enrichment
from fastapi import FastAPI
from keyword_match import (
    analyze_keyword_gaps,
    extract_job_keywords,
    resume_from_plain_text,
)
from parser import parse_resume_to_json
from pydantic import BaseModel
from review_warnings import build_review_warnings
from schemas import ResumeData

load_dotenv()

logger = logging.getLogger(__name__)

app = FastAPI()

class EvaluateRequest(BaseModel):
    resume_text: str
    job_description: str | None = None


@app.post("/evaluate")
async def post_item(payload: EvaluateRequest):

    # logging.error("bruh");
    # print('bruh')

    enrichment_task = asyncio.create_task(
        analyze_resume_enrichment(payload.resume_text)
    )
    parse_task = asyncio.create_task(parse_resume_to_json(payload.resume_text))

    enrichment_result, parse_result = await asyncio.gather(
        enrichment_task,
        parse_task,
        return_exceptions=True,
    )

    enrichment = None
    if isinstance(enrichment_result, Exception):
        logger.exception("Enrichment analysis failed", exc_info=enrichment_result)
    else:
        enrichment = enrichment_result

    parsed_resume = None
    warnings: list[str] = []
    if isinstance(parse_result, Exception):
        logger.exception("Resume parse failed", exc_info=parse_result)
    else:
        parsed_resume = parse_result
        warnings = build_review_warnings(ResumeData.model_validate(parsed_resume))

    keyword_match = None
    matched_keywords = None
    missing_keywords = None
    jd_keywords = None
    if payload.job_description:
        jd_keywords = await extract_job_keywords(payload.job_description)
        resume_for_match = parsed_resume or resume_from_plain_text(payload.resume_text)
        gaps = analyze_keyword_gaps(resume_for_match, jd_keywords)
        keyword_match = gaps["match_percent"]
        matched_keywords = gaps["matched_keywords"]
        missing_keywords = gaps["missing_keywords"]

    ai_phrases = detect_ai_phrases(
        payload.resume_text,
        payload.job_description or "",
    )

    return {
        "enrichment": enrichment,
        "keyword_match": keyword_match,
        "matched_keywords": matched_keywords,
        "missing_keywords": missing_keywords,
        "ai_phrases": ai_phrases,
        "warnings": warnings,
        "jd_keywords": jd_keywords,
    }
