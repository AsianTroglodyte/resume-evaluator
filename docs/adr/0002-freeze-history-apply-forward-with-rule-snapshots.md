# Freeze history apply forward with rule snapshots

**Status:** Accepted; amended 2026-07-24 (job context lives on `evaluations` per ADR `0006`, not duplicated on `submissions`); amended again to drop `resubmission_count` (revision via timestamps only; `allow_resubmission` remains assignment policy).

## Context

Assignment rules (due date, versioned policy, allowed listings, etc.) can change after a student has turned work in. Instructors need existing submissions to remain interpretable under the rules that applied at submit/resubmit time (“freeze history, apply forward”).

With ADR `0006`, resume and job context are evaluation inputs on the 1:1 submission-backed `evaluations` row. Duplicating `job_listing_id` / `job_description_text` on `submissions` would only repeat that snapshot.

## Decision

- Assignment rule changes apply only to **future** submits/resubmits. Existing submission rows stay valid under the policy captured at their last write.
- Each **`submissions`** row stores a **minimal assignment-policy snapshot**:
  - `assignment_version`
  - `due_date_snapshot` (nullable due-date snapshot at last write)
  - plus timestamps (`created_at` / `updated_at`) as revision metadata (ADR `0003`)
- **Job context** (pasted `job_description_text` and/or `job_listing_id` when listing-backed) is stored only on the linked **`evaluations`** row, same as workspace practice runs. It is not copied onto `submissions`.
- On resubmit, refresh the submission’s policy snapshot and update the submission-backed evaluation’s resume/JD inputs in place.
- **`allow_resubmission`** lives on the **assignment** (policy). Submissions do **not** store a resubmission counter.

## Consequences

- Auditing “what assignment rules applied” uses the submission row; auditing “what resume/JD were evaluated” uses the evaluation row.
- No dual JD/listing columns to keep in sync between `submissions` and `evaluations`.
- Workspace practice continues to snapshot listing-backed JD on `evaluations` only.
- **Polished MVP:** `due_date_snapshot` comes from the assignment’s `due_date` at submit/resubmit time only. Per-student assignment user overrides (`assignment_user_overrides`) are **post-MVP** — the table may exist as scaffolding but must not be wired into submit logic until promoted.

## Related

- ADR `0003` — single active submission updated in place
- ADR `0006` — evaluation XOR ownership; eval inputs on `evaluations`
