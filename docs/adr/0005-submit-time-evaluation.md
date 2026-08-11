# Submit-time evaluation

**Status:** Accepted (2026-07-04); storage amended by ADR `0006` (2026-07-24).

## Context

Automated resume feedback (enrichment, keyword match, warnings, AI phrases) is the primary instructor-facing artifact for MVP. Students should not manage evaluation history as part of assignment turn-in. Evaluation must still run reliably without blocking the web request.

## Decision

- Every assignment **submission** (create and resubmit) triggers an **evaluate-on-submit** job against the eval-service using the submission-backed evaluation’s resume and frozen job context (`job_description_text`; `job_listing_id` when listing-backed). For on-site mock assignments, that job context comes from the student’s **current claim** (ADR `0007`).
- Resume, job context, results (`evaluation_data`), **`evaluator_version`**, and status / `failure_reason` live on that **`evaluations`** row (XOR parent per ADR `0006`)—not duplicated onto `submissions`. Assignment-policy freeze fields stay on the submission (ADR `0002`).
- Execution is **asynchronous** (queued worker) for **workspace practice** and **assignment submit**; the evaluation row’s status reflects `processing` → `completed` | `failed` until evaluation finishes.
- The **submission** (with its linked evaluation) is the system of record for instructor review and audit. Practice runs use the same `evaluations` table under a workspace parent only.
- **MVP resume storage:** prefer `resume_text` on the evaluation (no long-term file). **Future:** file + frozen `resume_text`.

## Consequences

- No evaluation picker or workspace-to-submission linking in MVP.
- Resubmit updates the submission and re-runs evaluation on the same submission-backed evaluation row (ADR `0003` + `0006`).
- On **`failed`**, student may **retry** (re-queue stored inputs) or **resubmit** with edited resume/JD on the same submission / evaluation. Store **`failure_reason`** (sanitized); log full exception separately.
- **Async UI:** redirect to submission detail after submit; page polls until `completed` | `failed`.
- Instructors may view submissions at **any status**; evaluation feedback appears when the linked evaluation is `completed`, with status shown while processing or failed.
- Eval-service API and prompt changes after submit do not alter past evaluation rows; `evaluator_version` supports explaining drift on resubmits only.

## Related

- ADR `0004` — workspaces vs submit path
- ADR `0006` — evaluation XOR ownership (workspace or submission)
- ADR `0007` — groups, claims, claim-backed practice JD
- ADR `0002` — assignment-policy snapshot on submission; JD on evaluation
- ADR `0003` — single active submission
