# Single active submission updated in place

For MVP, each `(assignment_id, user_id)` has one active submission row that is updated in place on resubmission, with revision metadata (`resubmission_count`, `updated_at`). This keeps query paths and constraints simple, matches the chosen UX, and leaves room to evolve to full attempt history only if product needs demand it.
