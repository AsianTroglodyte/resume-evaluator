# Personal workspaces with assignment snapshots

Students own personal **workspaces** for resume iteration and automated evaluation, independent of any module or assignment. A workspace is where drafting happens: upload or revise resume versions, run evaluations, review LLM feedback, and keep a short history of versions and evaluation results.

**Submission** is a separate LMS action: the student promotes a **workspace snapshot** into an assignment. The snapshot freezes the chosen resume version, evaluation result, job context, and assignment rule fields at submit time (see freeze-history / rule-snapshot ADRs). Iteration in the workspace does not mutate an existing submission unless the student explicitly resubmits (see single active submission ADR).

Workspaces are user-scoped and usable without module membership. Submitting to an assignment still requires conformance to assignment rules (allowed job listings, due dates, resubmission policy, assignee eligibility, and related validity checks). The LMS domain (modules, memberships, assignments) remains decoupled from workspace lifecycle; only the submit action bridges the two.

For MVP, instructors are not required to provide manual feedback; automated evaluation output attached to the submission snapshot is the primary evaluation artifact for progress tracking.

_Open questions (not committed): workspace UI naming, history retention limits, score thresholds before submit, custom vs listing-only job descriptions._
