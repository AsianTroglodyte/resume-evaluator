<x-dashboard-layout>
    <x-slot:title>{{ $group["name"] }}</x-slot:title>

    <section class="space-y-4">
    <header>
    <header class="space-y-1">
        <h2 class="text-2xl font-semibold">{{ $group["name"] }}</h2>
        <p class="text-sm text-base-content/70">
            Make sure to add pending assignment tag thing
            Status: {{ ucfirst($group['status']) }}
        </p>
    </header>
    <div class="grid gap-4 lg:grid-cols-2">
        <div class="rounded-box border border-base-300 bg-base-100 p-4">
            <article>
                <div class="flex justify-between">
                    <h3 class="text-lg font-semibold">Assignments</h3>
                    <a
                    href="{{ route('dashboard.groups.assignments.create', ['id' => request()->route('id')]) }}"
                    class="btn"
                    >
                        Create Assignment
                    </a>
                </div>
                <p class="mb-3 text-sm text-base-content/70">Group tasks and submission targets.</p>
                <div class="space-y-3">
                @forelse ($assignments as $assignment)
                    <div class="rounded-box border border-base-300 p-3">
                        <div class="flex items-start justify-between gap-3">
                            <h4 class="font-medium">{{ $assignment['title'] }}</h4>
                            <span class="badge {{ $assignment['status'] === 'completed' ? 'badge-primary' : 'badge-success' }}">
                                {{ ucfirst($assignment['status']) }}
                            </span>
                        </div>
                        <p class="mt-2 text-sm text-base-content/70">Due: {{ $assignment['due_at'] }}</p>
                    </div>
                @empty
                    <p class="text-sm text-base-content/70">No assignments for this group yet.</p>
                @endforelse
                </div>
            </article>
        </div>
        <article class="rounded-box border border-base-300 bg-base-100 p-4">
            <div class="flex justify-between">
                <h3 class="text-lg font-semibold">Job Listings</h3>
                
                <button type="button" class="btn" onclick="create_job_listing_modal.showModal()">
                    Create Jobs Listing
                </button>

                <dialog id="create_job_listing_modal" class="modal">
                    <div class="modal-box w-[92vw] max-w-3xl">
                        <form method="POST" action="{{ route('dashboard.groups.job-listings.store', $group->id) }}">
                            @csrf

                            <button
                                type="button"
                                class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2"
                                onclick="create_job_listing_modal.close()"
                            >
                                x
                            </button>

                            <header class="space-y-1">
                                <h1 class="text-2xl font-bold text-primary">Create Job Listing</h1>
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

                                <label class="form-control">
                                    <span class="label-text mb-1">Description</span>
                                    <textarea
                                        name="description"
                                        placeholder="Job description and requirements..."
                                        class="textarea textarea-bordered w-full h-64 @error('description') textarea-error @enderror"
                                        required
                                    >{{ old('description') }}</textarea>
                                    @error('description')
                                        <span class="label-text-alt mt-1 text-error">{{ $message }}</span>
                                    @enderror
                                </label>

                                <button type="submit" class="btn btn-neutral">
                                    Create Job Listing
                                </button>
                            </fieldset>
                        </form>
                    </div>
                    <form method="dialog" class="modal-backdrop">
                        <button type="submit">close</button>
                    </form>
                </dialog>
            </div>
            <p class="mb-3 text-sm text-base-content/70">Relevant postings for this group.</p>
            <ul class="space-y-3">
            @forelse ($job_listings as $listing)
                <li class="rounded-box border border-base-300 p-3 cursor-pointer"
                    onclick="description_modal_{{ $listing['id'] }}.showModal()">
                        <h4 class="font-medium">{{ $listing['name'] }}</h4>
                </li>
                <dialog id="description_modal_{{ $listing['id'] }}" class="modal"
                onclick="description_modal_{{ $listing['id'] }}.close()">
                    <div class="modal-box">
                        <h4 class="font-medium">{{ $listing['name'] }}</h4>
                        <p class="mt-2 text-sm text-base-content/80">{{ $listing['description'] }}</p>
                        <form method="dialog">
                            <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">
                                x
                            </button>
                        </form>
                    </div>
                </dialog>
            @empty
                <p class="text-sm text-base-content/70">No job listings available for this group.</p>
            @endforelse
            </ul>
        </article>
    </div>
    </section>
</x-dashboard-layout>