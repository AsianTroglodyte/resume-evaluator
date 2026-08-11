# Groups, job listing claims, and claim-backed practice JD

**Status:** Accepted (2026-08-11). Amends ADR `0004` job-context rules for workspaces and assignment submit. Promotes former draft Part B claim/group decisions into MVP.

## Context

On-site mock listings need limited capacity so students self-select which posting they prepare for (senior seminar). Listing every allowed JD in a workspace is unusable at scale. Coupling workspace practice to “any allowed listing” also diverges from how students will actually submit (under a claim).

## Decision

### Groups (MVP)

- A **module may have optional groups**. With no groups, the module is one implicit everyone cohort.
- Groups may scope assignment eligibility and/or listing visibility (e.g. IT vs CS).

### Claims & capacity (MVP)

- For assignments that use on-site mock listings: students **claim** an allowed listing (**FCFS**).
- **One active claim per (student, assignment)**; students may change claim at any time on the **assignment** page.
- Capacity is **per assignment**; a slot is consumed **on claim**. **Submit does not free** the slot.
- Submit/resubmit for those assignments requires the student’s **current claim**; job context on the evaluation is snapshotted from the claimed listing (`job_description_text` + `job_listing_id`).
- Assignments that use **external / paste JD** do not require claims.

### Workspace practice JD (MVP product rule; UI deferred)

- Listing-backed practice uses only the JD of the student’s **current claim** for a **chosen assignment** (read claim state; do **not** create, change, or consume claims from the workspace).
- Paste-only and no-JD practice remain available.
- Do **not** offer a workspace browser of all allowed listings or of other unclaimed listings on an assignment.
- **Build order:** ship groups + claims (and assignment claim UI) before the workspace “pick assignment → use my claim’s JD” control. Until then, workspaces stay **paste / no JD only** for job context—do not invent a temporary “pick any listing” affordance that we will remove.

### Explicitly rejected for MVP

- Free-form pick of any allowed listing from the workspace.
- Claim-as-default plus “browse other listings on this assignment” in the workspace.
- Consuming or mutating claims from practice runs.

## Consequences

- Claims and groups are MVP dependencies for the intended practice and submit paths around mock listings.
- Workspace and assignment on-site paths share one notion of “which JD am I on” (the claim).
- Existing ADR `0004` wording that allowed any assignment listing in practice without claims is superseded by this ADR for that point.
- Prior draft Part B claim/group tables are promoted; keep instructor claim override / interview scheduling / some listing–group FK details as later work if still open.

## Related

- `CONTEXT.md` — Group, Job Listing Claim, Workspace, Submit to Assignment
- ADR `0004` — workspaces vs submit (amended)
- ADR `0005` — submit-time evaluation
- ADR `0006` — evaluation XOR ownership
