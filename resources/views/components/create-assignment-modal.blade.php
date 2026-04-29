@props([
    'modalId' => 'create_assignment_modal',
    'triggerLabel' => 'Create Assignment',
    'title' => 'Create Assignment Listing',
    'subtitle' => 'Add assignment details for this group.',
    'formAction' => '#',
    'jobListings' => [],
])

@php
    $basicsId = "{$modalId}_basics";
    $jobListingsId = "{$modalId}_job_listings";
    $groupMembersId = "{$modalId}_group_members";
@endphp

<button class="btn" type="button" onclick="document.getElementById('{{ $modalId }}').showModal()">
    {{ $triggerLabel }}
</button>

<dialog id="{{ $modalId }}" class="modal">
    <div class="modal-box w-[92vw] sm:w-full max-w-4xl flex flex-col bg-base-100 shadow-xl border border-base-300 p-0 max-h-[85vh] overflow-hidden">
        <div class="card-body flex flex-col gap-4 p-6 flex-1 min-h-0 overflow-y-auto">
            <header class="space-y-1">
                <h1 class="text-2xl font-bold text-primary">{{ $title }}</h1>
                <p class="text-sm text-base-content/70">{{ $subtitle }}</p>
            </header>

            <div class="flex flex-col gap-6 flex-1 min-h-0 md:flex-row">
                <ul class="steps steps-vertical md:w-48 md:shrink-0 md:border-r md:border-base-300 md:pr-6">
                    <li class="step step-primary" data-step-target="{{ $basicsId }}">Basics</li>
                    <li class="step" data-step-target="{{ $jobListingsId }}">Job Listings</li>
                    <li class="step" data-step-target="{{ $groupMembersId }}">Group Members</li>
                </ul>

                <form class="flex flex-col flex-1 min-h-0 gap-5" method="POST" action="{{ $formAction }}">
                    @csrf

                    <fieldset id="{{ $basicsId }}" class="flex flex-col gap-5 h-96 min-h-0">
                        <label class="form-control w-full">
                            <span class="label-text mb-1">Title</span>
                            <input type="text" placeholder="Assignment title" class="input input-bordered w-full" />
                        </label>

                        <label class="label w-fit text-sm gap-3">
                            <input type="checkbox" checked="checked" class="toggle"
                            onchange="document.getElementById('{{ $modalId }}_date_time').disabled = !this.checked"/>
                            Enable Due Date
                        </label>

                        <input type="datetime-local" class="input input-bordered w-full max-w-xs" id="{{ $modalId }}_date_time"/>

                        <label class="form-control w-full flex flex-col flex-1 min-h-0">
                            <span class="label-text mb-1">Description</span>
                            <textarea placeholder="Assignment details and instructions..." class="textarea textarea-bordered w-full flex-1 min-h-0 h-full"></textarea>
                        </label>

                        <div class="flex justify-end pt-2">
                            <button type="button" class="btn btn-primary" data-step-nav="1">Next</button>
                        </div>
                    </fieldset>

                    <fieldset id="{{ $jobListingsId }}" class="flex flex-col gap-5 hidden h-96 min-h-0">
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
                                @foreach ($jobListings as $listing)
                                    <option value="{{ $listing['id'] }}">{{ $listing['name'] }}</option>
                                @endforeach
                            </select>
                        </label>

                        <div class="flex-1 rounded-box border border-base-300 p-3 text-sm text-base-content/70">
                            Select the listing that this assignment should target.
                        </div>

                        <div class="flex items-center justify-between pt-2">
                            <button type="button" class="btn btn-ghost" data-step-nav="-1">Back</button>
                            <button type="button" class="btn btn-primary" data-step-nav="1">Next</button>
                        </div>
                    </fieldset>

                    <fieldset id="{{ $groupMembersId }}" class="flex flex-col gap-5 hidden h-96 min-h-0">
                        <label class="form-control w-full">
                            <span class="label-text mb-1">Group members</span>
                            <input type="text" placeholder="Search members..." class="input input-bordered w-full" />
                        </label>

                        <div class="flex-1 rounded-box border border-base-300 p-3 text-sm text-base-content/70">
                            Member selection UI placeholder.
                        </div>

                        <div class="flex items-center justify-between pt-2">
                            <button type="button" class="btn btn-ghost" data-step-nav="-1">Back</button>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>

        <form method="dialog">
            <button type="submit" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">x</button>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button type="submit">close</button>
    </form>
</dialog>

<script>
    (() => {
        const modal = document.getElementById(@js($modalId));
        if (!modal) return;

        const panels = Array.from(modal.querySelectorAll('fieldset[id]'));
        const stepItems = Array.from(modal.querySelectorAll('li[data-step-target]'));
        const stepOrder = stepItems.map((item) => item.dataset.stepTarget).filter(Boolean);

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
                panel.classList.toggle('hidden', panel.id !== targetId);
            }

            for (const item of stepItems) {
                const itemIndex = stepOrder.indexOf(item.dataset.stepTarget);
                item.classList.toggle('step-primary', itemIndex !== -1 && itemIndex <= nextIndex);
            }
        };

        for (const button of modal.querySelectorAll('[data-step-nav]')) {
            button.addEventListener('click', () => {
                navigateSteps(Number(button.dataset.stepNav));
            });
        }

        navigateSteps(0);
    })();
</script>
