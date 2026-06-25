@php
    use App\Enums\GlobalRole;
    use App\Models\Assignment;
    use App\Models\JobListing;
@endphp

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
                    @can('create', [Assignment::class, $module])
                        <a  href="{{ route('dashboard.modules.assignments.create', $module) }}"
                            class="btn btn-primary btn-sm shrink-0"
                        >
                            Create Assignment
                        </a>
                    @endcan
                </header>

                <div class="space-y-3">
                    @forelse ($assignments as $assignment)
                        @can('view', $assignment)
                        <div
                            class="flex flex-row relative justify-between
                            w-full rounded-box border border-base-300 p-3 text-left transition hover:bg-base-200"
                        >
                            <a class="absolute inset-0 z-0"
                            href={{ route('dashboard.modules.assignments.show', [$module, $assignment]) }}>
                            </a>
                            <div class="flex flex-col justify-between">
                                <h4 class="font-medium">{{ $assignment->title }}</h4>
                                <p class="mt-2 text-sm text-base-content/70">
                                    Due: {{ $assignment->due_at?->format('M j, Y g:i A') ?? 'No due date' }}
                                </p>
                            </div>

                            <button
                                type="button"
                                class="btn btn-ghost btn-sm btn-square relative z-10"
                                popovertarget="popover-{{ $assignment->id }}"
                                style="anchor-name:--anchor-{{ $assignment->id }}"
                                aria-label="Assignment actions"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10 3a1.5 1.5 0 110 3 1.5 1.5 0 010-3zM10 8.5a1.5 1.5 0 110 3 1.5 1.5 0 010-3zM11.5 15.5a1.5 1.5 0 10-3 0 1.5 1.5 0 003 0z" />
                                </svg>
                            </button>
                            <ul
                                class="dropdown menu z-20 w-52 rounded-box bg-base-100 shadow-sm"
                                popover
                                id="popover-{{ $assignment->id }}"
                                style="position-anchor:--anchor-{{ $assignment->id }}"
                            >
                                <li>
                                    <a href="{{ route('dashboard.modules.assignments.edit', [$module, $assignment]) }}">
                                        Edit
                                    </a>
                                </li>
                                <li>
                                    <button
                                        type="button"
                                        class="text-error"
                                        onclick="assignment_delete_modal_{{ $assignment->id }}.showModal()"
                                    >
                                        Delete
                                    </button>
                                </li>
                            </ul>
                        </div>
                        <dialog id="assignment_delete_modal_{{$assignment->id}}" class="modal">
                            <div class="modal-box">
                                <h3 class="text-lg font-bold">Delete Assignment?</h3>
                                <p class="py-4">
                                    This will delete {{$assignment->title}} and any data it contains
                                </p>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-circle btn-outline absolute right-2 top-2"
                                    onclick="assignment_delete_modal_{{ $assignment->id }}.close()"
                                    aria-label="Close"
                                >
                                    x
                                </button>
                                <form 
                                    method="POST" 
                                    action="{{ route('dashboard.modules.assignments.delete', [$module, $assignment]) }}"> 
                                @csrf
                                @method("DELETE")
                                    <div class="flex flex-row justify-between">
                                        <button type="button" class="btn btn-outline"
                                        onclick="assignment_delete_modal_{{ $assignment->id }}.close()">
                                            Cancel
                                        </button>
                                        <button type="submit" class="btn btn-error">
                                            Delete
                                        </button>
                                    </div>
                                </form>
                            </div>
                            <form method="dialog" class="modal-backdrop">
                                <button>close</button>
                            </form>
                        </dialog>
                        @endcan
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
                    @can('create', [JobListing::class, $module])
                        <button type="button" class="btn btn-primary btn-sm shrink-0" onclick="create_job_listing_modal.showModal()">
                            Create Job Listing
                        </button>
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
                    @endcan
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

                            @can('update', $jobListing)
                                <x-job-listing-edit-modal
                                    :jobListing="$jobListing"
                                    :module="$module"/>
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
                            @endcan
                        </li>
                    @empty
                        <li class="text-sm text-base-content/70">No job listings available for this module.</li>
                    @endforelse
                </ul>
            </article>
        </div>
    </section>
</x-dashboard-layout>
