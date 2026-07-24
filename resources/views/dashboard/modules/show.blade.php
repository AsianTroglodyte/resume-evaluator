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
                
                {{-- <x-assignment --}}

                <x-assignments :assignments="$assignments" :module="$module"/> 
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
                                            <input                                                type="text"
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
                        @if ($errors->has('description') || $errors->has('name'))
                            <script>
                                document.getElementById('create_job_listing_modal')?.showModal();
                            </script>
                        @endif
                    @endcan
                </header>

                <ul class="space-y-1">
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
