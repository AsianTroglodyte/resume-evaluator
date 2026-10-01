<x-dashboard-layout>
    <x-slot:title>{{ $workspace->name }}</x-slot:title>

    <section class="space-y-6">
        <header data-workspace-rename data-original-name="{{ $workspace->name }}">
            <a
                href="{{ route('dashboard.workspaces.index') }}"
                class="text-sm text-base-content/60 hover:text-base-content">
                ← Back to workspaces
            </a>

            <div data-rename-view class="mt-2 flex max-w-xl items-center gap-2">
                <h1 data-rename-display class="text-2xl font-semibold">{{ $workspace->name }}</h1>
                <button
                    type="button"
                    class="btn btn-ghost btn-sm btn-square shrink-0"
                    data-rename-start
                    aria-label="Rename workspace">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                    </svg>
                </button>
            </div>

            <form data-rename-edit class="mt-2 hidden max-w-xl space-y-2"
                method="POST"
                action="{{ route('dashboard.workspaces.update', $workspace) }}">
                @csrf
                @method('PATCH')
                <label class="form-control w-full">
                    <span class="label-text mb-1 font-medium">Workspace name</span>
                    <input
                        type="text"
                        class="input input-bordered w-full"
                        name="workspace_name"
                        data-rename-input
                        value="{{ old('name', $workspace->name) }}"
                        placeholder="Workspace name"
                        autocomplete="off"
                        required
                        minlength="3" />
                </label>
                <div class="flex flex-wrap gap-2 mt-2">
                    {{-- Wire to PATCH route when rename is implemented --}}
                    <button type="submit" class="btn btn-primary btn-sm" data-rename-save>
                        Save
                    </button>
                    <button type="cancel" class="btn btn-outline btn-sm" data-rename-cancel>
                        Cancel
                    </button>
                </div>
            </form>

            @error('workspace_name')
                <span class="label-text-alt mt-1 text-error">{{ $message }}</span>
            @enderror

            <p class="mt-1 text-sm text-base-content/70">
                Upload a resume and optionally add a job description to run a practice evaluation.
            </p>
        </header>

        <section class="rounded-box border border-base-300 bg-base-100">
            <div class="border-b border-base-300 px-4 py-3 sm:px-6">
                <h2 class="font-semibold">New evaluation</h2>
                <p class="text-sm text-base-content/60">Resume file is required. Job description is optional.</p>
            </div>
            <form
                class="flex flex-col gap-4 px-4 py-5 sm:px-6"
                method="POST"
                enctype="multipart/form-data"
                action="{{ route('dashboard.workspaces.evaluations.store', $workspace) }}"
            >
                @csrf
                <div class="form-control w-full">
                    <label class="label-text mb-1 font-medium" for="resume_file">Resume file</label>
                    <input
                        id="resume_file"
                        type="file"
                        name="resume_file"
                        class="file-input file-input-bordered w-full"
                        accept=".pdf,.doc,.docx,.txt" />
                    @error('resume_file')
                    <span class="label-text-alt mt-1 text-error">{{ $message }}</span>
                    @enderror
                </div>
                <span class="label-text-alt mt-1 text-base-content/60">
                    Accepted formats: PDF, DOC, DOCX, TXT
                </span>
                <div class="flex flex-col gap-4 [&:has(.listing-job-context:checked)_.pasted-job-description-hint]:hidden [&:not(:has(.listing-job-context:checked))_.listing-job-description-hint]:hidden">
                    @if ($practiceJobContexts->isNotEmpty())
                    @php
                        $selectedPracticeListingId = (int) old('job_listing_id', session('job_listing_id'));
                        $claimedContexts = $practiceJobContexts->whereNotNull('claimedJobListingId');
                        $browsedListing = $practiceJobContexts
                            ->flatMap(fn (array $context) => $context['listings'])
                            ->first(fn ($listing) => $listing->id === $selectedPracticeListingId
                                && ! $claimedContexts->contains('claimedJobListingId', $listing->id));
                    @endphp
                    <div class="form-control w-full">
                        <div class="mb-1 flex items-center justify-between gap-2">
                            <label class="label-text font-medium" for="job_listing_id">Job context</label>
                            <button type="button" class="btn btn-ghost btn-xs" onclick="practice_listings_modal.showModal()">
                                Browse all listings
                            </button>
                        </div>
                        <select id="job_listing_id" name="job_listing_id" class="select select-bordered w-full" onchange="syncPracticeJobDescription()">
                            <option value="">Paste my own job description</option>
                            @foreach ($claimedContexts as $context)
                                @php
                                    $claimedListing = $context['listings']->firstWhere('id', $context['claimedJobListingId']);
                                @endphp
                                <option
                                    class="listing-job-context"
                                    value="{{ $claimedListing->id }}"
                                    data-description="{{ $claimedListing->description }}"
                                    @selected($selectedPracticeListingId === $claimedListing->id)
                                >
                                    {{ $claimedListing->name }} — {{ $context['assignment']->title }} (your claim)
                                </option>
                            @endforeach
                            @if ($browsedListing)
                                <option class="listing-job-context" value="{{ $browsedListing->id }}" data-description="{{ $browsedListing->description }}" data-browsed selected>
                                    {{ $browsedListing->name }}
                                </option>
                            @endif
                        </select>
                        @error('job_listing_id')
                        <span class="label-text-alt mt-1 text-error">{{ $message }}</span>
                        @else
                        <span class="label-text-alt mt-1 text-base-content/60">
                            Use a listing you've claimed, or browse other listings on your assignments. Practising never changes a claim.
                        </span>
                        @enderror
                    </div>
                    @endif

                    <div class="form-control w-full">
                        <label class="label-text mb-1 font-medium" for="practice_job_description">Job description <span class="font-normal text-base-content/50">(optional)</span></label>
                        <textarea
                            id="practice_job_description"
                            name="job_description"
                            class="textarea textarea-bordered min-h-28 max-h-60 w-full text-sm"
                            placeholder="Paste a role description for targeted feedback and keyword analysis.">{{ session('job_description') }}</textarea>
                        <span class="pasted-job-description-hint label-text-alt text-sm text-base-content/60">
                            Leave blank for a general quality evaluation without keyword analysis.
                        </span>
                        <span class="listing-job-description-hint label-text-alt text-sm text-base-content/60">
                            From the selected listing. Choose "Paste my own job description" to edit.
                        </span>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="btn btn-primary btn-sm">
                        Run evaluation
                    </button>
                </div>
            </form>

            @if ($practiceJobContexts->isNotEmpty())
            <dialog id="practice_listings_modal" class="modal">
                <div class="modal-box w-[92vw] max-w-2xl">
                    <button
                        type="button"
                        class="btn btn-sm btn-circle btn-outline absolute right-2 top-2"
                        onclick="practice_listings_modal.close()"
                        aria-label="Close"
                    >
                        ×
                    </button>

                    <header class="space-y-1 pr-10">
                        <h3 class="text-xl font-bold text-primary">Practice job listings</h3>
                        <p class="text-sm text-base-content/70">
                            Listings on your assignments. "Use this" only sets this practice run's job description.
                            Claiming reserves a slot on the assignment, the same as claiming from the assignment page.
                        </p>
                    </header>

                    @if (session('claimStatus'))
                        <div role="status" class="alert alert-success alert-soft mt-3 py-2 text-sm">{{ session('claimStatus') }}</div>
                    @endif
                    @if ($errors->getBag('claim')->has('job_listing_id'))
                        <div role="alert" class="alert alert-error alert-soft mt-3 py-2 text-sm">{{ $errors->getBag('claim')->first('job_listing_id') }}</div>
                    @endif

                    <div class="mt-4 space-y-5">
                        @php
                            $isStillActive = fn (array $context): bool => ! $context['hasSubmitted'] && ! $context['assignment']->isPastDue();
                        @endphp
                        @foreach ($practiceJobContexts->groupBy(fn (array $context) => $context['assignment']->module->name) as $moduleName => $moduleContexts)
                            <section class="space-y-1">
                                <h4 class="px-1 text-xs font-semibold uppercase tracking-wide text-base-content/50">{{ $moduleName }}</h4>
                                <div class="divide-y divide-base-300 rounded-box border border-base-300">
                                    @foreach ($moduleContexts->sortBy(fn (array $context) => $isStillActive($context) ? 0 : 1) as $context)
                                        @php
                                            $assignment = $context['assignment'];
                                            $canClaim = auth()->user()->can('claim', $assignment);
                                            $claimedListingName = $context['listings']->firstWhere('id', $context['claimedJobListingId'])?->name;
                                        @endphp
                                        <details class="group" data-practice-assignment="{{ $assignment->id }}" @if ($isStillActive($context)) open @endif>
                                            <summary class="flex cursor-pointer list-none flex-wrap items-center justify-between gap-x-3 gap-y-1 px-3 py-2 hover:bg-base-200 [&::-webkit-details-marker]:hidden">
                                                <span class="flex min-w-0 items-center gap-2">
                                                    <span class="text-xs text-base-content/50 transition-transform group-open:rotate-90" aria-hidden="true">&#9656;</span>
                                                    <span class="min-w-0">
                                                        <span class="block text-sm font-medium">{{ $assignment->title }}</span>
                                                        <span class="block text-xs text-base-content/60">
                                                            {{ $claimedListingName ? 'Claimed: '.$claimedListingName : 'No claim' }}
                                                            · {{ $context['listings']->count() }} {{ Str::plural('listing', $context['listings']->count()) }}
                                                        </span>
                                                    </span>
                                                </span>
                                                <span class="flex items-center gap-2 text-xs">
                                                    <span @class(['text-error' => $assignment->isPastDue(), 'text-base-content/60' => ! $assignment->isPastDue()])>
                                                        @if ($assignment->due_date)
                                                            Due {{ $assignment->due_date->format('M j, g:i A') }}{{ $assignment->isPastDue() ? ' (past due)' : '' }}
                                                        @else
                                                            No due date
                                                        @endif
                                                    </span>
                                                    @if ($context['hasSubmitted'])
                                                        <span class="badge badge-success badge-sm">Submitted</span>
                                                    @else
                                                        <span class="badge badge-ghost badge-sm">Not submitted</span>
                                                    @endif
                                                </span>
                                            </summary>
                                            <ul class="divide-y divide-base-300/60 border-t border-base-300 bg-base-200/30">
                                                @foreach ($context['listings'] as $listing)
                                                    <li class="group/listing">
                                                        <div class="flex items-center gap-2 py-1.5 pl-8 pr-3">
                                                            <button
                                                                type="button"
                                                                class="flex min-w-0 flex-1 items-center gap-2 text-left text-sm hover:text-primary"
                                                                onclick="togglePracticeListingDescription(this)"
                                                                title="Show job description"
                                                            >
                                                                <span class="text-[0.6rem] text-base-content/40 transition-transform group-[.is-open]/listing:rotate-90" aria-hidden="true">&#9656;</span>
                                                                <span class="truncate">{{ $listing->name }}</span>
                                                                @if ($listing->id === $context['claimedJobListingId'])
                                                                    <span class="badge badge-primary badge-xs shrink-0">Your claim</span>
                                                                @endif
                                                            </button>
                                                            <span class="shrink-0 text-xs tabular-nums text-base-content/60">
                                                                @if ($listing->capacity === null)
                                                                    {{ $listing->claims_count }} claimed
                                                                @else
                                                                    {{ $listing->claims_count }}/{{ $listing->capacity }} slots
                                                                @endif
                                                            </span>
                                                            <button
                                                                type="button"
                                                                class="btn btn-outline btn-xs shrink-0"
                                                                data-listing-id="{{ $listing->id }}"
                                                                data-listing-label="{{ $listing->name }} — {{ $assignment->title }}"
                                                                data-listing-description="{{ $listing->description }}"
                                                                onclick="usePracticeListing(this)"
                                                            >
                                                                Use this
                                                            </button>
                                                            @if ($canClaim)
                                                                @if ($listing->id === $context['claimedJobListingId'])
                                                                    <form method="POST" action="{{ route('dashboard.modules.assignments.claim.destroy', [$assignment->module_id, $assignment]) }}" class="shrink-0"
                                                                        onsubmit="return confirm(@js('Release your claim on '.$listing->name.'? Someone else may take the slot.'))">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <input type="hidden" name="workspace_id" value="{{ $workspace->id }}" />
                                                                        <button type="submit" class="btn btn-ghost btn-xs w-24">Release</button>
                                                                    </form>
                                                                @elseif ($listing->isFull())
                                                                    <span class="badge badge-ghost badge-sm w-24 shrink-0">Full</span>
                                                                @else
                                                                    <form method="POST" action="{{ route('dashboard.modules.assignments.claim.update', [$assignment->module_id, $assignment]) }}" class="shrink-0"
                                                                        @if ($context['claimedJobListingId'])
                                                                            onsubmit="return confirm(@js('Switch your claim to '.$listing->name.'? Your current slot will be released.'))"
                                                                        @endif
                                                                    >
                                                                        @csrf
                                                                        @method('PUT')
                                                                        <input type="hidden" name="job_listing_id" value="{{ $listing->id }}" />
                                                                        <input type="hidden" name="workspace_id" value="{{ $workspace->id }}" />
                                                                        <button type="submit" class="btn btn-primary btn-xs w-24">
                                                                            {{ $context['claimedJobListingId'] ? 'Switch claim' : 'Claim' }}
                                                                        </button>
                                                                    </form>
                                                                @endif
                                                            @endif
                                                        </div>
                                                        <p class="hidden whitespace-pre-line pb-3 pl-12 pr-3 text-xs text-base-content/80 group-[.is-open]/listing:block">{{ $listing->description }}</p>
                                                    </li>
                                                @endforeach
                                                <li class="py-1.5 pl-8 pr-3">
                                                    <a href="{{ route('dashboard.modules.assignments.show', [$assignment->module_id, $assignment]) }}" class="link link-hover text-xs text-base-content/60">
                                                        Open assignment
                                                    </a>
                                                </li>
                                            </ul>
                                        </details>
                                    @endforeach
                                </div>
                            </section>
                        @endforeach
                    </div>
                </div>
                <form method="dialog" class="modal-backdrop">
                    <button type="submit">close</button>
                </form>
            </dialog>
            @if (session('claimStatus') || $errors->getBag('claim')->any())
                <script>
                    document.getElementById('practice_listings_modal')?.showModal();
                </script>
            @endif
            <script>
                function togglePracticeListingDescription(button) {
                    const row = button.closest('li');
                    const wasOpen = row.classList.contains('is-open');

                    document.querySelectorAll('#practice_listings_modal li.is-open').forEach((openRow) => openRow.classList.remove('is-open'));
                    row.classList.toggle('is-open', ! wasOpen);
                }

                function usePracticeListing(button) {
                    const select = document.getElementById('job_listing_id');
                    let option = select.querySelector(`option[value="${button.dataset.listingId}"]`);

                    if (! option) {
                        select.querySelector('option[data-browsed]')?.remove();
                        option = new Option(button.dataset.listingLabel, button.dataset.listingId);
                        option.className = 'listing-job-context';
                        option.dataset.browsed = '';
                        option.dataset.description = button.dataset.listingDescription;
                        select.add(option);
                    }

                    select.value = button.dataset.listingId;
                    syncPracticeJobDescription();
                    practice_listings_modal.close();
                }

                function syncPracticeJobDescription() {
                    const select = document.getElementById('job_listing_id');
                    const textarea = document.getElementById('practice_job_description');
                    const listingDescription = select.selectedOptions[0]?.dataset.description;

                    if (select.value) {
                        if (! textarea.disabled) {
                            textarea.dataset.pasted = textarea.value;
                        }
                        textarea.value = listingDescription ?? '';
                        textarea.disabled = true;
                    } else if (textarea.disabled) {
                        textarea.value = textarea.dataset.pasted ?? '';
                        textarea.disabled = false;
                    }
                }

                syncPracticeJobDescription();
            </script>
            @endif
        </section>

        <section class="space-y-4">
            <div class="px-1">
                <h2 class="font-semibold">Recent evaluations</h2>
                <p class="text-sm text-base-content/60">Showing the five most recent results for this workspace.</p>
            </div>

            @if (session('evaluation_error'))
            <p class="text-sm text-error">{{ session('evaluation_error') }}</p>
            @endif

            <div class="space-y-2">
                @forelse ($workspace->latestEvaluations()->with('jobListing:id,name')->get() as $evaluation)
                    @php
                        $keywordMatch = is_array($evaluation->evaluation_data) ? ($evaluation->evaluation_data['keyword_match'] ?? null) : null;
                        $statusBadgeClass = match ($evaluation->status) {
                            \App\Enums\EvaluationStatus::Completed => 'badge-success',
                            \App\Enums\EvaluationStatus::Failed => 'badge-error',
                            default => 'badge-ghost',
                        };
                    @endphp
                    <a
                        href="{{ route('dashboard.workspaces.evaluations.show', [$workspace, $evaluation]) }}"
                        class="flex flex-col gap-2 rounded-box border border-base-300 bg-base-100 p-4 transition hover:bg-base-200 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div class="min-w-0">
                            <p class="font-medium">{{ $evaluation->created_at->toDayDateTimeString() }}</p>
                            <p class="truncate text-sm text-base-content/60">
                                @if ($evaluation->jobListing)
                                    Against {{ $evaluation->jobListing->name }}
                                @elseif (filled($evaluation->job_description_text))
                                    Against a pasted job description
                                @else
                                    General review (no job description)
                                @endif
                            </p>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            @if (is_numeric($keywordMatch))
                                <span class="badge badge-outline badge-primary">{{ (int) round($keywordMatch) }}%</span>
                            @endif
                            <span class="badge badge-sm {{ $statusBadgeClass }}">{{ $evaluation->status->value }}</span>
                        </div>
                    </a>
                @empty
                    <div class="rounded-box border border-base-300 bg-base-100 px-4 py-5 sm:px-6">
                        <p class="text-sm text-base-content/60">No evaluation run yet. Submit the form above to see results here.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="rounded-box border border-error/40 bg-error/5 p-4">
            <div class="flex flex-col items-start gap-4">
                <div class="space-y-1">
                    <h2 class="font-medium text-error">Danger zone</h2>
                    <p class="text-sm text-base-content/70">
                        Deleting this workspace removes it and any practice evaluations stored in it. This cannot be undone.
                    </p>
                </div>

                <button
                    type="button"
                    class="btn btn-error btn-outline btn-sm shrink-0"
                    onclick="delete_workspace_{{ $workspace->id }}.showModal()">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    Delete workspace
                </button>
            </div>

            <dialog id="delete_workspace_{{ $workspace->id }}" class="modal">
                <div class="modal-box w-[92vw] max-w-lg">
                    <button
                        type="button"
                        class="btn btn-sm btn-circle btn-outline absolute right-2 top-2"
                        onclick="delete_workspace_{{ $workspace->id }}.close()"
                        aria-label="Close">
                        ×
                    </button>

                    <header class="space-y-1">
                        <h3 class="text-2xl font-bold text-primary">Delete workspace</h3>
                    </header>
                    <p class="mt-4 text-sm text-base-content/80">
                        Are you sure you want to delete <strong>{{ $workspace->name }}</strong>?
                        All practice evaluations in this workspace will be removed permanently.
                    </p>

                    <form
                        class="modal-action mt-6"
                        method="POST"
                        action="{{ route('dashboard.workspaces.destroy', $workspace) }}">
                        @csrf
                        @method('DELETE')
                        <input name='workspace' />
                        <button
                            type="button"
                            class="btn btn-outline"
                            onclick="delete_workspace_{{ $workspace->id }}.close()">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-error">
                            Delete workspace
                        </button>
                    </form>
                </div>
                <form method="dialog" class="modal-backdrop">
                    <button type="submit">close</button>
                </form>
            </dialog>
        </section>
    </section>

    @error('evaluation')
        <x-toast type="warning">{{ $message }}</x-toast>
    @enderror

    <x-toast type="warning" event="evaluation-blocked" />
    <script>
        document.querySelectorAll('[data-workspace-rename]').forEach((root) => {
            const viewBlock = root.querySelector('[data-rename-view]');
            const editBlock = root.querySelector('[data-rename-edit]');
            const display = root.querySelector('[data-rename-display]');
            const input = root.querySelector('[data-rename-input]');
            const originalName = root.dataset.originalName;

            root.querySelector('[data-rename-start]').addEventListener('click', () => {
                viewBlock.classList.add('hidden');
                editBlock.classList.remove('hidden');
                input.value = display.textContent.trim();
                input.focus();
                input.select();
            });

            root.querySelector('[data-rename-cancel]').addEventListener('click', () => {
                input.value = originalName;
                editBlock.classList.add('hidden');
                viewBlock.classList.remove('hidden');
            });

            input.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    root.querySelector('[data-rename-cancel]').click();
                }
            });
        });
    </script>
</x-dashboard-layout>
