@php
    use App\Enums\JobListingSource;
    use App\Enums\ModuleJobListingScope;
@endphp

{{-- Assignment details --}}
<article class="rounded-box border border-base-300 bg-base-100 p-6">
    <header class="mb-4 space-y-1 border-b border-base-300 pb-4">
        <h3 class="text-lg font-semibold">Details</h3>
    </header>

    <dl class="space-y-6 text-sm">
        <div>
            <dt class="font-medium">Description</dt>
            <dd class="mt-1 text-base-content/80">
                {{ $assignment->description ?? '—' }}
            </dd>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <dt @class(['font-medium', 'text-error' => $assignment->due_date && $assignment->isPastDue()])>
                    @if ($assignment->due_date && $assignment->isPastDue())
                        Due date passed
                    @else
                        Due date
                    @endif
                </dt>
                <dd class="mt-1">
                    {{ $assignment->due_date?->format('M j, Y g:i A') ?? 'No due date' }}
                </dd>
            </div>
            <div>
                <dt class="font-medium">Job listing source</dt>
                <dd class="mt-1">{{ ucfirst($assignment->job_listing_source->value) }}</dd>
            </div>
        </div>
    </dl>

    @if ($submission === null && ! $assignment->isPastDue())
    <form
        class="flex flex-col gap-4 px-4 py-5 sm:px-6"
        method="POST"
        enctype="multipart/form-data"
        action="{{ route('dashboard.modules.assignments.submissions.store', [$module, $assignment]) }}">
        @csrf
        <div class="form-control w-full" > 
            <label class="label-text w-fit" for="resume_file">
                Resume file
            </label> 
            <input
                id="resume_file"
                name="resume_file"
                type="file"
                class="file-input file-input-bordered w-full"
                accept=".pdf,.doc,.docx,.txt" />
            <span class="label-text-alt mt-1 text-base-content/60">
                Accepted formats: PDF, DOC, DOCX, TXT
            </span>
            @error('resume_file')
            <span class="label-text-alt mt-1 text-error">{{ $message }}</span>
            @enderror
        </div>
        @if ($currentClaim && $assignment->usesModuleListings())
        <p class="rounded-box border border-primary/30 bg-primary/5 p-3 text-sm">
            Your resume will be evaluated against your claimed listing:
            <span class="font-medium">{{ $currentClaim->jobListing->name }}</span>.
        </p>
        @elseif ($assignment->requiresClaim())
        <p class="rounded-box border border-warning/30 bg-warning/5 p-3 text-sm">
            Claim a job listing below before submitting.
        </p>
        @else
        <div class="form-control w-full">
            <label class="label-text mb-1 font-medium" for="job_description">
                Job description <span class="font-normal text-base-content/50">(optional)</span>
            </label>
            {{-- NOTE all white space including newlines are counted in the text area slot area --}}
            <textarea
                id="job_description"
                name="job_description"
                class="textarea textarea-bordered min-h-28 max-h-60 w-full text-sm"
                placeholder="Paste a role description for targeted feedback and keyword analysis.">{{ session('job_description') }}</textarea>
            <span class="label-text-alt text-sm text-base-content/60">
                Leave blank for a general quality evaluation without keyword analysis.
            </span>
        </div>
        @endif
        @error('submission')
            <span class="label-text-alt mt-1 text-error">{{ $message }}</span>
        @enderror
        <div class="flex flex-wrap justify-end gap-2">
            <button type="submit" class="btn btn-primary" @disabled($assignment->requiresClaim() && ! $currentClaim)>Submit resume</button>
        </div>
    </form>
    @elseif($submission !== null)
    <section class="mt-6 border-t border-base-300 pt-6" aria-labelledby="submission-heading">
        <div class="rounded-box border border-success/30 bg-success/5 p-5 sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="flex items-start gap-3">
                    <div class="grid size-10 shrink-0 place-items-center rounded-full bg-success/15 text-success">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                            class="size-5"
                            aria-hidden="true">
                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.478-9.817a.75.75 0 0 1 1.052-.143Z" clip-rule="evenodd" />
                        </svg>
                    </div>

                    <div>
                        <h3 id="submission-heading" class="font-semibold">Submission received</h3>
                        <p class="mt-1 text-sm text-base-content/70">
                            Your resume has been submitted for this assignment.
                        </p>
                    </div>
                </div>

                <span class="badge badge-success badge-outline shrink-0">Submitted</span>
            </div>

            <dl class="mt-5 grid gap-4 border-t border-success/20 pt-4 text-sm sm:grid-cols-2">
                <div>
                    <dt class="text-base-content/60">Submitted on</dt>
                    <dd class="mt-1 font-medium">
                        {{ $submission->created_at->format('M j, Y g:i A') }}
                    </dd>
                </div>
                <div>
                    <dt class="text-base-content/60">Assignment version</dt>
                    <dd class="mt-1 font-medium">{{ $submission->assignment_version }}</dd>
                </div>
            </dl>
        </div>
        <form
            method="POST"
            class="mt-4 flex justify-end"
            action="{{ route('dashboard.modules.assignments.submissions.destroy', [$module, $assignment]) }}"
            onsubmit="return confirm('Remove your submission for this assignment?')">
            @csrf
            @method('DELETE')
            <button
                type="submit"
                class="btn btn-outline btn-error btn-sm">
                Remove submission
            </button>
        </form>
    </section>
    @elseif ($submission === null && $assignment->isPastDue())
    <section class="mt-6 border-t border-base-300 pt-6" aria-labelledby="past-due-heading">
        <div class="rounded-box border border-error/30 bg-error/5 p-5 sm:p-6">
            <div class="flex items-start gap-3">
                <div class="grid size-10 shrink-0 place-items-center rounded-full bg-error/15 text-error">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                        class="size-5"
                        aria-hidden="true">
                        <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495ZM10 5a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 5Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div>
                    <h3 id="past-due-heading" class="font-semibold">Submissions closed</h3>
                    <p class="mt-1 text-sm text-base-content/70">
                        The due date for this assignment was
                        {{ $assignment->due_date->format('M j, Y g:i A') }}.
                        New submissions are no longer accepted.
                    </p>
                </div>
            </div>
        </div>
    </section>
    @endif
    @if ($evaluation !== null)
    <section class="space-y-4">
        <div class="px-1">
            <h2 class="font-semibold">Submission evaluation</h2>
        </div>
        @if (session('evaluation_error'))
        <p class="text-sm text-error">{{ session('evaluation_error') }}</p>
        @endif
        <livewire:evaluation.evaluation :$evaluation />
        </section>
    @endif
</article>

{{-- Allowed job listings --}}
@if ($assignment->usesModuleListings())
@php
    $claimErrors = $errors->getBag('claim');
    $canClaim = auth()->user()->can('claim', $assignment);
@endphp
<details class="collapse collapse-arrow rounded-box border border-base-300 bg-base-100" open>
    <summary class="collapse-title text-lg font-semibold">Job listings</summary>
    <div class="collapse-content space-y-3">
        <p class="text-sm text-base-content/70">
            @if ($assignment->requiresClaim())
                Claim one listing to submit against. Slots are first come, first served, and you can switch while another listing has room.
            @else
                Claim a listing to submit against it, or paste an external job description when you submit.
            @endif
        </p>

        @if (session('claimStatus'))
            <div role="status" class="alert alert-success alert-soft py-2 text-sm">{{ session('claimStatus') }}</div>
        @endif

        @if ($claimErrors->has('job_listing_id'))
            <div role="alert" class="alert alert-error alert-soft py-2 text-sm">{{ $claimErrors->first('job_listing_id') }}</div>
        @endif

        @forelse ($claimableJobListings as $jobListing)
        @php
            $isCurrentClaim = $currentClaim?->job_listing_id === $jobListing->id;
        @endphp
        <div @class([
            'rounded-box border p-4',
            'border-primary bg-primary/5' => $isCurrentClaim,
            'border-base-300' => ! $isCurrentClaim,
        ])>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0">
                    <h4 class="flex flex-wrap items-center gap-2 font-medium">
                        {{ $jobListing->name }}
                        @if ($isCurrentClaim)
                            <span class="badge badge-primary badge-sm">Your claim</span>
                        @endif
                    </h4>
                    <p class="mt-1 text-xs text-base-content/60">
                        @if ($jobListing->capacity === null)
                            {{ $jobListing->claims_count }} claimed · no limit
                        @else
                            {{ $jobListing->claims_count }} / {{ $jobListing->capacity }} slots taken
                        @endif
                    </p>
                </div>

                @if ($canClaim)
                    @if ($isCurrentClaim)
                        <form method="POST" action="{{ route('dashboard.modules.assignments.claim.destroy', [$module, $assignment]) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline btn-sm">Release</button>
                        </form>
                    @elseif ($jobListing->isFull())
                        <span class="badge badge-ghost shrink-0">Full</span>
                    @else
                        <form method="POST" action="{{ route('dashboard.modules.assignments.claim.update', [$module, $assignment]) }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="job_listing_id" value="{{ $jobListing->id }}" />
                            <button type="submit" class="btn btn-primary btn-sm">
                                {{ $currentClaim ? 'Switch to this' : 'Claim' }}
                            </button>
                        </form>
                    @endif
                @endif
            </div>

            <details class="mt-2">
                <summary class="cursor-pointer text-sm text-base-content/70">Job description</summary>
                <p class="mt-2 whitespace-pre-line text-sm text-base-content/80">{{ $jobListing->description }}</p>
            </details>
        </div>
        @empty
        <p class="text-sm text-base-content/70">No job listings are available for this assignment.</p>
        @endforelse
    </div>
</details>
@endif

{{-- Instructor-only section --}}
@can('seeAllAssignmentDetails', $assignment)
<article class="rounded-box border border-base-300 bg-base-100 p-6">
    <header class="mb-4 space-y-1 border-b border-base-300 pb-4">
        <h3 class="text-lg font-semibold">Assignment configuration</h3>
        <p class="text-sm text-base-content/70">Visible to instructors and admins only.</p>
    </header>

    <dl class="space-y-4 text-sm">
        <div>
            <dt class="font-medium">Assignee scope</dt>
            <dd class="mt-1">
                {{ ucfirst($assignment->assignee_scope->value) }}
                @if ($assignment->group)
                    — <a href="{{ route('dashboard.modules.groups.show', [$assignment->module_id, $assignment->group]) }}" class="link link-primary">{{ $assignment->group->name }}</a>
                @endif
            </dd>
        </div>
    </dl>

    @if ($assignment->assignee_scope === \App\Enums\AssigneeScope::Selected)
    <details class="collapse collapse-arrow mt-4 rounded-box border border-base-300">
        <summary class="collapse-title text-sm font-medium">Assignees</summary>
        <div class="collapse-content">
            <ul class="space-y-1 text-sm">
                @forelse ($assignment->assignees as $assignee)
                <li>{{ $assignee->first_name }} {{ $assignee->last_name }} — {{ $assignee->email }}</li>
                @empty
                <li class="text-base-content/70">Noone in the module was selected</li>
                @endforelse
            </ul>
        </div>
    </details>
    @endif
</article>
@endcan
