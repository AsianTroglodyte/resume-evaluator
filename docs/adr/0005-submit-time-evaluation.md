# Submit-time evaluation

**Status:** Accepted (2026-07-04)

## Context

Automated resume feedback (enrichment, keyword match, warnings, AI phrases) is the primary instructor-facing artifact for MVP. Students should not manage evaluation history as part of assignment turn-in. Evaluation must still run reliably without blocking the web request.

## Decision

- Every assignment **submission** (create and resubmit) triggers an **evaluate-on-submit** job against the eval-service using the submitted **`resume_text`** and frozen job context (`job_description_text`; `job_listing_id` when listing-backed).
- Results are stored on the submission as structured **`evaluation_data`** (JSON), plus metadata such as **`evaluator_version`** and nullable score fields (e.g. keyword match).
- Execution is **asynchronous** (queued worker) for **workspace practice** and **assignment submit**; row status reflects `pending` → `processing` → `completed` | `failed` until evaluation finishes.
- The frozen submission record is the system of record for instructor review and audit. Practice evaluations in workspaces live in **`evaluations`** only; submissions store their own **`evaluation_data`** with no FK between the two.
- **MVP resume storage:** `resume_text` only (no persisted file). **Future:** file + frozen `resume_text`.

## Consequences

- No evaluation picker or workspace-to-submission linking in MVP.
- Resubmit replaces resume and re-runs evaluation on the same submission row.
- On **`failed`**, student may **retry** (re-queue stored inputs) or **resubmit** with edited resume/JD on the same row. Store **`failure_reason`** (sanitized); log full exception separately.
- **Async UI:** redirect to submission detail after submit; page polls until `completed` | `failed`.
- Instructors may view submissions at **any status**; evaluation feedback appears when `completed`, with status shown while pending or failed.
- Eval-service API and prompt changes after submit do not alter past submissions; `evaluator_version` supports explaining drift on resubmits only.

## Related

- ADR `0004` — workspaces vs submit path
- ADR `0002` — rule snapshot at submit
- ADR `0003` — single active submission
