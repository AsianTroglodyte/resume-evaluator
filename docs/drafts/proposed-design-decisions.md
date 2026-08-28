# Proposed design decisions (draft)

**Status:** Part A largely superseded by ADRs `0004` / `0005` / `0006`. Part B claim/group decisions **promoted to MVP** via ADR `0007` (2026-08-11). This draft retains narrative / open items only.  
**Authoritative vocabulary:** `CONTEXT.md`. **Authoritative decisions:** `docs/adr/0004`–`0007`.

---

## Part A — Resume evaluation & workspaces (historical)

Kept for history. Prefer ADRs for current rules. Job-description sources below are **obsolete** (replaced by ADR `0007`).

### Job description sources (obsolete — see ADR `0007`)

| Context | Rule (obsolete) |
|---------|-----------------|
| Workspace practice | Was: any allowed listing. **Now:** paste or **current claim** on a chosen assignment. |
| Assignment submit | Was: pick any allowed listing. **Now:** paste (external) or **current claim** (on-site). |

---

## Part B — Groups, claims, capacity (promoted)

**Status:** **MVP** — recorded in ADR `0007`. Summary retained here for seminar workflow storytelling.

### Real-world workflow (target)

1. **Assignment 1:** Students target **any online job** (external JD — paste at submit).
2. **Assignment 2 (typical):** Students use **on-site mock job listings** with limited capacity; mock interview follow-up (out of scope for software).
3. Often **two assignments** in a course; system should support **more** for flexibility.
4. **IT vs CS** students may see different mock listings (via **groups**).
5. Mock listings may have **capacity**; allocation **first-come first-served** via **claims**.

### Decisions (authoritative in ADR `0007` / CONTEXT)

| Topic | Choice |
|-------|--------|
| Modules vs groups | Optional groups; no groups ⇒ everyone cohort |
| Claim scope | One active claim per (student, assignment) |
| Change claim | Anytime on assignment page; freeze-history on existing submissions |
| Capacity | Per assignment; consumed on claim; submit does not free slot |
| Workspace listing JD | Read current claim only; no claim mutation from practice |
| Workspace claim-JD UI | **Deferred** until groups/claims land — paste-only until then |
| Claim UI | On the assignment page |

### Open (not decided / later)

- **Assignment user overrides** — per-student due-date extensions, exemptions, and resubmission exceptions (`assignment_user_overrides`). **Post-MVP**; polished MVP uses assignment-level `due_date` and `allow_resubmission` only.
- Full instructor claim override + audit (post-MVP).
- Interview scheduling (out of scope).
- Exact `group_id` attachment on job listings vs filter-only via assignment attachment — refine when implementing groups.

---

## Build sequencing note (2026-08-11)

Do **not** build workspace JD selection UI now. Continue other MVP work; implement **groups + claims** (and assignment claim UX) before the workspace “pick assignment → use my claim’s JD” control.

---

## Promotion checklist

- [x] Revise submit model in `CONTEXT.md`, ADR `0004`, ADR `0005`, README.
- [x] Promote Part B claims/groups into ADR `0007` and update `CONTEXT.md`.
- [x] Record deferred workspace claim-JD UI until groups/claims ship.
- [ ] Implement groups + claims + assignment claim UI.
- [ ] Then add workspace claim-JD picker (no listing browser).
- [ ] Archive or trim superseded sections in this draft after implementation sign-off.
