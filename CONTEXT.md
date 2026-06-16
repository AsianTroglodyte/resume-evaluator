# Resume Matcher LMS Context

This system is an LMS-style platform for assignment-driven resume scanning. It models module-scoped teaching workflows with strict membership and submission invariants.

## Language

### Identity & Access

**Admin**:
A global role with platform-wide authority, including creating modules and overriding module-local permissions.
_Avoid_: Superuser, owner

**Instructor**:
A module-local role that manages module content, membership, assignments, and job listings within that module.
_Avoid_: Teacher (unless used as display text), moderator

**Student**:
A module-local role that can access assigned work and submit resume scans when actively enrolled.
_Avoid_: Learner, participant

### Core Domain

**Module**:
The primary teaching container for members, assignments, and module-scoped job listings.
_Avoid_: Group, class, course (unless intentionally mapped in UI copy)

**Module Membership**:
A relationship between a user and module with exactly one role and lifecycle status.
_Avoid_: Enrollment (unless mapped intentionally), subscription

**Active Membership**:
A membership status that grants runtime access to module resources and submission actions.
_Avoid_: Joined, enabled

**Archived Module**:
A deactivated module whose historical data remains readable but operational workflows are read-only.
_Avoid_: Deleted module

### Assignment & Submission

**Assignment**:
A module-owned work item that defines submission-validity rules and allowed job listings.
_Avoid_: Task, project (unless explicitly different)

**Allowed Job Listing**:
A job listing explicitly attached to an assignment and selectable for submission.
_Avoid_: Global listing, open listing

**Submission**:
The single active per-user, per-assignment record updated in place on resubmission.
_Avoid_: Attempt record (for MVP), draft upload

**Resubmission**:
An update to the existing submission record that increments submission revision metadata.
_Avoid_: New attempt row (for MVP)

**Assignment Version**:
A monotonic version incremented only when submission-validity rules change.
_Avoid_: Edit count, revision number (for cosmetic edits)

**Rule Snapshot**:
Submission-time persisted rule fields used to evaluate and audit that submission under frozen historical behavior.
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
