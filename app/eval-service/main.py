from fastapi import FastAPI
from pydantic import BaseModel

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
def post_item(payload: EvaluateRequest):

    return {
        'resume_text' : payload.resume_text,
        'job_description' : payload.job_description,
    }


# 'id' : 102,
# 'status' : 'completed',
# 'job_description_label' : 'Software Engineering Intern — RiverTech',
# 'match_percent' : 74,
# 'keyword_match' : 68,
# 'quality_eval' : 'Solid structure and relevant coursework. Add more quantified project outcomes and mirror the posting\'s language around REST APIs and Git workflows.',
# 'created_at' : 'Mar 22, 2026 · 4:30 PM',
# 'resume_text_preview' : "Alex Kim\nComputer Science, Junior\n\nExperience\n— Teaching Assistant, Data Structures...",
# 'job_description_preview' : 'We are looking for a Software Engineering Intern with experience in Python, REST APIs, and collaborative development using Git...',