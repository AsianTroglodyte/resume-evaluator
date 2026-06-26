<x-dashboard-layout>
    <x-slot:title>{{ $workspace['name'] }}</x-slot:title>

    <section class="space-y-6">
        <header class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <a
                    href="{{ route('dashboard.workspaces.index') }}"
                    class="text-sm text-base-content/60 hover:text-base-content"
                >
                    ← Back to workspaces
                </a>
                <h1 class="mt-2 text-2xl font-semibold">{{ $workspace['name'] }}</h1>
                <p class="mt-1 text-sm text-base-content/70">
                    Upload resume versions, run scans with optional job context, and iterate before submitting a snapshot to an assignment.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" class="btn btn-outline btn-sm" onclick="upload_version_modal.showModal()">
                    Upload Version
                </button>
                <button type="button" class="btn btn-primary btn-sm" onclick="new_scan_modal.showModal()">
                    Run Scan
                </button>
            </div>
        </header>

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-box border border-base-300 bg-base-100">
                <div class="border-b border-base-300 px-4 py-3">
                    <h2 class="font-semibold">Resume Versions</h2>
                    <p class="text-sm text-base-content/60">Each upload is a new revision in this workspace.</p>
                </div>
                <ul class="divide-y divide-base-300">
                    @forelse ($workspace['versions'] as $version)
                        <li class="flex items-start justify-between gap-3 px-4 py-3">
                            <div>
                                <div class="font-medium">{{ $version['original_name'] }}</div>
                                <div class="text-xs text-base-content/60">Uploaded {{ $version['uploaded_at'] }}</div>
                            </div>
                            @if ($version['is_latest'])
                                <span class="badge badge-ghost badge-sm">Latest</span>
                            @endif
                        </li>
                    @empty
                        <li class="px-4 py-8 text-center text-sm text-base-content/60">
                            No resume versions yet. Upload a file to get started.
                        </li>
                    @endforelse
                </ul>
            </section>

            <section class="rounded-box border border-base-300 bg-base-100">
                <div class="border-b border-base-300 px-4 py-3">
                    <h2 class="font-semibold">Resume Scans</h2>
                    <p class="text-sm text-base-content/60">Automated evaluations against general quality or a specific job description.</p>
                </div>
                <ul class="divide-y divide-base-300">
                    @forelse ($workspace['scans'] as $scan)
                        <li class="px-4 py-3">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <div>
                                    <div class="font-medium">{{ $scan['job_context_label'] }}</div>
                                    <div class="mt-1 text-xs text-base-content/60">
                                        {{ $scan['version_name'] }} · {{ $scan['created_at'] }}
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <span class="badge badge-primary badge-outline">ATS {{ $scan['ats_score'] }}%</span>
                                    @if (isset($scan['keyword_match']))
                                        <span class="badge badge-secondary badge-outline">Keywords {{ $scan['keyword_match'] }}%</span>
                                    @endif
                                </div>
                            </div>
                            @if ($scan['feedback_preview'])
                                <p class="mt-2 text-sm text-base-content/70">{{ $scan['feedback_preview'] }}</p>
                            @endif
                        </li>
                    @empty
                        <li class="px-4 py-8 text-center text-sm text-base-content/60">
                            No scans yet. Run a scan to see scores and feedback.
                        </li>
                    @endforelse
                </ul>
            </section>
        </div>

        <div class="rounded-box border border-dashed border-base-300 bg-base-200/40 px-4 py-3 text-sm text-base-content/70">
            Assignment submission is separate: choose a snapshot from this workspace when turning in work on a module assignment page.
        </div>

        <dialog id="upload_version_modal" class="modal">
            <div class="modal-box max-w-lg border border-base-300 bg-base-100 p-0">
                <div class="border-b border-base-300 px-6 py-4">
                    <h3 class="text-lg font-bold">Upload Resume Version</h3>
                    <p class="mt-1 text-sm text-base-content/70">Adds a new revision without replacing previous uploads.</p>
                </div>
                <form class="flex flex-col gap-4 px-6 py-5" method="POST" action="#" enctype="multipart/form-data">
                    @csrf
                    <label class="form-control w-full">
                        <span class="label-text mb-1 font-medium">Resume File</span>
                        <input type="file" class="file-input file-input-bordered w-full" accept=".pdf,.doc,.docx" />
                    </label>
                    <div class="modal-action mt-2">
                        <button type="button" class="btn btn-outline" onclick="upload_version_modal.close()">Cancel</button>
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </div>
                </form>
            </div>
            <form method="dialog" class="modal-backdrop">
                <button type="submit">close</button>
            </form>
        </dialog>

        <dialog id="new_scan_modal" class="modal">
            <div class="modal-box max-w-2xl border border-base-300 bg-base-100 p-0">
                <div class="border-b border-base-300 px-6 py-4">
                    <h3 class="text-lg font-bold">Run Resume Scan</h3>
                    <p class="mt-1 text-sm text-base-content/70">
                        Evaluate a resume version. Add a job description for keyword matching and role-specific feedback.
                    </p>
                </div>
                <form class="flex flex-col gap-4 px-6 py-5" method="POST" action="#">
                    @csrf
                    <label class="form-control w-full">
                        <span class="label-text mb-1 font-medium">Resume Version</span>
                        <select class="select select-bordered w-full">
                            @foreach ($workspace['versions'] as $version)
                                <option value="{{ $version['id'] }}" @selected($version['is_latest'])>
                                    {{ $version['original_name'] }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                    <label class="form-control w-full">
                        <span class="label-text mb-1 font-medium">Job Description (optional)</span>
                        <textarea
                            class="textarea textarea-bordered min-h-28 max-h-60 w-full"
                            placeholder="Paste a role description to enable keyword matching and targeted feedback."
                        ></textarea>
                        <span class="label-text-alt text-sm text-base-content/60">
                            Leave blank for a general ATS and quality scan only.
                        </span>
                    </label>
                    <div class="modal-action mt-2">
                        <button type="button" class="btn btn-outline" onclick="new_scan_modal.close()">Cancel</button>
                        <button type="submit" class="btn btn-primary">Run Scan</button>
                    </div>
                </form>
            </div>
            <form method="dialog" class="modal-backdrop">
                <button type="submit">close</button>
            </form>
        </dialog>
    </section>
</x-dashboard-layout>
