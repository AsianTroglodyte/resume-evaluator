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
                        Due: {{ $assignment->due_at?->format('M j, Y g:i A') ?? 'No due date' }}
                    </p>
                </div>

                {{-- Instructor/admin only --}}
                @can('update', $assignment)
                    <a
                        href="{{ route('dashboard.modules.assignments.edit', [$module, $assignment]) }}"
                        class="btn btn-outline btn-sm shrink-0"
                    >
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

                <dl class="space-y-4 text-sm">
                    <div>
                        <dt class="font-medium">Description</dt>
                        <dd class="mt-1 text-base-content/80">
                            {{ $assignment->description ?? '—' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="font-medium">Due date</dt>
                        <dd class="mt-1">{{ $assignment->due_at?->format('M j, Y g:i A') ?? 'No due date' }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium">Resubmission</dt>
                        <dd class="mt-1">{{ $assignment->allow_resubmission ? 'Allowed' : 'Not allowed' }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium">Job listing source</dt>
                        <dd class="mt-1">{{ ucfirst($assignment->job_listing_source->value) }}</dd>
                    </div>
                </dl>
            </article>

            {{-- Allowed job listings --}}
            @if ($assignment->job_listing_source === JobListingSource::Both
                || $assignment->job_listing_source === JobListingSource::Module)
            <details class="collapse collapse-arrow rounded-box border border-base-300 bg-base-100" open>
                <summary class="collapse-title text-lg font-semibold">Allowed job listings</summary>
                <div class="collapse-content space-y-1">
                    <p class="text-sm text-base-content/70">Submit your resume against one of these postings.</p>

                    {{-- {{ $assignment->module_job_listing_scope }}
                    {{ ModuleJobListingScope::All->value}} --}}
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

            {{-- Resume submission --}}
            <article class="rounded-box border border-base-300 bg-base-100 p-6">
                <header class="mb-4 space-y-1 border-b border-base-300 pb-4">
                    <h3 class="text-lg font-semibold">Your submission</h3>
                    <p class="text-sm text-base-content/70">Upload your resume for this assignment.</p>
                </header>

                <div class="space-y-5">
                    <label class="form-control w-full">
                        <span class="label-text mb-1 font-medium">Resume file</span>
                        <input
                            type="file"
                            class="file-input file-input-bordered w-full"
                            accept=".pdf,.doc,.docx"
                        />
                        <span class="label-text-alt mt-1 text-base-content/60">Accepted formats: PDF, DOC, DOCX</span>
                    </label>

                    <div class="flex flex-wrap justify-end gap-2 border-t border-base-300 pt-4">
                        <button type="submit" class="btn btn-primary">Submit resume</button>
                    </div>
                </div>
            </article>

        </div>
    </section>
</x-dashboard-layout>
