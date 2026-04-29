@php
$BASICS = 'basics';
$JOB_LISTINGS = 'job_listings';
$GROUP_MEMBERS = 'group_members';
@endphp

<x-dashboard-layout>
    <x-slot:title>Create Assignment</x-slot:title>

    <section class="space-y-4">
        <header class="space-y-1">
            <a href="{{ route('dashboard.groups.show', ['id' => $group['id']]) }}" class="link link-primary text-sm">&larr; Back to {{ $group_name }}</a>
            <h2 class="text-2xl font-semibold">Create Assignment</h2>
            <p class="text-sm text-base-content/70">Build a new assignment for {{ $group_name }}.</p>
        </header>

        <article class="flex flex-col rounded-box border border-base-300 bg-base-100 p-6 md:flex-row">
            <ul class="steps steps-vertical md:w-48 md:shrink-0 md:border-r md:border-base-300 md:pr-6">
                <li class="step step-primary" data-step="{{ $BASICS }}" data-target="{{ $BASICS }}">Basics</li>
                <li class="step" data-step="{{ $JOB_LISTINGS }}" data-target="{{ $JOB_LISTINGS }}">Job <br/> Listings</li>
                <li class="step" data-step="{{ $GROUP_MEMBERS }}" data-target="{{ $GROUP_MEMBERS }}">Group <br/> Members</li>
            </ul>

            <form id="assignment_form" class="flex flex-col flex-1 min-h-0 gap-5 p-6" method="POST" action="#">
                @csrf

                <fieldset id="{{ $BASICS }}" class="flex flex-col gap-5 h-96 min-h-0">
                    <label class="form-control w-full">
                        <span class="label-text mb-1">Title</span>
                        <input type="text" placeholder="Assignment title" class="input input-bordered w-full" />
                    </label>

                    <label class="label w-fit text-sm gap-3">
                        <input type="checkbox" checked="checked" class="toggle"
                        onchange="document.getElementById('date-time').disabled = !this.checked"/>
                        Enable Due Date
                        <input type="datetime-local" class="input input-bordered" id="date-time"/>
                    </label>

                    <label class="form-control w-full flex flex-col flex-1 min-h-0">
                        <span class="label-text mb-1">Description</span>
                        <textarea placeholder="Job Description" class="textarea textarea-bordered w-full flex-1 min-h-0 h-full"></textarea>
                    </label>

                    <div class="flex justify-end pt-2">
                        <button type="button" class="btn btn-primary" onclick="navigateSteps(1)">
                            Next
                        </button>
                    </div>
                </fieldset>

                <fieldset id="{{ $JOB_LISTINGS }}" class="flex flex-col gap-5 hidden h-96 min-h-0">
                    <label class="label">
                        <input type="checkbox" checked="checked" class="checkbox" />
                        Found Online
                    </label>
                    <label class="label">
                        <input type="checkbox" checked="checked" class="checkbox" />
                        On-Site
                    </label>

                    <label class="form-control w-full">
                        <span class="label-text mb-1">Job listing</span>
                        <select class="select select-bordered w-full">
                            @foreach ($job_listings as $listing)
                                <option value="{{ $listing['id'] }}">{{ $listing['name'] }}</option>
                            @endforeach
                        </select>
                    </label>

                    <div class="flex-1 rounded-box border border-base-300 p-3 text-sm text-base-content/70">
                        Select the listing that this assignment should target.
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <button type="button" class="btn btn-ghost" onclick="navigateSteps(-1)">
                            Back
                        </button>

                        <button type="button" class="btn btn-primary" onclick="navigateSteps(1)">
                            Next
                        </button>
                    </div>
                </fieldset>

                <fieldset id="{{ $GROUP_MEMBERS }}" class="flex flex-col gap-5 hidden h-96 min-h-0">
                    <label class="form-control w-full">
                        <span class="label-text mb-1">Group members</span>
                        <input type="text" placeholder="Search members..." class="input input-bordered w-full" />
                    </label>

                    <div class="flex-1 rounded-box border border-base-300 p-3 text-sm text-base-content/70">
                        Member selection UI placeholder.
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <button type="button" class="btn btn-ghost" onclick="navigateSteps(-1)">
                            Back
                        </button>

                        <button type="submit" class="btn btn-primary">
                            Submit
                        </button>
                    </div>
                </fieldset>
            </form>
        </article>
    </section>

    <script>
        const panels = Array.from(document.querySelectorAll('fieldset[id]'));
        const stepItems = Array.from(document.querySelectorAll('li[data-target]'));
        const stepOrder = stepItems.map((item) => item.dataset.target).filter(Boolean);

        const navigateSteps = (delta) => {
            if (!stepOrder.length) return;

            const activePanel = panels.find((panel) => !panel.classList.contains('hidden'));
            const currentId = activePanel ? activePanel.id : stepOrder[0];
            let currentIndex = stepOrder.indexOf(currentId);
            if (currentIndex === -1) currentIndex = 0;

            const nextIndex = currentIndex + delta;
            if (nextIndex < 0 || nextIndex >= stepOrder.length) return;

            const targetId = stepOrder[nextIndex];

            for (const panel of panels) {
                if (panel.id === targetId) panel.classList.remove('hidden');
                else panel.classList.add('hidden');
            }

            for (const item of stepItems) {
                const itemIndex = stepOrder.indexOf(item.dataset.target);
                item.classList.toggle('step-primary', itemIndex !== -1 && itemIndex <= nextIndex);
            }
        };

        navigateSteps(0);
    </script>
</x-dashboard-layout>
