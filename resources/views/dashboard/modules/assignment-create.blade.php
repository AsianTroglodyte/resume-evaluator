<x-dashboard-layout>
    <x-slot:title>Create Assignment</x-slot:title>

    <fieldset class="space-y-4">
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

                <fieldset class="space-y-5 ">

                    <header class="space-y-1 border-b border-base-300 pb-3">
                        <h3 class="text-lg font-semibold">Basics</h3>
                        <p class="text-sm text-base-content/70">Title, due date, and instructions for this assignment.</p>
                    </header>

                    <label class="form-control w-full">
                        <span class="label-text mb-1 font-medium">Title</span>
                        <input
                            type="text"
                            name="title"
                            placeholder="Assignment title"
                            class="input input-bordered w-full"
                        />
                    </label>

                    <div class="flex flex-col gap-2 mt-3">
                        <label class="flex cursor-pointer items-center gap-3">
                            <input
                                type="checkbox"
                                checked
                                class="toggle"
                                id="due-date-enabled"
                                onchange="document.getElementById('date-time').disabled = !this.checked"
                            />
                            <span class="label-text">Enable due date</span>
                        </label>

                        <label class="form-control w-full max-w-xs">
                            <span class="label-text mb-1">Due date</span>
                            <input
                                type="datetime-local"
                                name="due_date"
                                id="date-time"
                                class="input input-bordered w-full"
                            />
                        </label>
                    </div>

                    <label class="form-control w-full">
                        <span class="label-text mb-1 font-medium">Description</span>
                        <textarea
                            name="description"
                            placeholder="Assignment details and instructions..."
                            class="textarea textarea-bordered min-h-32 w-full"
                        ></textarea>
                    </label>
                </fieldset>

                <fieldset class="space-y-5">

                    <header class="space-y-1 border-b border-base-300 pb-3">
                        <h3 class="text-lg font-semibold">Allowed job listings</h3>
                        <p class="text-sm text-base-content/70">Students may submit against any listing you select here.</p>
                    </header>
                    <fieldset class="[&:not(:has(input[value='selected']:checked))_.job-listing-list]:hidden">
                        <label class="flex cursor-pointer items-center gap-3 p-1 transition hover:bg-base-200 rounded">
                            <input type="radio" name="assignment_scope" value="everyone" class="radio radio-primary" checked />
                            <span class="font-medium">Everyone in module</span>
                        </label>

                        <label class="flex cursor-pointer items-center gap-3 p-1 transition hover:bg-base-200 rounded">
                            <input type="radio" name="assignment_scope" value="selected" class="radio radio-primary" />
                            <span class="font-medium">Selected members</span>
                        </label>

                        <div class="job-listing-list mt-4 space-y-3">
                            <label class="form-control w-full">
                                <span class="label-text mb-1 font-medium">Select Members</span>
                            </label>
                            <ul class="list bg-base-100 ">
                                @forelse ($job_listings as $job_listing)
                                <li>
                                    <label 
                                        class="flex cursor-pointer items-center gap-3 p-1 transition hover:bg-base-200 rounded"
                                        for="{{$job_listing->name}} {{ $job_listing->id }}">
                                        <input type="checkbox"
                                            class="checkbox checkbox-md mt-0.5 shrink-0" 
                                            id="{{$job_listing->name}} {{ $job_listing->id }}"
                                            name="job_listing_ids[]"
                                            value="{{$job_listing->id}}"/>
                                        <p class="min-w-0 font-medium text-md">
                                            {{$job_listing->name }}
                                        </p>
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
                        </div>
                    </fieldset>
                </fieldset>

                <fieldset class="space-y-5">
                    <header class="space-y-1 border-b border-base-300 pb-3">
                        <h3 class="text-lg font-semibold">Assignees</h3>
                        <p class="text-sm text-base-content/70">Choose who this assignment applies to.</p>
                    </header>

                    <fieldset class="min-w-0 [&:not(:has(input[value='selected']:checked))_.assignment-member-list]:hidden">
                        <legend class="sr-only">Assignment scope</legend>

                        <label class="flex cursor-pointer items-center gap-3 p-1 transition hover:bg-base-200 rounded">
                            <input type="radio" name="assignment_scope" value="everyone" class="radio radio-primary" checked />
                            <span class="font-medium">Everyone in module</span>
                        </label>

                        <label class="flex cursor-pointer items-center gap-3 p-1 transition hover:bg-base-200 rounded">
                            <input type="radio" name="assignment_scope" value="selected" class="radio radio-primary" />
                            <span class="font-medium">Selected members</span>
                        </label>

                        <div class="assignment-member-list mt-4 space-y-3">
                            <label class="form-control w-full">
                                <span class="label-text mb-1 font-medium">Select Members</span>
                            </label>

                            <ul class="list bg-base-100">
                                @forelse ($users as $user)
                                <li>
                                    <label
                                        class="flex cursor-pointer items-center gap-3 p-1 transition hover:bg-base-200 rounded"
                                        for="user-{{ $user->id }}">
                                        <input type="checkbox"
                                            class="checkbox checkbox-md mt-0.5 shrink-0"
                                            id="user-{{ $user->id }}"
                                            name="assignee_ids[]"
                                            value="{{ $user->id }}"/>
                                        <p class="min-w-0 font-medium">
                                            {{ $user->first_name }} {{ $user->last_name }} -
                                            {{ $user->email }}
                                        </p>
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
                        </div>
                    </fieldset>
                </fieldset>

                <div class="flex flex-wrap justify-end gap-2 border-t border-base-300 pt-4">
                    <a href="{{ route('dashboard.modules.show', $module) }}" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary">Create assignment</button>
                </div>
            </form>
        </article>
    </fieldset>
</x-dashboard-layout>
