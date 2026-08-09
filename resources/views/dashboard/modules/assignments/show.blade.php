@php
use App\Enums\ModuleJobListingScope;
use App\Enums\JobListingSource;
@endphp

<x-dashboard-layout>
    <x-slot:title>{{ $assignment->title }}</x-slot:title>

    <section class="space-y-6">
        <header class="space-y-1">
            <a href="{{ route('dashboard.modules.show', $module) }}" class="link link-primary text-sm">
                &larr; Back to {{ $module->name }}
            </a>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-2xl font-semibold">{{ $assignment->title }}</h2>
                    <p class="mt-1 text-sm text-base-content/70">
                        Due: {{ $assignment->due_date?->format('M j, Y g:i A') ?? 'No due date' }}
                    </p>
                </div>

                {{-- Instructor/admin only --}}
                @can('update', $assignment)
                <a
                    href="{{ route('dashboard.modules.assignments.edit', [$module, $assignment]) }}"
                    class="btn btn-outline btn-sm shrink-0">
                    Edit assignment
                </a>
                @endcan
            </div>
        </header>

        <div class="space-y-6">

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

                    <div class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <dt class="font-medium">Due date</dt>
                            <dd class="mt-1">{{ $assignment->due_date?->format('M j, Y g:i A') ?? 'No due date' }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium">Resubmission</dt>
                            <dd class="mt-1">{{ $assignment->allow_resubmission ? 'Allowed' : 'Not allowed' }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium">Job listing source</dt>
                            <dd class="mt-1">{{ ucfirst($assignment->job_listing_source->value) }}</dd>
                        </div>
                    </div>
                </dl>

                @if ($assignment->submission === null)
                <form
                    class="flex flex-col gap-4 px-4 py-5 sm:px-6"
                    method="POST"
                    enctype="multipart/form-data"
                    action="{{ route('submissions.evaluations.store', $assignment) }}">
                    @csrf
                    <label class="form-control w-full">
                        <span class="label-text mb-1 font-medium">Resume file</span>
                        <input
                            type="file"
                            name="resume_file"
                            class="file-input file-input-bordered w-full"
                            accept=".pdf,.doc,.docx" />
                        <span class="label-text-alt mt-1 text-base-content/60">Accepted formats: PDF, DOC, DOCX</span>
                        @error('resume_file')
                        <span class="label-text-alt mt-1 text-error">{{ $message }}</span>
                        @enderror
                    </label>
                    <label class="form-control w-full">
                        <span class="label-text mb-1 font-medium">Job description <span class="font-normal text-base-content/50">(optional)</span></span>
                        <textarea
                            name="job_description"
                            class="textarea textarea-bordered min-h-28 max-h-60 w-full text-sm"
                            placeholder="Paste a role description for targeted feedback and keyword analysis.">{{ session('job_description') }}</textarea>
                        <span class="label-text-alt text-sm text-base-content/60">
                            Leave blank for a general quality evaluation without keyword analysis.
                        </span>
                    </label>
                    <div class="flex flex-wrap justify-end gap-2">
                        <button type="submit" class="btn btn-primary">Submit resume</button>
                    </div>
                </form>
                @else
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
                                    {{ $assignment->submission->created_at->format('M j, Y g:i A') }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-base-content/60">Assignment version</dt>
                                <dd class="mt-1 font-medium">{{ $assignment->submission->assignment_version }}</dd>
                            </div>
                        </dl>
                    </div>
                    <form
                        method="POST"
                        class="mt-4 flex justify-end"
                        action="{{ route('submissions.evaluations.destroy', $assignment) }}"
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
                @php
                    $evaluation = $assignment->evaluation;
                @endphp
                @endif
                @if ($evaluation !== null)
                <section class="space-y-4">
                    <div class="px-1">
                        <h2 class="font-semibold">Submission evaluation</h2>
                    </div>
                    @if (session('evaluation_error'))
                    <p class="text-sm text-error">{{ session('evaluation_error') }}</p>
                    @endif
                    <x-evaluation :$evaluation :expandedIds=null> </x-evaluation>
                    </section>
                @endif
            </article>

            {{-- Allowed job listings --}}
            @if ($assignment->job_listing_source === JobListingSource::Both
            || $assignment->job_listing_source === JobListingSource::Module)
            <details class="collapse collapse-arrow rounded-box border border-base-300 bg-base-100" open>
                <summary class="collapse-title text-lg font-semibold">Allowed job listings</summary>
                <div class="collapse-content space-y-1">
                    <p class="text-sm text-base-content/70">Submit your resume against one of these postings.</p>

                    @if ($assignment->module_job_listing_scope === ModuleJobListingScope::All)
                    @forelse ($module->jobListings as $jobListing)

                    <details class="collapse collapse-arrow rounded-box border border-base-300">
                        <summary class="collapse-title font-medium">
                            {{ $jobListing->name }}
                        </summary>
                        <div class="collapse-content">
                            <p class="text-sm text-base-content/70">
                                {{ $jobListing->description }}
                            </p>
                        </div>
                    </details>
                    @empty
                    <p class="text-sm text-base-content/70">No specific job listings are linked to this assignment.</p>
                    @endforelse
                    @else
                    @forelse ($assignment->jobListings as $listing)
                    <details class="collapse collapse-arrow rounded-box border border-base-300">
                        <summary class="collapse-title font-medium">{{ $listing->name }}</summary>
                        <div class="collapse-content">
                            <p class="text-sm text-base-content/70">{{ $listing->description }}</p>
                        </div>
                    </details>
                    @empty
                    <p class="text-sm text-base-content/70">No specific job listings are linked to this assignment.</p>
                    @endforelse
                    @endif
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
                        <dd class="mt-1">{{ ucfirst($assignment->assignee_scope->value) }}</dd>
                    </div>
                </dl>

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
            </article>
            @endcan

        </div>
    </section>
</x-dashboard-layout>
