# Proposed design decisions (draft)

**Status:** Part A **superseded** for submit flow by ADRs `0004` (revised) and `0005` (2026-07-04). Part B remains tentative pending client review.  
**Authoritative vocabulary:** `CONTEXT.md`. **Authoritative submit/practice split:** `docs/adr/0004`, `docs/adr/0005`.

---

## Part A — Resume evaluation & workspaces (revised MVP)

### Product scope

| Decision | Choice |
|----------|--------|
| MVP AI scope | **Evaluate-only** (scores + structured feedback). No tailor/improve in MVP. |
| Long-term | Toward **feature parity** with Resume-Matcher (tailor, improve, etc.) after MVP. |
| Resume-Matcher relationship | **Thin Python eval service** owned by this project; port patterns/modules from Resume-Matcher, do not merge their main branch continuously. |

### Workspace vs assignment (revised)

| Decision | Choice |
|----------|--------|
| Workspaces | **Practice only** — draft resumes, optional **practice evaluations** for feedback. Not required for submit. **Multiple named workspaces per user.** |
| Data model | **`evaluations`** = workspace practice runs. **`submissions`** = resume + `evaluation_data` on row (no FK to practice). |
| Assignment submit unit | **Resume** via upload (PDF/DOCX) or paste; **MVP stores `resume_text` only** (extract on upload, discard file). **Future:** persisted file + `resume_text`. |
| When evaluation runs for assignments | **On submit** (and on resubmit), async job, results frozen on **submission** row. |
| Instructor artifact | Frozen **`evaluation_data`** (+ scores, `evaluator_version`) on submission. |

### Job description sources (revised)

| Context | Rule |
|---------|------|
| **Workspace practice** | **Pasted JD** or select JD from **any assignment’s allowed on-site job listing**. No claims, no capacity. |
| **Assignment submit (MVP)** | Per assignment instructions: **paste JD** or **select allowed on-site job listing**. No claims, no capacity. |
| **Listing-backed rows** | **Snapshot `job_description_text` + `job_listing_id`** at eval/submit time (paste-only: text only, `job_listing_id` null). |
| **Assignment submit (future)** | Claim listing (FCFS) then submit resume when capacity is required. |
| **No JD** | Optional; keyword match omitted. |

### Evaluation output (MVP)

| Field / behavior | Choice |
|------------------|--------|
| Storage | **`evaluations`** table for workspace practice; **`evaluation_data`** JSON on **`submissions`** for turn-in (separate; no FK). |
| Scores | `keyword_match` nullable when no JD; other scores TBD. |
| Feedback | Enrichment, warnings, AI phrases, keyword lists (eval-service shape today). |
| Instructor view | Same evaluation detail as students, from **submission** record. |

### Technical pipeline (revised)

| Step | Choice |
|------|--------|
| Resume artifact (MVP) | Upload → extract text → store **`resume_text`** on row; **no file persistence**. Paste-as-text also allowed. Same for workspace practice and submissions. |
| Resume artifact (future) | **File + `resume_text`** — storage key, original filename, frozen text snapshot. |
| Job context (listing) | **`job_description_text` snapshot + nullable `job_listing_id`** when sourced from an allowed listing; paste-only rows store text with null FK. |
| Assignment submit | Store resume text + job context → queue **EvaluateSubmission** job. |
| Evaluation execution | **Async** for **both** workspace practice and assignment submit (`pending` → `processing` → `completed` \| `failed`); queued worker calls eval-service. |
| Failed evaluation | **Retry + edit** — failed rows show **Retry** (re-queue with stored inputs). Student may also edit resume/JD and re-submit: workspace → **new `evaluations` row**; assignment → **resubmit overwrites** same submission row (ADR `0003`). |
| Practice history retention | **Cap per workspace** — keep latest **10** `evaluations` rows per workspace; prune oldest on new insert. |
| Async UI | **Redirect + poll on detail** — after submit/run, redirect to evaluation or submission detail; that page polls until `completed` \| `failed`. |
| Instructor submission view | **Full row at all statuses** — instructors see resume, JD, and status (`pending` / `failed` / `completed`) immediately; same detail as student **minus** retry/resubmit actions. |
| Workspace privacy | **Owner only** — practice workspaces and evaluations are private; instructors do not see practice runs. |
| Failed row detail | **`failure_reason`** — user-safe message on `failed` rows; full exception detail in Laravel logs only. |
| Workspaces | Optional; practice eval pipeline can reuse eval-service; **no submit linkage** in MVP. |
| `evaluator_version` | Stored on submission (and practice rows when they exist). |

### Retired (do not implement for MVP)

- Evaluation-first snapshot / workspace snapshot submit.
- Qualifying evaluation picker.
- Requirement that practice evaluation match listing before submit.
- “Scan history” as prerequisite to turn-in.

---

## Part B — LMS redesign (senior seminar workflow)

**Status:** **Future** — not MVP. Current build uses simple JD paste or listing select at submit with **no claims or capacity**.

Decisions below are retained for later client alignment. Do not implement for MVP unless explicitly promoted to an ADR.

### Real-world workflow (target)

1. **Assignment 1:** Students target **any online job** (external JD — paste at submit).
2. **Assignment 2 (typical):** Students use **on-site mock job listings** with limited capacity; mock interview follow-up (out of scope for software).
3. Often **two assignments** in a course; system should support **more** for flexibility.
4. **IT vs CS** students may see different mock listings.
5. Mock listings may have **capacity**; allocation **first-come first-served**.

### Job listing claims

| Decision | Choice |
|----------|--------|
| Claim scope | **One active claim per (student, assignment)** — Option C. |
| Change claim | Student may **change claim at any time**. Existing submissions remain valid under **freeze-history**; new/resubmit uses new claim + new resume submit + re-evaluate. |
| Submit validation | Submission must use student's **current claim** for that assignment; claim must be an **allowed listing**. Resume submitted on assignment; JD from claimed listing at submit time. |

### Modules vs groups

| Decision | Choice |
|----------|--------|
| Structure | **One module with optional groups** — Option A. |
| Default (no groups) | Module behaves as implicit **“everyone”** cohort. |
| When groups used | e.g. IT / CS — group-scoped assignments and/or listing visibility. |

### Job listing ownership & assignment attachment

| Decision | Direction (tentative) |
|----------|----------------------|
| Listing storage | Listings at **module level** (optionally filtered by group); **`capacity`** on listing. |
| Assignment link | Via `assignment_allowed_job_listings`. Assignment 1: external only, no claims. |

### Job listing capacity (FCFS)

| Decision | Choice |
|----------|--------|
| Slot consumption | **On claim** (Option C). |
| After submit | Submitting does **not** free the slot. |
| Capacity scope | **Per assignment** (Option B). |

### Claim change after submission

| Decision | Choice |
|----------|--------|
| Behavior | **Submission unchanged until resubmit** (Option A). UI may show mismatch between current claim and submitted listing snapshot. |

### Claim UI placement (MVP)

| Decision | Choice |
|----------|--------|
| Where students claim | **On the assignment page** — claim, upload resume, submit (and view submission status/feedback). |

### Assignment 1 (external JD)

| Decision | Choice |
|----------|--------|
| External submit | Paste JD at submit (or stored on submission); resume upload; evaluate-on-submit; freeze JD + `evaluation_data` on submission. |

### Open (not decided)

- Full instructor claim override + audit (post-MVP).
- Interview scheduling (out of scope).
- **`group_id` on job listings** — deferred.

---

## Promotion checklist

- [x] Revise submit model in `CONTEXT.md`, ADR `0004`, ADR `0005`, README.
- [ ] Client review of Part B claim rules after Part A revision.
- [ ] Split remaining Part B items into ADRs when committed.
- [ ] Archive or trim superseded sections in this draft after sign-off.
