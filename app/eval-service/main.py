import os

import litellm
from dotenv import load_dotenv
from fastapi import FastAPI
from pydantic import BaseModel

load_dotenv()

app = FastAPI()


async def evaluate_resume(resume_text: str, job_description: str | None) -> str:
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
        temperature=0.3,
    )

    return response.choices[0].message.content


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
    llm_response = await evaluate_resume(payload.resume_text, payload.job_description)

    return {
        "quality_eval": llm_response,
    }
