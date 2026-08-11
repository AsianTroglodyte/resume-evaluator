# Resume Matcher LMS Context

This system is an LMS-style platform for assignment-driven resume evaluation. It models module-scoped teaching workflows with strict membership and submission invariants.

**Two separate flows:**

1. **Workspaces** — Students freely evaluate resumes for practice. No module, assignment, or turn-in coupling.
2. **Assignments** — Students submit a **resume** only. Evaluation runs automatically on submit; the resume and feedback are visible to the student and module **instructors** (**TAs deferred post-MVP**). Instructors see submissions at all evaluation statuses.

Personal workspaces are **not** part of the assignment submit path.

## Language

### Identity & Access

**Admin**:
A global role with platform-wide authority, including creating modules and overriding module-local permissions.
_Avoid_: Superuser, owner

**Instructor**:
A module-local role that manages module content, membership, assignments, and job listings within that module.
_Avoid_: Teacher (unless used as display text), moderator

**TA** (teaching assistant):
A module-local role (or permission bundle) that can view student submissions and automated evaluation feedback within a module, without full instructor admin powers. **MVP: deferred** — only `student` and `instructor` ship; instructors cover submission review. Post-MVP: add `ta` to `RoleInModule` with view-only access.
_Avoid_: Grader (unless distinct product role), moderator

**Student**:
A module-local role that can access assigned work and submit resumes to assignments when actively enrolled and eligible.
_Avoid_: Learner, participant

### Core Domain

**Module**:
The primary teaching container for members, optional groups, assignments, and module-scoped job listings.
_Avoid_: class, course (unless intentionally mapped in UI copy)

**Group**:
An optional cohort within a module (e.g. IT vs CS) used to scope assignment eligibility and/or which job listings students see. A module with no groups behaves as a single implicit “everyone” cohort.
_Avoid_: section, team, cohort (unless mapped as UI copy for Group)

**Module Membership**:
A relationship between a user and module with exactly one role and lifecycle status.
_Avoid_: Enrollment (unless mapped intentionally), subscription

**Active Membership**:
A membership status that grants runtime access to module resources and submission actions.
_Avoid_: Joined, enabled

**Archived Module**:
A deactivated module whose historical data remains readable but operational workflows are read-only.
_Avoid_: Deleted module

### Workspace & practice (optional, not submit)

**Workspace**:
A **user-owned, named** area where students **freely evaluate resumes** for practice—upload or paste resume, optional job context. A user may have **multiple workspaces**. **Private to the owner** — instructors and other students cannot view workspace practice runs. Job context may be a **pasted JD** or the JD of the student’s **current claim** on a chosen assignment (practice against that mock posting). Workspaces do **not** create or change claims, do **not** consume listing capacity, and do **not** gate or supply assignment submissions.
_Avoid_: module workspace, assignment draft, browsing all allowed listings from the workspace

**Practice Evaluation**:
An automated assessment run from a workspace for student feedback only. Not submitted to assignments. **MVP:** each practice run is **persisted** in the **`evaluations`** table (one row per run; `workspace_id` set). History UI may be minimal at first; **retention: keep latest 5** workspace-backed runs per workspace (prune oldest on insert).
_Avoid_: Submission, qualifying evaluation, turn-in

### Assignment & submission

**Assignment**:
A module-owned work item that defines submission-validity rules and allowed job listings.
_Avoid_: Task, project (unless explicitly different)

**Allowed Job Listing**:
A job listing explicitly attached to an assignment; students claim one of these (when the assignment uses on-site listings) or the listing’s JD is otherwise used as job context.
_Avoid_: Global listing, open listing

**Job Listing Claim**:
A student’s reserved slot on an on-site job listing for a specific assignment, enforced with per-assignment capacity (first-come first-served). At most **one active claim per (student, assignment)**. Claiming and changing claims happens on the **assignment** page; workspace practice may **read** the current claim’s JD but never creates or consumes a claim.
_Avoid_: Soft preference, shortlist, practice-time claim

**Submit to Assignment**:
The LMS action where a student uploads (or provides) a **resume** for an assignment. Job context follows **assignment instructions**: **paste a JD** (e.g. external job) or, for on-site mock listings, the JD from the student’s **current claim** (claim required before submit). The system creates/updates the submission and a linked **submit-time evaluation** (ADR `0006`), then runs automated evaluation **on submit**.
_Avoid_: Submitting an evaluation, picking a past practice run, workspace snapshot, picking an arbitrary unclaimed listing at submit when claims apply

**Submission**:
The single active per-user, per-assignment record of a committed **resume** turn-in, updated in place on resubmission. Holds LMS turn-in identity and **assignment-policy snapshot** (`assignment_version`, `due_date_snapshot`). Resume, job context, and evaluation output live on the linked **`evaluations`** row (`submission_id` set; XOR with workspace per ADR `0006`)—not embedded on the submission. Visible to the submitting student and module **instructors** at all statuses (TAs deferred post-MVP). Instructors see resume, job context, and evaluation status via that link; retry/resubmit actions are student-only.
_Avoid_: Attempt record (for MVP), workspace snapshot, evaluation-as-submit-unit, practice `evaluation_id` picker, resubmission counter

**Submit-Time Evaluation**:
The automated feedback pipeline invoked when a submission is created or updated. Same eval-service as practice; results are **owned by the submission-backed `evaluations` row** and frozen for instructor/student review. Re-running on resubmit updates that evaluation in place; practice runs in workspaces are unrelated.
_Avoid_: Pre-submit qualifying evaluation, evaluation picker

**Resubmission**:
An update to the existing submission record (new resume, refresh policy snapshot, re-evaluate the linked evaluation in place). Whether resubmit is allowed is an **assignment** policy (`allow_resubmission`), not a counter on the submission.
_Avoid_: New attempt row (for MVP), `resubmission_count`

**Assignment Version**:
A monotonic version incremented only when submission-validity rules change.
_Avoid_: Edit count, revision number (for cosmetic edits)

**Rule Snapshot**:
Submission-time persisted rule fields used to audit that submission under frozen historical behavior.
_Avoid_: Live rule lookup only

### Invariants

**Single Membership Invariant**:
A user may have at most one membership row per module.
_Avoid_: Multi-role duplicate memberships

**Instructor Presence Invariant**:
A module must always have at least one instructor; removing or demoting the last instructor is disallowed.
_Avoid_: Instructorless module

**Freeze-History / Apply-Forward**:
Rule changes affect future submissions only; existing submissions remain valid under their original snapshot.
_Avoid_: Retroactive invalidation

**Workspace Independence**:
A user may use workspaces without module membership. Assignment submission does not require a workspace and does not reference workspace evaluation history. Workspace practice is **private to the owner**.
_Avoid_: Workspace-bound turn-in, evaluation-first submit, instructor visibility into practice runs

**Submit-Evaluate-Freeze**:
Assignment submissions always trigger evaluation at submit time; the linked submission-backed `evaluations` row is the audit artifact for instructors and progress tracking.
_Avoid_: Submit-only without evaluation, submit by reference to a prior practice evaluation

**Evaluation storage (XOR parent)**:
All runs live in **`evaluations`**. Each row has **exactly one** parent (ADR `0006`):
- **Workspace practice** → `workspace_id` set, `submission_id` null.
- **Assignment submit** → `submission_id` set, `workspace_id` null (at most one evaluation per submission).

**Async evaluation**:
Both workspace practice and assignment submit run evaluation via a **queued job**; the evaluation row stays `processing` until `completed` or `failed`.

**Job context freeze (listing-backed)**:
When JD comes from an allowed listing, store **`job_description_text` snapshot + `job_listing_id`** on the **`evaluations`** row at eval/submit time. Paste-only: text only, `job_listing_id` null. Listing edits do not alter past rows.

**Failed evaluation**:
Rows in `failed` state offer **retry** (re-queue with stored inputs) and **edit + try again**. Workspace practice: changed inputs → new `evaluations` row. Assignment submit: resubmit updates the single submission and its evaluation in place (ADR `0003`). Persist **`failure_reason`** (user-safe message); full stack traces stay in application logs.

**Async UI**:
After run/submit, redirect to the **detail page** (practice evaluation entry or submission). That page polls until status is `completed` or `failed`.

**Instructor submission access**:
Instructors see submissions at **all evaluation statuses** (`processing`, `failed`, `completed`, or no submission yet). Evaluation output renders when complete; status is visible throughout. Student-only: retry and resubmit.

### MVP vs later (job listings)

| | **MVP** | **Later** |
|---|---|---|
| Groups | Optional groups within a module; omit ⇒ everyone cohort | Instructor claim override / audit, richer group tooling |
| On-site listing selection | **Claim** allowed listing (FCFS, capacity on claim); then submit resume | Same model |
| External / paste JD | Paste at submit when assignment instructions require it | Same |
| Capacity | Per-assignment listing capacity; submit does **not** free the slot | Same |
| Workspace + listings | Paste JD, or practice using **current claim’s JD** for a chosen assignment (read-only; no claim mutation) | Same; never consume claim slots from practice |
| Workspace claim-JD UI | **Deferred** until groups/claims ship — paste-only (or no listing picker) until then | Ship assignment picker that resolves JD from claim |
| Resume storage | **`resume_text` only** (upload → extract; paste OK; no file on disk) | **File + `resume_text`** (storage key, filename, frozen text) |
| Listing-backed JD | **Snapshot `job_description_text` + `job_listing_id`** on row | Same |
| Practice history | **Cap: latest 5 runs per workspace** (prune on insert) | Instructor-configurable or higher default |
