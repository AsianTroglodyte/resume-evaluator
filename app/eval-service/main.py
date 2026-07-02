import os
from keyword_match import calculate_keyword_match
import litellm
from dotenv import load_dotenv
from fastapi import FastAPI
from pydantic import BaseModel
from keyword_match import extract_job_keywords, resume_from_plain_text

load_dotenv()

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


@app.post("/evaluate")
async def post_item(payload: EvaluateRequest):

    system = "You are a resume coach, Give concise, actionable feedback."
    user = f"Resume:\n{payload.resume_text}\n\nJob description:\n{payload.job_description or '(none)'}"

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

    keyword_match = None
    jd_keywords = None
    if payload.job_description:
        jd_keywords = await extract_job_keywords(payload.job_description)
        keyword_match = calculate_keyword_match(
            resume_from_plain_text(payload.resume_text),
            jd_keywords
        )

    llm_response = response.choices[0].message.content


    return {
        "quality_eval": llm_response,
        "keyword_match": keyword_match,
        "jd_keywords": jd_keywords
    }

