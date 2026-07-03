import asyncio
import logging
import os

import litellm
from ai_phrases import detect_ai_phrases
from dotenv import load_dotenv
from enrichment_analyze import analyze_resume_enrichment
from fastapi import FastAPI
from keyword_match import (
    analyze_keyword_gaps,
    extract_job_keywords,
    resume_from_plain_text,
)
from pydantic import BaseModel

load_dotenv()

logger = logging.getLogger(__name__)

app = FastAPI()


@app.get("/")
def read_root():
    return {"Hello": "World"}


@app.get("/items/{item_id}")
def read_item(item_id: int, q: str | None = None):
    return {"item_id": item_id, "q": q}


class EvaluateRequest(BaseModel):
    resume_text: str
    job_description: str | None = None


async def _quality_eval(resume_text: str, job_description: str | None) -> str:
    system = "You are a resume coach, Give concise, actionable feedback."
    user = f"Resume:\n{resume_text}\n\nJob description:\n{job_description or '(none)'}"

    response = await litellm.acompletion(
        model=os.getenv("LLM_MODEL", "gpt-4o-mini"),
        messages=[
            {"role": "system", "content": system},
            {"role": "user", "content": user},
        ],
        api_key=os.getenv("OPENAI_API_KEY"),
        max_tokens=1024,
        temperature=0.1,
    )
    return response.choices[0].message.content or ""


@app.post("/evaluate")
async def post_item(payload: EvaluateRequest):
    quality_task = asyncio.create_task(
        _quality_eval(payload.resume_text, payload.job_description)
    )
    enrichment_task = asyncio.create_task(
        analyze_resume_enrichment(payload.resume_text)
    )

    quality_eval = ""
    enrichment = None

    quality_result, enrichment_result = await asyncio.gather(
        quality_task,
        enrichment_task,
        return_exceptions=True,
    )

    if isinstance(quality_result, Exception):
        logger.exception("Quality evaluation failed", exc_info=quality_result)
        raise quality_result

    quality_eval = quality_result

    if isinstance(enrichment_result, Exception):
        logger.exception("Enrichment analysis failed", exc_info=enrichment_result)
    else:
        enrichment = enrichment_result

    keyword_match = None
    matched_keywords = None
    missing_keywords = None
    jd_keywords = None
    if payload.job_description:
        jd_keywords = await extract_job_keywords(payload.job_description)
        gaps = analyze_keyword_gaps(
            resume_from_plain_text(payload.resume_text),
            jd_keywords,
        )
        keyword_match = gaps["match_percent"]
        matched_keywords = gaps["matched_keywords"]
        missing_keywords = gaps["missing_keywords"]

    ai_phrases = detect_ai_phrases(
        payload.resume_text,
        payload.job_description or "",
    )

    return {
        "quality_eval": quality_eval,
        "enrichment": enrichment,
        "keyword_match": keyword_match,
        "matched_keywords": matched_keywords,
        "missing_keywords": missing_keywords,
        "ai_phrases": ai_phrases,
        "jd_keywords": jd_keywords,
    }
