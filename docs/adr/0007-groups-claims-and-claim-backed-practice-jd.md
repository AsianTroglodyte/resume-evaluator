# Groups, job listing claims, and claim-backed practice JD

**Status:** Accepted (2026-08-11). Amends ADR `0004` job-context rules for workspaces and assignment submit. Promotes former draft Part B claim/group decisions into MVP.

## Context

On-site mock listings need limited capacity so students self-select which posting they prepare for (senior seminar). Listing every allowed JD in a workspace is unusable at scale. Coupling workspace practice to “any allowed listing” also diverges from how students will actually submit (under a claim).

## Decision

### Groups (MVP)

- A **module may have optional groups**. With no groups, the module is one implicit everyone cohort.
- Groups may scope assignment eligibility and/or listing visibility (e.g. IT vs CS).
- A student belongs to **at most one group per module** (or none = ungrouped). Stored as nullable `module_memberships.module_group_id`, so the schema enforces the rule. Unlike Moodle's default (many groups per student), this keeps listing filters and FCFS capacity pools unambiguous.
- Deleting a group **ungroups** its students (`nullOnDelete`); it never removes their module membership.
- Group names are unique within a module. Groups are managed (create / rename / delete / place) by module instructors and global admins only; students do not see the group list.

### Groups × assignments × job listings (MVP)

- **Assignment targeting** is one of `Everyone` | `Group` | `Selected` (`assignee_scope`). `Group` uses a single nullable `assignments.module_group_id`; it is never combined with a manual `Selected` list. Track-specific work is one assignment **per group** (e.g. an IT and a CS copy of Assignment 2), not one assignment targeting many groups.
- **Ungrouped students** see `Everyone` assignments (and `Selected` ones naming them) only; group-scoped assignments stay hidden until they are placed in that group.
- **Job listings have no group link.** A student sees a listing only through an assignment they can see that allows it. Group-appropriate listings come from attaching them to that group's assignment.
- **Deleting a group is blocked** while any assignment targets it (`restrictOnDelete`), so a track assignment is never silently widened or orphaned. Re-target or delete those assignments first.
- **Re-targeting** an assignment (changing scope or group) is a submission-validity rule change: bump `assignment_version`; existing submissions stay frozen (ADR `0002`).
- **Moving a student between groups** (or ungrouping / removing them): existing submissions remain visible to them and instructors; any claim on an assignment they lose access to **and have not submitted to** is released; the old group's assignments otherwise drop out of their list.

### Claims & capacity (MVP)

- For assignments that use on-site mock listings: students **claim** an allowed listing (**FCFS**).
- **One active claim per (student, assignment)**; students may change claim at any time on the **assignment** page.
- Capacity is **per assignment**, stored on the attachment (`assignment_allowed_job_listings.capacity`; `null` = unlimited), so the same listing may have different capacity on different assignments. A slot is consumed **on claim** and released when the student changes claim. **Submit does not free** the slot.
- Changing claim after submitting does not alter the frozen submission; it only matters for a later resubmit (post-MVP).
- Assignments using **all module listings** (no attachment rows) are claimable but uncapped; use **selected listings** to set capacity.
- `job_listing_source = module` requires a claim to submit. `both` makes claiming optional: a current claim supplies the JD, otherwise the pasted JD is used. `external` has no claims.
- Detaching a listing from an assignment (or switching it to external) deletes claims on that listing; re-targeting releases claims of students who lost access (same rule as group moves).
- Submit/resubmit for those assignments requires the student’s **current claim**; job context on the evaluation is snapshotted from the claimed listing (`job_description_text` + `job_listing_id`).
- Assignments that use **external / paste JD** do not require claims.

### Workspace practice JD (MVP; amended 2026-10-01)

- The workspace job-context picker defaults to **paste**, and otherwise lists only the student's **current claims** (one per assignment they are still given). Claims stay the primary, prioritised path.
- A separate **"Browse all listings"** modal offers breadth: every listing allowed on an assignment the student is given, organised **module › assignment › listing**, with the claimed listing marked. Choosing one sets only that practice run's JD.
- The browse modal also offers **Claim / Switch claim / Release** per listing (amended 2026-10-01). These post to the same claim endpoint as the assignment page (same `claim` policy, FCFS lock, capacity) and return to the workspace. Switching or releasing asks for confirmation since the old slot is freed.
- **Practice runs** remain read-only with respect to claims: running an evaluation never creates, changes, or consumes a claim.
- Job context is snapshotted on the practice evaluation (`job_description_text` + `job_listing_id`), same as submit.

### Explicitly rejected for MVP

- Listing every allowed listing inline in the workspace picker (too busy at scale); breadth lives behind the browse modal instead.
- Consuming or mutating claims as a side effect of practice runs (explicit claim actions in the browse modal are allowed).

## Consequences

- Claims and groups are MVP dependencies for the intended practice and submit paths around mock listings.
- Workspace and assignment on-site paths share one notion of “which JD am I on” (the claim).
- Existing ADR `0004` wording that allowed any assignment listing in practice without claims is superseded by this ADR for that point.
- Prior draft Part B claim/group tables are promoted; keep instructor claim override / interview scheduling as later work. The listing–group FK question is closed: listings are filtered only via assignment attachment (2026-09-30).

## Related

- `CONTEXT.md` — Group, Job Listing Claim, Workspace, Submit to Assignment
- ADR `0004` — workspaces vs submit (amended)
- ADR `0005` — submit-time evaluation
- ADR `0006` — evaluation XOR ownership
