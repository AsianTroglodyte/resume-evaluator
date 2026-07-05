# Personal workspaces (practice) and resume submission (evaluate on submit)

**Status:** Accepted (revised 2026-07-04). Supersedes the earlier “workspace snapshot” submit model described in this file.

## Context

Students need a place to practice resume feedback without assignment pressure. Instructors need a simple turn-in path with frozen automated feedback for grading and progress tracking. Tying assignment submit to **prior workspace evaluations** (history, qualifying runs, evaluation picker) added implementation and UX complexity with limited benefit: keyword match and most checks are reproducible from resume + job context at submit time.

## Decision

### Workspaces (practice)

- Personal **workspaces**: students **freely evaluate resumes** with no turn-in coupling. A user may own **multiple named workspaces** (e.g. per job target).
- Job context in a workspace: **pasted JD** or JD from **any assignment’s allowed on-site job listing** (practice against mock postings). Listing-backed runs store **`job_description_text` snapshot + `job_listing_id`**; paste-only runs store text with null listing FK.
- Practice evaluations are **not** submitted to assignments.
- **Privacy:** workspaces and practice evaluations are **owner-only**; module staff do not have access.
- **MVP:** each workspace practice run is **persisted** in **`evaluations`** (one row per run). **Retention:** keep latest **10** runs per workspace; prune oldest on insert. UI may show latest first. Resume stored as **`resume_text` only** (upload → extract → discard file; paste allowed).

### Assignment submission

- Students submit a **resume** on the assignment (upload or paste; **MVP:** `resume_text` only on row).
- Job context per **assignment instructions**: **paste JD** or **select allowed on-site job listing** (MVP: no claims/capacity). Listing-backed submissions snapshot **`job_description_text` + `job_listing_id`** at submit time.
- On submit/resubmit: **evaluate-on-submit** (async); store resume, job context, and **`evaluation_data` on the `submissions` row** (not an FK to workspace `evaluations`).
- **Visibility:** submitter and module **instructors** (TA role deferred post-MVP). Instructors see the submission at **all statuses** (including `pending` and `failed`); student-only actions (retry/resubmit) are not offered on instructor views.

### Async evaluation (both flows)

- **Workspace practice** and **assignment submit** both enqueue eval jobs; row status is `pending` → `processing` → `completed` | `failed` until done.
- **On `failed`:** student may **retry** (re-queue with frozen inputs) or **edit** resume/JD and try again — workspace creates a **new** `evaluations` row when inputs change; assignment **resubmit overwrites** the single submission row. Row stores **`failure_reason`** (user-safe); full errors logged server-side.
- **Async UI:** after run/submit, **redirect to detail page** (evaluation entry or submission); page **polls** until terminal status.

### What we explicitly do not do (MVP)

- No “pick a qualifying evaluation from workspace history” on submit.
- No requirement that a practice run exist before submit.
- No workspace snapshot promoted into a submission.

## Consequences

- Simpler student mental model: **turn in resume → get feedback on that turn-in**.
- Simpler data model: submission owns resume + evaluation output; workspace tables are optional practice infrastructure.
- Resubmission re-uploads resume and re-runs evaluation; prior submission content remains frozen under freeze-history ADR until updated in place per single-submission ADR.
- Practice evaluation history in workspaces can be added later without changing the submit contract.
- **Practice retention:** cap at **10** evaluations per workspace (delete oldest beyond cap on new run).

## Related ADRs

- `0002` — rule snapshots on submission still apply.
- `0003` — one submission row per user per assignment, updated on resubmit.
- `0005` — submit-time evaluation pipeline (if split out).

### MVP scope note (job listings)

**Current build:** select any allowed on-site job listing or paste JD at submit—**no claims, no capacity, no FCFS**. **Future:** separate claim step (ADR draft Part B) before submit when capacity matters. Workspace practice never consumes listing capacity.
