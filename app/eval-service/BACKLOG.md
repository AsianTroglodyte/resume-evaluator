# Eval Service — RM Feature Backlog

Features worth borrowing from [Resume-Matcher](https://github.com/srbhr/Resume-Matcher) (`apps/backend/`) for **evaluate-only** feedback. Not tied to master resume, tailoring, or the resume builder UI unless noted.

**Status:** `todo` | `in_progress` | `done` | `skip`

**Already in eval-service:** `extract_job_keywords`, `calculate_keyword_match`, `_keyword_in_text`, `_extract_all_text`, prompt-injection sanitization (`keyword_match.py`), `complete_json` (`llm.py`), MarkItDown parse + optional `parse_resume_to_json` (`parser.py`).

---

## Suggested order

1. Keyword gap lists (matched / missing)
2. AI phrase detection (blacklist scan — feedback only, no mutation)
3. Enrichment analyze (`analysis_summary` + questions)
4. `build_review_warnings` (deterministic completeness checks)
5. Medium-value items as needed

---

## High value (feedback)

### 1. AI phrase detection

| | |
|---|---|
| **Status** | `done` |
| **RM source** | `app/prompts/refinement.py` (`AI_PHRASE_BLACKLIST`, `AI_PHRASE_REPLACEMENTS`), `app/services/refiner.py` (`remove_ai_phrases`) |
| **What** | Flag buzzwords and AI-sounding phrases (“leveraged”, “synergy”, em-dashes, etc.). RM mutates text; for evaluate, **detect and report** only (optionally suggest replacements). |
| **Needs** | Plain resume text is enough; structured `ResumeData` optional. JD can protect phrases that appear in the posting (RM skips removal if phrase is in JD). |
| **Evaluate output** | List of flagged phrases; optional “try X instead of Y” from `AI_PHRASE_REPLACEMENTS`. |

### 2. Keyword gap lists

| | |
|---|---|
| **Status** | `done` |
| **RM source** | `app/services/refiner.py` (`analyze_keyword_gaps`, `calculate_keyword_match`) |
| **What** | Extend match % with **matched** and **missing** keyword lists (required + preferred + general keywords from JD extraction). |
| **Needs** | Resume as text or dict (`_extract_all_text`). Drop RM’s injectable vs non-injectable split (master-coupled) unless we add a “already mentioned elsewhere in resume” heuristic later. |
| **Evaluate output** | `match_percent`, `matched_keywords[]`, `missing_keywords[]`. |

### 3. Enrichment analyze (structured feedback)

| | |
|---|---|
| **Status** | `todo` |
| **RM source** | `app/prompts/enrichment.py` (`ANALYZE_RESUME_PROMPT`, `WEAK DESCRIPTION INDICATORS`), `app/schemas/enrichment.py` (`AnalysisResponse`, `EnrichmentItem`, `EnrichmentQuestion`), `app/routers/enrichment.py` (`analyze_resume` — analyze path only) |
| **What** | RM’s main coaching flow: weak bullets on experience/projects, up to 6 clarifying questions, `analysis_summary`. |
| **Needs** | Resume text or `json.dumps(resume)`; small enrichment schema (not full `ResumeData`). |
| **Skip for now** | `ENHANCE_*`, `REGENERATE_*`, apply/regenerate endpoints (builder). |
| **Evaluate output** | `analysis_summary`, `items_to_enrich[]`, `questions[]`. |

### 4. Deterministic review warnings

| | |
|---|---|
| **Status** | `todo` |
| **RM source** | `app/services/resume_wizard.py` (`build_review_warnings`) |
| **What** | Rule-based gaps: no name, no contact, no experience/projects, empty education, empty skills. No LLM. |
| **Needs** | Parsed `ResumeData` easiest; could approximate from text/heuristics later. |
| **Evaluate output** | `warnings[]` strings. |

### 5. Improver utilities (mostly done)

| | |
|---|---|
| **Status** | partial |
| **RM source** | `app/services/improver.py` |
| **Done** | `extract_job_keywords`, `_sanitize_user_input` (in `keyword_match.py`) |
| **Skip** | `generate_improvements()` — cheap template filler after tailor, low value for evaluate |

---

## Medium value (adapt or defer)

### 6. Full-text extraction for keyword match

| | |
|---|---|
| **Status** | `todo` |
| **RM source** | `refiner.py` (`_extract_all_text`) — already in `keyword_match.py` |
| **What** | Stop using `resume_from_plain_text()` stub; pass full resume text or structured dict through `_extract_all_text`. |

### 7. JD truncation helper

| | |
|---|---|
| **Status** | `todo` |
| **RM source** | `refiner.py` (`_prepare_job_description`, `MAX_JD_LENGTH`) |
| **What** | Truncate very long job postings before LLM calls; log when truncated. |

### 8. Parse truncation warning

| | |
|---|---|
| **Status** | `todo` |
| **RM source** | `improver.py` (`_check_for_truncation`) |
| **What** | After `parse_resume_to_json`, warn if `workExperience` is empty (possible LLM truncation). |

### 9. Weak-bullet pre-check (local)

| | |
|---|---|
| **Status** | `todo` |
| **RM source** | `prompts/enrichment.py` (`WEAK DESCRIPTION INDICATORS`) |
| **What** | Regex/heuristics on bullets: “responsible for”, “worked on”, no digits — flag before or without a full enrichment LLM call. |

### 10. Truthfulness prompt rules

| | |
|---|---|
| **Status** | `todo` |
| **RM source** | `app/prompts` (`CRITICAL_TRUTHFULNESS_RULES` in improver imports) |
| **What** | Add to coach/enrichment system prompts: only comment on content present in the resume. |

### 11. LLM enrichment truncation retries

| | |
|---|---|
| **Status** | partial |
| **RM source** | `app/llm.py` (`_appears_truncated` with `schema_type="enrichment"`) |
| **What** | Ensure `complete_json(..., schema_type="enrichment")` retries when analyze JSON is incomplete. |

### 12. Resume structure sanity check

| | |
|---|---|
| **Status** | `todo` |
| **RM source** | `refiner.py` (`_validate_resume_structure`) |
| **What** | Optional guard after parse; more relevant if we persist structured resumes. |

### 13. Resume diff / change summary

| | |
|---|---|
| **Status** | `skip` (until versioning) |
| **RM source** | `improver.py` (`calculate_resume_diff`) |
| **What** | Before/after change stats; only if we compare two resume versions. |

---

## Explicitly out of scope (builder / tailor / master)

Do not port for evaluate-only MVP:

- `refine_resume()`, `inject_keywords()`, `validate_master_alignment()`, `fix_alignment_violations()`
- `improve_resume()`, `generate_resume_diffs()`, `apply_diffs()`, skill target plan
- Master / tailored resume DB model, `parent_id`, `is_master`
- `cover_letter.py`, `pdf.py`, resume wizard (except `build_review_warnings`)
- Full `ResumeData` required on every evaluate call (keep parse as optional infrastructure)

---

## Notes

- **Master resume:** High-value items above do not require master/tailored split. Injectable vs non-injectable keywords is tailor-only.
- **Career inventory:** Alternative to RM master; defer unless product needs “pick from full history per job.”
- **Pydantic `ResumeData`:** Optional for feedback; required for parse pipeline, wizard-style warnings, and builder features later.
