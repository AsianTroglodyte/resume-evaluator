# Proposed design decisions (draft)

**Status:** Under review with client — not committed.  
**Supersedes nothing.** Existing ADRs (`docs/adr/0001`–`0004`) and `CONTEXT.md` remain authoritative until these are accepted and promoted.

---

## Part A — Resume evaluation & workspaces

Decisions from architecture discussion (evaluate-only MVP, Resume-Matcher as long-term reference).

### Product scope

| Decision | Choice |
|----------|--------|
| MVP AI scope | **Evaluate-only** (scores + structured feedback). No tailor/improve in MVP. |
| Long-term | Toward **feature parity** with Resume-Matcher (tailor, improve, etc.) after MVP. |
| Resume-Matcher relationship | **Thin Python eval service** owned by this project; port patterns/modules from Resume-Matcher, do not merge their main branch continuously. |

### Workspace model

| Decision | Choice |
|----------|--------|
| Primary container | **Workspace-centric** — versions and scans are children of a workspace. |
| Module coupling | **Fully decoupled** until submit. Students create workspaces freely; submission is a separate LMS action. |
| Submit unit | **Scan-first snapshot** — student picks a qualifying resume scan (implies version + job context + scores + feedback). |

### Job description sources (scanning vs submit)

| Context | Rule |
|---------|------|
| Personal workspace scans | Paste external JD **or** pick an assignment-linked on-site listing (details TBD for paste UX). |
| Assignment submit | Must use an **on-site allowed job listing** selected in the submit UI — **no paste**, no fuzzy matching pasted text to listings. **Exception:** external assignments (Assignment 1) — paste-backed scan; snapshot **denormalizes** scan payload on submit. |
| General scan (no JD) | One pipeline, JD optional; keyword fields null/omitted. Cannot satisfy assignments that require a listing-backed scan. |

### Scan output (MVP)

| Field / behavior | Choice |
|------------------|--------|
| Scores | `ats_score`; `keyword_match` nullable when no JD. |
| Feedback | **Structured `feedback_json`** (e.g. strengths, gaps, ATS issues, keyword misses when JD present). |
| Instructor view | **Same evaluation detail as students**, backed by frozen snapshot at submit (one view, two entry points). |

### Technical pipeline

| Step | Choice |
|------|--------|
| Upload | **Parse on upload** — store structured resume JSON per version (queued job, status on version row). |
| Scan execution | **Async queued job**; status on workspace show page (`pending` → `processing` → `completed` \| `failed`). |
| Integration | Laravel = system of record; **Python eval service** for parse (if not done in Laravel job calling Python) + evaluate endpoints. |
| `evaluator_version` | Tracks Python service + prompt/schema version for audit and re-scan. |

### Open (not decided)

- Exact shape of `feedback_json` sections.
- Paste-from-online-JD UX in workspace (noted for later).
- Whether parse runs in Laravel-only job vs Python-only (likely Python for parity with Resume-Matcher `parser.py`).

---

## Part B — LMS redesign (senior seminar workflow)

Decisions from LMS workflow discussion. **Tentative** — author expressed uncertainty; validate with client.

### Real-world workflow (target)

1. **Assignment 1:** Students target **any online job** (external JD — paste).
2. **Assignment 2 (typical):** Students use **on-site mock job listings** with limited capacity; mock interview follow-up (out of scope for software).
3. Often **two assignments** in a course; system should support **more** for flexibility.
4. **IT vs CS** students may see different mock listings.
5. Mock listings may have **capacity**; allocation **first-come first-served**.

### Job listing claims

| Decision | Choice |
|----------|--------|
| Claim scope | **One active claim per (student, assignment)** — Option C. Each assignment with on-site listings has its own FCFS pick. Options A/B (one claim per module/group) are special cases. |
| Change claim | Student may **change claim at any time**. Existing submissions remain valid under **freeze-history**; new/resubmit uses new claim + new qualifying scan. |
| Submit validation | Submission must use student's **current claim** for that assignment, and claim must be an **allowed listing** on that assignment. Scan must have been run against that **exact listing** (not paste). |

### Modules vs groups

| Decision | Choice |
|----------|--------|
| Structure | **One module with optional groups** — Option A. |
| Default (no groups) | Module behaves as implicit **“everyone”** cohort; no group hierarchy required for simple deployments. |
| When groups used | e.g. IT / CS — group-scoped assignments and/or listing visibility for split mock pools while **shared Assignment 1** stays module-wide. |
| Alternative considered | Separate modules per track (Option B) — rejected for seminar shape due to duplicated Assignment 1 and split roster; may still suit other clients. |

### Job listing ownership & assignment attachment

| Decision | Direction (tentative) |
|----------|----------------------|
| Listing storage | Listings live at **module level** (optionally filtered by group); **`capacity`** on listing. |
| Assignment link | Assignments attach **subsets** via existing `assignment_allowed_job_listings` pattern. Assignment 1: external only, no claims. Assignment 2+: selected on-site listings. |
| Shared pool vs per-assignment | **Assignment-attached subsets** preferred for scalability; same listing may attach to multiple assignments if needed. |

### Class templates

| Decision | Direction (tentative) |
|----------|----------------------|
| Templates | **Worth building** — preconfigure groups (optional), assignments, starter listings for “Senior Seminar” shape. Not scoped for MVP. |

### Group membership & visibility

| Decision | Choice |
|----------|--------|
| Groups per student | **At most one group** per student per module (Option A). Ungrouped = implicit everyone. |
| What groups gate | **Both assignments and job listings** (Option C). Assignment 1 remains **module-wide** (`group_id` null, external JD). Track-specific assignments (e.g. mock interview) are group-scoped with group-appropriate allowed listings. |

### Job listing capacity (FCFS)

| Decision | Choice |
|----------|--------|
| Slot consumption | **On claim** (Option C). Claiming holds a slot; changing claim releases the previous listing’s slot. |
| After submit | Submitting does **not** free the slot — the student occupied that mock role for the course. |
| Capacity scope | **Per assignment** (Option B). Same listing on two assignments = independent capacity pools (each assignment ≈ another interview round). |
| Waitlist | **Out of MVP**; add later if needed. |

### Ungrouped students (when groups enabled)

| Decision | Choice |
|----------|--------|
| Access | **Module-wide assignments only** until instructor assigns a group (Option A). Group-scoped assignments and listings hidden until placed in IT/CS (or other group). Assignment 1 (external JD) available immediately. |

### Listing capacity default

| Decision | Choice |
|----------|--------|
| Unset capacity | **`null` = unlimited** (Option A). FCFS enforced only when capacity is explicitly set. |

### Instructor overrides (MVP)

| Decision | Choice |
|----------|--------|
| MVP scope | **Group placement only** (Option B). Instructor moves students between groups (or into a group from ungrouped). Students self-service claim/change within FCFS rules. Full claim override + audit (A/D) deferred. |

### Claim change after submission

| Decision | Choice |
|----------|--------|
| Behavior | **Submission unchanged until resubmit** (Option A). Frozen submission stays on old listing/scan; new claim applies only to future resubmit. UI may show mismatch between current claim and submitted snapshot. |

### Claim UI placement (MVP)

| Decision | Choice |
|----------|--------|
| Where students claim | **On the assignment page** (Option A). Claim, scan link-out, qualifying scan picker, and submit on one assignment-centric flow. Module job board deferred. |

### Assignee scope vs groups

| Decision | Choice |
|----------|--------|
| Eligibility | **Groups supersede `assignee_scope` / manual assignee lists** when groups are in use (Option A, tweaked). |
| Module-wide assignment | `group_id = null` → all active module members (Assignment 1). |
| Group-scoped assignment | `group_id` set → only members of that group. No parallel Selected checkbox list for the same cohort. |
| Legacy `assignee_scope` | **Deprecate for new work** when groups enabled; keep `assignment_user_overrides` for exemptions/extensions (Moodle-style individual overrides). Modules with no groups: everyone sees everything (current Everyone behavior). |

### Group change mid-course

| Decision | Choice |
|----------|--------|
| Instructor moves student to another group | **Soft carry** (Option B). Update group membership only. Frozen submissions on old group’s assignments remain visible/auditable. Clear **active claims** on assignments the student no longer accesses; release those capacity slots. New group’s assignments unlock; student claims/scans/submits fresh there. |

**Moodle analogy (for client discussion):** Moodle uses **Groups** (membership) + **Restrict access** on activities (limit who sees/submits). Optional **Groupings** bundle groups for targeting. There is no separate “pick students from checklist” parallel to groups — groups *are* the roster split. Individual **user overrides** handle exceptions. This design mirrors that: group on assignment ≈ Moodle restrict-by-group; overrides ≈ Moodle user overrides.

### Assignment 1 (external JD) — snapshot shape

| Decision | Choice |
|----------|--------|
| External submit freeze | **Denormalize on submit** (Option B). Submission/snapshot copies JD text, scores, and `feedback_json` from the qualifying scan — not only a FK. Audit trail survives workspace/scan lifecycle changes. Scan FK may still be stored for traceability. |
| Validation | Assignment `job_listing_source = external`; qualifying scan has pasted JD (no `job_listing_id`); no claim check. |

### Open (not decided)

- Full instructor claim override + audit (post-MVP).
- Interview scheduling integration (explicitly out of scope).
- **`group_id` on job listings** (optional vs assignment-only gating) — deferred.

### Resolved in this pass

- Assignment 1 + groups: **module-wide** (`group_id` null), external JD only.

---

## Promotion checklist (after client sign-off)

- [ ] Client review of this draft complete.
- [ ] Split Part A / Part B into numbered ADRs where decisions differ from or extend `0001`–`0004`.
- [ ] Update `CONTEXT.md` language (Workspace, Group, Job Listing Claim, etc.).
- [ ] Mark draft archived or delete superseded sections.
