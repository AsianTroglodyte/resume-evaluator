# Evaluation belongs to exactly one of workspace or submission

**Status:** Accepted (2026-07-24). Supersedes the “embed `evaluation_data` on `submissions` / no evaluation FK” storage choice in ADR `0004` and ADR `0005`. Product separation of practice vs turn-in is unchanged.

## Context

Workspace practice and assignment submit both run the same async eval-service pipeline and need the same result shape (`status`, `failure_reason`, `evaluation_data`, resume/JD inputs, `evaluator_version`, …). Storing practice runs in `evaluations` while embedding eval fields on `submissions` duplicated that shape and split the job/UI update path.

Product rules still forbid treating a practice run as the turn-in artifact (no evaluation picker, no promote-workspace-eval-to-submit).

## Decision

- All evaluation runs live in the **`evaluations`** table.
- Each evaluation belongs to **exactly one** parent:
  - **`workspace_id`** set and **`submission_id`** null → workspace practice run, or
  - **`submission_id`** set and **`workspace_id`** null → submit-time evaluation for that submission.
- Enforce XOR in the schema (nullable FKs alone are insufficient): exactly one of `workspace_id` / `submission_id` is non-null.
- **Practice:** many evaluations per workspace; retention cap (latest **10** per workspace; prune oldest on insert) applies only to workspace-backed rows.
- **Submit:** at most **one** evaluation per submission (`submission_id` unique). Create/resubmit updates that evaluation in place (aligned with ADR `0003`), same as overwriting embedded fields previously did.
- Inputs and outputs for a run (resume text/path, **job context** `job_description_text` / `job_listing_id`, status, `evaluation_data`, etc.) live on the **`evaluations`** row only. Do **not** duplicate JD/listing onto `submissions`.
- `submissions` keeps LMS turn-in concerns: assignment, submitter, **assignment-policy** snapshot (`assignment_version`, `due_at_snapshot` per ADR `0002`), `resubmission_count`, and relates to its evaluation via the XOR FK (evaluation → submission)—not by copying eval JSON or job context onto the submission.
- **Still forbidden:** linking or copying a workspace practice evaluation into a submission; submit always creates/updates the submission’s own evaluation.

## Consequences

- One EvaluateJob / status machine / result renderer can target `evaluations` for both flows.
- Authorization and retention must branch on parent type (owner-only workspace vs submitter + module instructors).
- Cascade/delete rules: deleting a workspace removes its practice evaluations; deleting a submission removes its evaluation; pruning must never delete submission-backed rows.
- CONTEXT.md and migrations must match this ADR (application work may lag the decision record).

## Related

- ADR `0003` — single active submission updated in place
- ADR `0004` — workspaces vs submit path (product); storage amended by this ADR
- ADR `0005` — submit-time evaluation (pipeline); storage amended by this ADR
