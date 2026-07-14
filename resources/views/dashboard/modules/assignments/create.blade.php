<x-dashboard-layout>
    <x-slot:title>Create Assignment</x-slot:title>

    <section class="space-y-4">
        <header class="space-y-1">
            <a href="{{ route('dashboard.modules.show', $module) }}" class="link link-primary text-sm">
                &larr; Back to {{ $module->name }}
            </a>
            <h2 class="text-2xl font-semibold">Create Assignment</h2>
            <p class="text-sm text-base-content/70">Build a new assignment for {{ $module->name }}.</p>
        </header>

        <article class="rounded-box border border-base-300 bg-base-100 p-6">
            <form
                id="assignment_form"
                class="flex flex-col gap-8"
                method="POST"
                action="{{ route('dashboard.modules.assignments.store', $module->id) }}"
            >
                @csrf

                <section class="min-w-0 space-y-5" aria-labelledby="assignment-basics-heading">
                    <header class="space-y-1 border-b border-base-300 pb-4">
                        <h3 id="assignment-basics-heading" class="text-lg font-semibold">Basics</h3>
                        <p class="text-sm text-base-content/70">Title, due date, and instructions for this assignment.</p>
                    </header>

                    <label class="form-control w-full">
                        <span class="label-text mb-1 font-medium">Title</span>
                        <input
                            type="text"
                            name="title"
                            placeholder="Assignment title"
                            class="input input-bordered w-full"
                            value="{{ old('title') }}"
                            required
                        />
                        @error('title')
                            <span class="label-text-alt mt-1 text-error">{{ $message }} </span>
                        @enderror
                    </label>

                    <label class="flex w-fit cursor-pointer items-center gap-3 mt-4">
                        <input type="hidden" name="allow_resubmission" value="0" />
                        <input
                            type="checkbox"
                            name="allow_resubmission"
                            class="toggle"
                            @checked(old('allow_resubmission', true))
                        />
                        <span class="label-text">Allow resubmissions</span>
                    </label>
                    @error('allow_resubmission')
                        <span class="label-text-alt mt-1 text-error">{{ $message }} </span>
                    @enderror
                    

                    <div class="mt-4 flex flex-col gap-2 [&:not(:has(#due-date-enabled:checked))_.due-date-input]:hidden">
                        <label class="flex w-fit cursor-pointer items-center gap-3">
                            <input type="hidden" name="due_date_enabled" value="0"/>
                            <input
                                type="checkbox"
                                name="due_date_enabled"
                                class="toggle"
                                id="due-date-enabled"
                                @checked(old('due_date_enabled', true))
                            />
                            <span class="label-text">Enable due date</span>
                        </label>
                        @error('due_date_enabled')
                            <span class="label-text-alt mt-1 text-error">{{ $message }} </span>
                        @enderror

                        <label class="due-date-input form-control w-full max-w-xs">
                            <span class="label-text mb-1">Due date</span>
                            <input
                                type="datetime-local"
                                name="due_at"
                                class="input input-bordered w-full"  
                                value="{{ old('due_at') }}"
                            />
                        </label>
                    </div>

                    <label class="form-control w-full">
                        <span class="label-text mb-1 font-medium">Description</span>
                        <textarea
                            name="description"
                            placeholder="Assignment details and instructions..."
                            class="textarea textarea-bordered min-h-32 w-full"
                        >{{ old('description') }}</textarea>
                    </label>
                </section>

                <section class="min-w-0 space-y-5" aria-labelledby="allowed-job-listings-heading">
                    <header class="space-y-1 border-b border-base-300 pb-3">
                        <h3 id="allowed-job-listings-heading" class="text-lg font-semibold">Allowed job listings</h3>
                        <p class="text-sm text-base-content/70">Students may submit against any listing you select here.</p>
                    </header>

                    <fieldset
                        id="job-listing-sources"
                        class="min-w-0 space-y-3 [&:not(:has(.job-source-module:checked)):not(:has(.job-source-both:checked))_.module-listing-options]:hidden 
                        [&:not(:has(.job-listing-scope-selected:checked))_.job-listing-list]:hidden"
                    >
                        <legend class="text-sm font-semibold">Allowed job sources</legend>

                        <label class="flex cursor-pointer items-center gap-3 rounded p-1 transition hover:bg-base-200">
                            <input
                                type="radio"
                                name="job_listing_source"
                                value="external"
                                class="radio radio-primary"
                                required
                                @checked(old('job_listing_source', 'external') === "external")
                                checked
                            />
                            <span class="font-medium">External job listings only</span>
                        </label>

                        <label class="flex cursor-pointer items-center gap-3 rounded p-1 transition hover:bg-base-200">
                            <input
                                type="radio"
                                name="job_listing_source"
                                value="module"
                                class="job-source-module radio radio-primary"
                                @checked(old('job_listing_source', 'external') === "module") 
                            />
                            <span class="font-medium">Module job listings only</span>
                        </label>

                        <label class="flex cursor-pointer items-center gap-3 rounded p-1 transition hover:bg-base-200">
                            <input
                                type="radio"
                                name="job_listing_source"
                                value="both"
                                class="job-source-both radio radio-primary"
                                @checked(old('job_listing_source', 'external') === "both") 
                            />
                            <span class="font-medium">Both external and module job listings</span>
                        </label>

                        <fieldset id="module-listing-options" class="module-listing-options min-w-0 space-y-3">
                            <legend class="text-sm font-semibold">On-site module listings</legend>

                            <label class="flex cursor-pointer items-center gap-3 rounded p-1 transition hover:bg-base-200">
                                <input
                                    type="radio"
                                    name="module_job_listing_scope"
                                    value="all"
                                    class="radio radio-primary"
                                    @checked(old('module_job_listing_scope', 'all') === 'all') 
                                />
                                <span class="font-medium">All module job listings</span>
                            </label>

                            <label class="flex cursor-pointer items-center gap-3 rounded p-1 transition hover:bg-base-200">
                                <input
                                    type="radio"
                                    name="module_job_listing_scope"
                                    value="selected"
                                    class="job-listing-scope-selected radio radio-primary"
                                    @checked(old('module_job_listing_scope', 'selected') === 'selected') 
                                />
                                <span class="font-medium">Select job listings</span>
                            </label>

                            <fieldset id="job-listing-list" class="job-listing-list min-w-0 space-y-3">
                                <legend class="label-text font-medium">Select job listings</legend>

                                <ul class="list max-h-150 overflow-y-auto bg-base-100">
                                @forelse ($job_listings as $job_listing)
                                    <li>
                                        <label
                                            class="flex cursor-pointer items-center gap-3 rounded p-1 transition hover:bg-base-200"
                                            for="job-listing-{{ $job_listing->id }}"
                                        >
                                            <input
                                                type="checkbox"
                                                class="checkbox checkbox-md mt-0.5 shrink-0"
                                                id="job-listing-{{ $job_listing->id }}"
                                                name="job_listing_ids[]"
                                                value="{{ $job_listing->id }}"
                                                @checked(in_array($job_listing->id, old('job_listing_ids', [])))
                                            />
                                            <span class="min-w-0 font-medium">
                                                {{ $job_listing->name }}
                                            </span>
                                        </label>
                                    </li>
                                @empty
                                    <li>
                                        <p class="rounded-box border border-base-300 p-4 text-sm text-base-content/70">
                                            No job listings in this module yet. Create one from the module overview before assigning.
                                        </p>
                                    </li>
                                @endforelse
                                </ul>
                            </fieldset>
                        </fieldset>
                    </fieldset>
                </section>

                <section class="min-w-0 space-y-5" aria-labelledby="assignment-assignees-heading">
                    <header class="space-y-1 border-b border-base-300 pb-3">
                        <h3 id="assignment-assignees-heading" class="text-lg font-semibold">Assignees</h3>
                        <p class="text-sm text-base-content/70">Choose who this assignment applies to.</p>
                    </header>

                    <fieldset
                        id="assignment-scope"
                        class="min-w-0 space-y-3 [&:not(:has(.assignment-scope-selected:checked))_.assignment-member-list]:hidden"
                    >
                        <legend class="sr-only">Assignment scope</legend>

                        <label class="flex cursor-pointer items-center gap-3 rounded p-1 transition hover:bg-base-200">
                            <input
                                type="radio"
                                name="assignee_scope"
                                value="everyone"
                                class="radio radio-primary"
                                @checked(old('assignee_scope', 'everyone') === 'everyone') 
                            />
                            <span class="font-medium">Everyone in module</span>
                        </label>

                        <label class="flex cursor-pointer items-center gap-3 rounded p-1 transition hover:bg-base-200">
                            <input
                                type="radio"
                                name="assignee_scope"
                                value="selected"
                                class="assignment-scope-selected radio radio-primary"
                                @checked(old('assignee_scope', 'everyone') === 'selected') 
                            />
                            <span class="font-medium">Select members</span>
                        </label>

                        <fieldset id="assignment-member-list" class="assignment-member-list min-w-0 space-y-3">
                            <legend class="label-text font-medium">Select members</legend>

                            <ul class="list max-h-150 overflow-y-auto bg-base-100">
                            @forelse ($users as $user)
                                <li>
                                    <label
                                        class="flex cursor-pointer items-center gap-3 rounded p-1 transition hover:bg-base-200"
                                        for="user-{{ $user->id }}"
                                    >
                                        <input
                                            type="checkbox"
                                            class="checkbox checkbox-md mt-0.5 shrink-0"
                                            id="user-{{ $user->id }}"
                                            name="assignee_ids[]"
                                            value="{{ $user->id }}"
                                            @checked(in_array($user->id, old('assignee_ids', [])))
                                        />
                                        <span class="min-w-0 font-medium">
                                            {{ $user->first_name }} {{ $user->last_name }} -
                                            {{ $user->email }}
                                        </span>
                                    </label>
                                </li>
                            @empty
                                <li>
                                    <p class="rounded-box border border-base-300 p-4 text-sm text-base-content/70">
                                        No members in this module yet.
                                    </p>
                                </li>
                            @endforelse
                            </ul>
                        </fieldset>
                    </fieldset>
                </section>

                @if($errors->any())
                    <ul = "
                    @foreach($errors->all() as $error) 
                        <li> {{$error}}</li>
                    @endforeach
                    </ul>
                @endif

                <div class="flex flex-wrap justify-end gap-2 border-t border-base-300 pt-4">
                    <a href="{{ route('dashboard.modules.show', $module) }}" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-primary">Create assignment</button>
                </div>


            </form>
        </article>
    </section>
</x-dashboard-layout>
