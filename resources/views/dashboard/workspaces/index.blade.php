<x-dashboard-layout>
    <x-slot:title>Workspaces</x-slot:title>

    <section class="space-y-4">
        <header class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-2xl font-semibold">Your Workspaces</h2>
                <p class="mt-1 text-sm text-base-content/70">
                    Draft resume versions, run scans, and review feedback. Submit snapshots to assignments from module pages when you are ready.
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
                            Create a drafting area for resume versions and scans. Job context is added when you run a scan.
                        </p>
                    </div>

                    <form class="flex flex-col gap-4 px-6 py-5" method="POST" action="#" enctype="multipart/form-data">
                        @csrf
                        <label class="form-control w-full">
                            <span class="label-text mb-1 font-medium">Workspace Name</span>
                            <input
                                type="text"
                                class="input input-bordered w-full"
                                placeholder="e.g. Summer Internship Prep"
                            />
                        </label>

                        <label class="form-control w-full">
                            <span class="label-text mb-1 font-medium">Initial Resume (optional)</span>
                            <input type="file" class="file-input file-input-bordered w-full" accept=".pdf,.doc,.docx" />
                            <span class="label-text-alt text-sm text-base-content/60">
                                Accepted: PDF, DOC, DOCX. You can upload more versions later.
                            </span>
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
                        <th>Latest Version</th>
                        <th>Latest Scan</th>
                        <th>ATS Score</th>
                        <th>Keyword Match</th>
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
                                    {{ $workspace['version_count'] }} {{ Str::plural('version', $workspace['version_count']) }},
                                    {{ $workspace['scan_count'] }} {{ Str::plural('scan', $workspace['scan_count']) }}
                                </div>
                            </td>
                            <td>
                                @if ($workspace['latest_version'])
                                    <div class="text-sm">{{ $workspace['latest_version']['original_name'] }}</div>
                                @else
                                    <span class="text-sm text-base-content/50">No uploads yet</span>
                                @endif
                            </td>
                            <td>
                                @if ($workspace['latest_scan'])
                                    <div class="text-sm">{{ $workspace['latest_scan']['label'] }}</div>
                                @else
                                    <span class="text-sm text-base-content/50">No scans yet</span>
                                @endif
                            </td>
                            <td>
                                @if ($workspace['latest_scan'] && isset($workspace['latest_scan']['ats_score']))
                                    <span class="badge badge-primary badge-outline">
                                        {{ $workspace['latest_scan']['ats_score'] }}%
                                    </span>
                                @else
                                    <span class="text-sm text-base-content/50">—</span>
                                @endif
                            </td>
                            <td>
                                @if ($workspace['latest_scan'] && isset($workspace['latest_scan']['keyword_match']))
                                    <span class="badge badge-secondary badge-outline">
                                        {{ $workspace['latest_scan']['keyword_match'] }}%
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
                            <td colspan="7" class="py-10 text-center text-base-content/60">
                                No workspaces yet. Create one to start uploading resume versions and running scans.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-dashboard-layout>
