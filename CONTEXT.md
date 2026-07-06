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
The primary teaching container for members, assignments, and module-scoped job listings.
_Avoid_: class, course (unless intentionally mapped in UI copy)

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
A **user-owned, named** area where students **freely evaluate resumes** for practice—upload or paste resume, optional job context. A user may have **multiple workspaces**. **Private to the owner** — instructors and other students cannot view workspace practice runs. Job context may be a **pasted JD** or the JD from **any assignment’s allowed on-site job listing** (for practice against mock postings). Independent of modules, assignments, and turn-in. Workspaces do **not** gate or supply assignment submissions.
_Avoid_: module workspace, assignment draft

**Practice Evaluation**:
An automated assessment run from a workspace for student feedback only. Not submitted to assignments. **MVP:** each practice run is **persisted** in the **`evaluations`** table (one row per run). History UI may be minimal at first; retention limits TBD later.
_Avoid_: Submission, qualifying evaluation, turn-in

### Assignment & submission

**Assignment**:
A module-owned work item that defines submission-validity rules and allowed job listings.
_Avoid_: Task, project (unless explicitly different)

**Allowed Job Listing**:
A job listing explicitly attached to an assignment and used as job context when the student submits.
_Avoid_: Global listing, open listing

**Job Listing Claim** (future, not MVP):
A student’s reserved slot on an on-site job listing for a specific assignment, enforced with capacity (FCFS). Separate step before submit in the future design. **MVP does not implement claims or capacity**—students select a listing or paste a JD at submit time only.
_Avoid_: Using claim for workspace practice

**Submit to Assignment**:
The LMS action where a student uploads (or provides) a **resume** for an assignment. Job context follows **assignment instructions**: **paste a JD** (e.g. external job) or **select an allowed on-site job listing** (JD taken from the listing). The system runs automated evaluation **on submit**, then stores the resume, job context, and **evaluation result** on the submission row.
_Avoid_: Submitting an evaluation, picking a past practice run, workspace snapshot

**Submission**:
The single active per-user, per-assignment record of a committed **resume** turn-in, updated in place on resubmission. Holds the resume artifact, job context, rule snapshot, and **submit-time evaluation output** (`evaluation_data`, scores, `evaluator_version`) **on the submission row**—not a foreign key to a workspace `evaluations` row. Visible to the submitting student and module **instructors** at all statuses (TAs deferred post-MVP). Instructors see resume, job context, and evaluation status; retry/resubmit actions are student-only.
_Avoid_: Attempt record (for MVP), workspace snapshot, evaluation-as-submit-unit, `evaluation_id` from practice

**Submit-Time Evaluation**:
The automated feedback pipeline invoked when a submission is created or updated. Same eval-service as practice, but results are **owned by the submission** and frozen for instructor/student review. Re-running on resubmit replaces the submission’s evaluation fields; practice runs in workspaces are unrelated.
_Avoid_: Pre-submit qualifying evaluation, evaluation picker

**Resubmission**:
An update to the existing submission record (new resume, re-evaluate, increment revision metadata).
_Avoid_: New attempt row (for MVP)

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
Assignment submissions always trigger evaluation at submit time; the result stored on the submission is the audit artifact for instructors and progress tracking.
_Avoid_: Submit-only without evaluation, submit by reference to a prior practice evaluation

**Evaluation storage (two homes)**:
- **Workspace practice** → `evaluations` table (rows per practice run; `workspace_id`).
- **Assignment submit** → `evaluation_data` (and related fields) **on the `submissions` row**. No FK to workspace `evaluations`.

**Async evaluation**:
Both workspace practice and assignment submit run evaluation via a **queued job**; the relevant row stays `pending` / `processing` until complete or failed.

**Job context freeze (listing-backed)**:
When JD comes from an allowed listing, store **`job_description_text` snapshot + `job_listing_id`** on the row at eval/submit time. Paste-only: text only, `job_listing_id` null. Listing edits do not alter past rows.

**Failed evaluation**:
Rows in `failed` state offer **retry** (re-queue with stored inputs) and **edit + try again**. Workspace practice: changed inputs → new `evaluations` row. Assignment submit: resubmit overwrites the single submission row (ADR `0003`). Persist **`failure_reason`** (user-safe message); full stack traces stay in application logs.

**Async UI**:
After run/submit, redirect to the **detail page** (practice evaluation entry or submission). That page polls until status is `completed` or `failed`.

**Instructor submission access**:
Instructors see submissions at **all statuses** (`pending`, `processing`, `failed`, `completed`). Evaluation output renders when complete; status is visible throughout. Student-only: retry and resubmit.

### MVP vs future (job listings)

| | **MVP (current)** | **Future** |
|---|---|---|
| Listing selection | Select any allowed on-site listing or paste JD per assignment instructions | Claim listing (FCFS) then submit resume |
| Capacity | Not implemented | Per-assignment listing capacity |
| Workspace + listings | May use any assignment’s listing JD for practice; no claim | Same; practice never consumes claim slots |
| Resume storage | **`resume_text` only** (upload → extract; paste OK; no file on disk) | **File + `resume_text`** (storage key, filename, frozen text) |
| Listing-backed JD | **Snapshot `job_description_text` + `job_listing_id`** on row | Same |
| Practice history | **Cap: latest 10 runs per workspace** (prune on insert) | Instructor-configurable or higher default |
