<x-dashboard-layout>
    <x-slot:title>Workspaces</x-slot:title>

    <section class="space-y-4">
        <header class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-2xl font-semibold">Your Workspaces</h2>
                <p class="mt-1 text-sm text-base-content/70">
                    Run resume evaluations and review evaluation history. Each evaluation stores the resume and job context you used.
                </p>
            </div>
            <button type="button" class="btn btn-primary btn-sm" onclick="new_workspace_modal.showModal()">
                New Workspace
            </button>

            <dialog id="new_workspace_modal" class="modal">
                <div class="modal-box max-w-lg border border-base-300 bg-base-100 p-0">
                    <div class="border-b border-base-300 px-6 py-4">
                        <h3 class="text-lg font-bold">New Workspace</h3>
                        <p class="mt-1 text-sm text-base-content/70">
                            Create a place to run evaluations and keep a history of results.
                        </p>
                    </div>

                    <form class="flex flex-col gap-4 px-6 py-5" method="POST" action="#">
                        @csrf
                        <label class="form-control w-full">
                            <span class="label-text mb-1 font-medium">Workspace Name</span>
                            <input
                                type="text"
                                class="input input-bordered w-full"
                                placeholder="e.g. Summer Internship Prep"
                            />
                        </label>

                        <div class="modal-action mt-2">
                            <button type="button" class="btn btn-outline" onclick="new_workspace_modal.close()">Cancel</button>
                            <button type="submit" class="btn btn-primary">Create Workspace</button>
                        </div>
                    </form>
                </div>
                <form method="dialog" class="modal-backdrop">
                    <button type="submit">close</button>
                </form>
            </dialog>
        </header>

        <div class="overflow-x-auto rounded-box border border-base-300 bg-base-100">
            <table class="table">
                <thead>
                    <tr>
                        <th>Workspace</th>
                        <th>Latest evaluation</th>
                        <th>Match</th>
                        <th>Keywords</th>
                        <th>Updated</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($workspaces as $workspace)
                        <tr class="relative hover:bg-base-200">
                            <td>
                                <a
                                    href="{{ route('dashboard.workspaces.show', $workspace['id']) }}"
                                    class="absolute inset-0"
                                    aria-label="Open {{ $workspace['name'] }}"
                                ></a>
                                <div class="font-semibold">{{ $workspace['name'] }}</div>
                                <div class="text-xs text-base-content/60">
                                    {{ $workspace['evaluation_count'] }} {{ Str::plural('evaluation', $workspace['evaluation_count']) }}
                                </div>
                            </td>
                            <td>
                                @if ($workspace['latest_evaluation'])
                                    <div class="text-sm">
                                        @if ($workspace['latest_evaluation']['job_description_label'])
                                            {{ $workspace['latest_evaluation']['job_description_label'] }}
                                        @else
                                            General evaluation
                                        @endif
                                    </div>
                                    @if ($workspace['latest_evaluation']['status'] === 'pending')
                                        <span class="badge badge-warning badge-xs mt-1">Pending</span>
                                    @elseif ($workspace['latest_evaluation']['status'] === 'failed')
                                        <span class="badge badge-error badge-xs mt-1">Failed</span>
                                    @endif
                                @else
                                    <span class="text-sm text-base-content/50">No evaluations yet</span>
                                @endif
                            </td>
                            <td>
                                @if ($workspace['latest_evaluation'] && $workspace['latest_evaluation']['status'] === 'completed' && isset($workspace['latest_evaluation']['match_percent']))
                                    <span class="badge badge-primary badge-outline">
                                        {{ $workspace['latest_evaluation']['match_percent'] }}%
                                    </span>
                                @else
                                    <span class="text-sm text-base-content/50">—</span>
                                @endif
                            </td>
                            <td>
                                @if ($workspace['latest_evaluation'] && $workspace['latest_evaluation']['status'] === 'completed' && isset($workspace['latest_evaluation']['keyword_match']))
                                    <span class="badge badge-secondary badge-outline">
                                        {{ $workspace['latest_evaluation']['keyword_match'] }}%
                                    </span>
                                @else
                                    <span class="text-sm text-base-content/50">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-sm text-base-content/70">{{ $workspace['updated_at'] }}</span>
                            </td>
                            <td class="relative z-10 text-right">
                                <a
                                    href="{{ route('dashboard.workspaces.show', $workspace['id']) }}"
                                    class="btn btn-outline btn-xs"
                                >
                                    Open
                                </a>
                                <button type="button" class="btn btn-outline btn-xs btn-error">
                                    <span class="sr-only">Delete {{ $workspace['name'] }}</span>
                                    Delete
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-base-content/60">
                                No workspaces yet. Create one to start running evaluations.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-dashboard-layout>
