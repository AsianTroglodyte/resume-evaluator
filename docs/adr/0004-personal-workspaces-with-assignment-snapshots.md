# Personal workspaces (practice) and resume submission (evaluate on submit)

**Status:** Accepted (revised 2026-07-04; storage amended by ADR `0006` on 2026-07-24). Supersedes the earlier “workspace snapshot” submit model described in this file.

## Context

Students need a place to practice resume feedback without assignment pressure. Instructors need a simple turn-in path with frozen automated feedback for grading and progress tracking. Tying assignment submit to **prior workspace evaluations** (history, qualifying runs, evaluation picker) added implementation and UX complexity with limited benefit: keyword match and most checks are reproducible from resume + job context at submit time.

## Decision

### Workspaces (practice)

- Personal **workspaces**: students **freely evaluate resumes** with no turn-in coupling. A user may own **multiple named workspaces** (e.g. per job target).
- Job context in a workspace: **pasted JD** or JD from **any assignment’s allowed on-site job listing** (practice against mock postings). Listing-backed runs store **`job_description_text` snapshot + `job_listing_id`**; paste-only runs store text with null listing FK.
- Practice evaluations are **not** submitted to assignments.
- **Privacy:** workspaces and practice evaluations are **owner-only**; module staff do not have access.
- **MVP:** each workspace practice run is **persisted** in **`evaluations`** with `workspace_id` set and `submission_id` null (ADR `0006`). **Retention:** keep latest **5** workspace-backed runs per workspace; prune oldest on insert. UI may show latest first. Resume stored as **`resume_text` only** on the evaluation (upload → extract → discard file; paste allowed).

### Assignment submission

- Students submit a **resume** on the assignment (upload or paste).
- Job context per **assignment instructions**: **paste JD** or **select allowed on-site job listing** (MVP: no claims/capacity). Listing-backed submit-time evaluations snapshot **`job_description_text` + `job_listing_id`** at submit time.
- On submit/resubmit: **evaluate-on-submit** (async); create or update the submission’s **`evaluations`** row (`submission_id` set, `workspace_id` null per ADR `0006`). Do not copy or link a workspace practice evaluation.
- **Visibility:** submitter and module **instructors** (TA role deferred post-MVP). Instructors see the submission (and its evaluation status) at **all statuses**; student-only actions (retry/resubmit) are not offered on instructor views.

### Async evaluation (both flows)

- **Workspace practice** and **assignment submit** both enqueue eval jobs against **`evaluations`** rows; status is `pending` → `processing` → `completed` | `failed` until done.
- **On `failed`:** student may **retry** (re-queue with frozen inputs) or **edit** resume/JD and try again — workspace creates a **new** `evaluations` row when inputs change; assignment **resubmit** updates the single submission and its evaluation in place (ADR `0003`). Row stores **`failure_reason`** (user-safe); full errors logged server-side.
- **Async UI:** after run/submit, **redirect to detail page** (evaluation entry or submission); page **polls** until terminal status.

### What we explicitly do not do (MVP)

- No “pick a qualifying evaluation from workspace history” on submit.
- No requirement that a practice run exist before submit.
- No workspace snapshot promoted into a submission.

## Consequences

- Simpler student mental model: **turn in resume → get feedback on that turn-in**.
- Shared `evaluations` table for both flows with XOR parent (ADR `0006`); submissions remain the LMS turn-in record.
- Resubmission updates resume/JD on the submission-backed evaluation and refreshes the submission’s assignment-policy snapshot (ADR `0002`); job context is not stored on `submissions`.
- **Practice retention:** cap at **5** evaluations per workspace (delete oldest workspace-backed rows beyond cap on new run); never prune submission-backed evaluations via that policy.

## Related ADRs

- `0002` — assignment-policy snapshots on submission; job context on evaluation.
- `0003` — one submission row per user per assignment, updated on resubmit.
- `0005` — submit-time evaluation pipeline.
- `0006` — evaluation XOR ownership (workspace or submission).

### MVP scope note (job listings)

**Current build:** select any allowed on-site job listing or paste JD at submit—**no claims, no capacity, no FCFS**. **Future:** separate claim step (ADR draft Part B) before submit when capacity matters. Workspace practice never consumes listing capacity.
