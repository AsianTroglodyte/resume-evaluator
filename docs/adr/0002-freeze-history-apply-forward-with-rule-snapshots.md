# Freeze history apply forward with rule snapshots

Assignment rule changes apply only to future submissions, while existing submissions remain valid under the rules in effect at submission time. To make this auditable and deterministic, each submission stores a minimal snapshot (`assignment_version`, `selected_job_listing_id`, `job_description_text`, `due_at_snapshot`), preventing retroactive policy drift and simplifying exception handling later. Workspace practice `evaluations` rows use the same listing snapshot pattern when JD is listing-backed.
