<x-dashboard-layout>
    <x-slot:title>{{ $module->name }}</x-slot:title>

    <section class="space-y-6">
        <x-module-header :module="$module" />

        <div class="grid gap-6 lg:grid-cols-2">
            <article class="rounded-box border border-base-300 p-4">
                <header class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold">Assignments</h3>
                        <p class="mt-1 text-sm text-base-content/70">Module tasks and submission targets.</p>
                    </div>
                    <a
                        href="{{ route('dashboard.modules.assignments.create', $module) }}"
                        class="btn btn-primary btn-sm shrink-0"
                    >
                        Create Assignment
                    </a>
                </header>

                <div class="space-y-3">
                    @forelse ($assignments as $assignment)
                        <button
                            type="button"
                            class="w-full rounded-box border border-base-300 p-3 text-left transition hover:bg-base-200 cursor-pointer"
                            onclick="assignment_modal_{{ $assignment->id }}.showModal()"
                        >
                            <h4 class="font-medium">{{ $assignment->title }}</h4>
                            <p class="mt-2 text-sm text-base-content/70">
                                Due: {{ $assignment->due_at?->format('M j, Y g:i A') ?? 'No due date' }}
                            </p>
                        </button>

                        <dialog id="assignment_modal_{{ $assignment->id }}" class="modal">
                            <div class="modal-box max-w-lg">
                                <form method="dialog">
                                    <button class="btn btn-sm btn-circle btn-outline absolute right-2 top-2" aria-label="Close">×</button>
                                </form>

                                <h3 class="text-lg font-bold">{{ $assignment->title }}</h3>

                                <dl class="mt-4 space-y-3 text-sm">
                                    <div>
                                        <dt class="font-medium">Description</dt>
                                        <dd class="mt-1 text-base-content/80">{{ $assignment->description }}</dd>
                                    </div>
                                    <div>
                                        <dt class="font-medium">Due</dt>
                                        <dd class="mt-1">{{ $assignment->due_at?->format('M j, Y g:i A') ?? 'No due date' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="font-medium">assignee_scope</dt>
                                        <dd class="mt-1">{{ $assignment->assignee_scope }}</dd>
                                    </div>
                                    <div>
                                        <dt class="font-medium">Resubmission allowed</dt>
                                        <dd class="mt-1">{{ $assignment->allow_resubmission ? 'Yes' : 'No' }}</dd>
                                    </div>
                                </dl>

                                <div class="mt-4 space-y-4 border-t border-base-300 pt-4">
                                    <div>
                                        <h4 class="font-medium">Assignees</h4>
                                        <ul class="mt-2 space-y-1 text-sm">
                                            @forelse ($assignment->assignees as $assignee)
                                                <li>{{ $assignee->first_name }} {{ $assignee->last_name }}</li>
                                            @empty
                                                <li class="text-base-content/70">No one has been assigned to this assignment.</li>
                                            @endforelse
                                        </ul>
                                    </div>

                                    <div>
                                        <h4 class="font-medium">Allowed Job Listings</h4>
                                        <ul class="mt-2 space-y-1 text-sm">
                                            @forelse ($assignment->jobListings as $listing)
                                                <li>{{ $listing->name }}</li>
                                            @empty
                                                <li class="text-base-content/70">No job listings are linked to this assignment.</li>
                                            @endforelse
                                        </ul>
                                    </div>
                                </div>

                                <label class="form-control mt-4 w-full">
                                    <span class="label-text mb-1 font-medium">Resume File</span>
                                    <input type="file" class="file-input file-input-bordered w-full" accept=".pdf,.doc,.docx" />
                                    <span class="label-text-alt text-sm text-base-content/60">Choose a submission</span>
                                </label>
                            </div>
                            <form method="dialog" class="modal-backdrop">
                                <button>close</button>
                            </form>
                        </dialog>
                    @empty
                        <p class="text-sm text-base-content/70">No assignments for this module yet.</p>
                    @endforelse
                </div>
            </article>

            <article class="rounded-box border border-base-300 p-4">
                <header class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold">Job Listings</h3>
                        <p class="mt-1 text-sm text-base-content/70">Relevant postings for this module.</p>
                    </div>
                   <button type="button" class="btn btn-primary btn-sm shrink-0" onclick="create_job_listing_modal.showModal()">
                        Create Job Listing
                    </button>
                </header>

                <ul class="space-y-3">
                    @forelse ($jobListings as $jobListing)
                        <li>
                            <button
                                type="button"
                                class="w-full rounded-box border border-base-300 p-3 text-left transition hover:bg-base-200 cursor-pointer"
                                onclick="description_modal_{{ $jobListing->id }}.showModal()"
                            >
                                <h4 class="font-medium">{{ $jobListing->name }}</h4>
                            </button>
                        </li>
                        @if ($loop->index % 2 === 0)
                        <dialog id="description_modal_{{ $jobListing->id }}" class="modal">
                            <div class="modal-box w-[92vw] max-w-3xl">
                                <form method="POST" action="{{ route('dashboard.modules.job-listings.update', [$module, $jobListing]) }}">
                                    @csrf
                                    @method("PATCH")
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-circle btn-outline absolute right-2 top-2"
                                        onclick="description_modal_{{ $jobListing->id }}.close()"
                                        aria-label="Close"
                                    >
                                        x
                                    </button>
        
                                    <header class="space-y-1">
                                        <h3 class="text-2xl font-bold text-primary">Job listing</h3>
                                    </header>
        
                                    <fieldset class="mt-4 flex flex-col gap-5">
                                        <label class="form-control">
                                            <span class="label-text mb-1">Title</span>
                                            <input
                                                type="text"
                                                name="name"
                                                value="{{ $jobListing->name}}"
                                                placeholder="Job Title"
                                                class="input input-bordered w-full @error('name') input-error @enderror"
                                                required
                                            />
                                            @error('name')
                                                <span class="label-text-alt mt-1 text-error">{{ $message }}</span>
                                            @enderror
                                        </label>
                                        <label 
                                        class="form-control">
                                            <span class="label-text mb-1">Description</span>
                                            <textarea
                                                name="description"
                                                placeholder="Job description and requirements..."
                                                class="textarea textarea-bordered h-64 w-full @error('description') textarea-error @enderror"
                                                required
                                            >{{$jobListing->description}}</textarea>
                                            @error('description')
                                                <span class="label-text-alt mt-1 text-error">{{ $message }}</span>
                                            @enderror
                                        </label>
        
                                        <button 
                                            type="reset" 
                                            class="btn btn-outline"
                                            onclick="description_modal_{{ $jobListing->id }}.close()"
                                        >
                                            Cancel Edits
                                        </button>
                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                    </fieldset>
                                </form>
                            </div>
                            <form method="dialog" class="modal-backdrop">
                                <button type="submit">close</button>
                            </form>
                        </dialog>
                        @else
                        <dialog id="description_modal_{{ $jobListing->id }}" class="modal">
                            <div class="modal-box max-w-lg">
                                <form method="dialog">
                                    <button class="btn btn-sm btn-circle btn-outline absolute right-2 top-2" aria-label="Close">x</button>
                                </form>
                                <h4 class="text-lg font-semibold">{{ $jobListing->name }}</h4>
                                <p class="mt-2 text-sm text-base-content/80">{{ $jobListing->description }}</p>
                            </div>
                            <form method="dialog" class="modal-backdrop">
                                <button>close</button>
                            </form>
                        </dialog>
                        @endif
                    @empty
                        <li class="text-sm text-base-content/70">No job listings available for this module.</li>
                    @endforelse
                </ul>



                <dialog id="create_job_listing_modal" class="modal">
                    <div class="modal-box w-[92vw] max-w-3xl">
                        <form method="POST" action="{{ route('dashboard.modules.job-listings.store', $module) }}">
                            @csrf

                            <button
                                type="button"
                                class="btn btn-sm btn-circle btn-outline absolute right-2 top-2"
                                onclick="create_job_listing_modal.close()"
                                aria-label="Close"
                            >
                                x
                            </button>

                            <header class="space-y-1">
                                <h3 class="text-2xl font-bold text-primary">Create Job Listing</h3>
                            </header>

                            <fieldset class="mt-4 flex flex-col gap-5">
                                <label class="form-control">
                                    <span class="label-text mb-1">Title</span>
                                    <input
                                        type="text"
                                        name="name"
                                        value="{{ old('name') }}"
                                        placeholder="Job Title"
                                        class="input input-bordered w-full @error('name') input-error @enderror"
                                        required
                                    />
                                    @error('name')
                                        <span class="label-text-alt mt-1 text-error">{{ $message }}</span>
                                    @enderror
                                </label>
                                <label 
                                class="form-control">
                                    <span class="label-text mb-1">Description</span>
                                    <textarea
                                        name="description"
                                        placeholder="Job description and requirements..."
                                        class="textarea textarea-bordered h-64 w-full @error('description') textarea-error @enderror"
                                        required
                                    >{{ old('description') }}</textarea>
                                    @error('description')
                                        <span class="label-text-alt mt-1 text-error">{{ $message }}</span>
                                    @enderror
                                </label>

                                <button type="submit" class="btn btn-primary">Create Job Listing</button>
                            </fieldset>
                        </form>
                    </div>
                    <form method="dialog" class="modal-backdrop">
                        <button type="submit">close</button>
                    </form>
                </dialog>
            </article>
        </div>
    </section>
</x-dashboard-layout>
