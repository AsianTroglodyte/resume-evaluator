<x-dashboard-layout>
    <x-slot:title>{{ $workspace['name'] }}</x-slot:title>

    <section class="space-y-6">
        <header>
            <a
                href="{{ route('dashboard.workspaces.index') }}"
                class="text-sm text-base-content/60 hover:text-base-content"
            >
                ← Back to workspaces
            </a>
            <h1 class="mt-2 text-2xl font-semibold">{{ $workspace['name'] }}</h1>
            <p class="mt-1 text-sm text-base-content/70">
                Paste resume text, optionally add a job description, and run an evaluation. Each run is saved in your scan history below.
            </p>
        </header>

        <section class="rounded-box border border-base-300 bg-base-100">
            <div class="border-b border-base-300 px-4 py-3 sm:px-6">
                <h2 class="font-semibold">New evaluation</h2>
                <p class="text-sm text-base-content/60">Resume text is required. Job description is optional.</p>
            </div>
            <form class="flex flex-col gap-4 px-4 py-5 sm:px-6" 
                method="POST" 
                action="{{ route('dashboard.workspaces.scans.store', $workspace['id']) }}">
                @csrf
                <label class="form-control w-full">
                    <span class="label-text mb-1 font-medium">Resume text</span>
                    <textarea
                        name="resume_text"
                        class="textarea textarea-bordered min-h-40 w-full font-mono text-sm"
                        placeholder="Paste your resume content here..."
                        required
                    ></textarea>
                </label>
                <label class="form-control w-full">
                    <span class="label-text mb-1 font-medium">Job description <span class="font-normal text-base-content/50">(optional)</span></span>
                    <textarea
                        name="job_description"
                        class="textarea textarea-bordered min-h-28 max-h-60 w-full text-sm"
                        placeholder="Paste a role description for keyword matching and targeted feedback."
                    ></textarea>
                    <span class="label-text-alt text-sm text-base-content/60">
                        Leave blank for a general quality evaluation without keyword match.
                    </span>
                </label>
                <div class="flex justify-end">
                    <button type="submit" class="btn btn-primary btn-sm">Run evaluation</button>
                </div>
            </form>
        </section>

        <section class="rounded-box border border-base-300 bg-base-100 px-4 py-5 sm:px-6">
            @if (session('evaluation_error'))
                <p class="text-sm text-error">{{ session('evaluation_error') }}</p>
            @elseif (session('evaluation'))
                @php($evaluation = session('evaluation'))

                <div class="flex flex-wrap items-start justify-between gap-3">
                    <h2 class="font-semibold">Latest evaluation result</h2>
                    <div class="flex flex-wrap gap-2">
                        @if (isset($evaluation['match_percent']))
                            <span class="badge badge-primary badge-outline">Match {{ $evaluation['match_percent'] }}%</span>
                        @endif
                        @if (isset($evaluation['keyword_match']))
                            <span class="badge badge-secondary badge-outline">Keywords {{ $evaluation['keyword_match'] }}%</span>
                        @endif
                    </div>
                </div>

                @if (! empty($evaluation['quality_eval']))
                    <p class="mt-4 text-sm leading-relaxed text-base-content/80 whitespace-pre-wrap">{{ $evaluation['quality_eval'] }}</p>
                @else
                    <p class="mt-4 text-sm text-base-content/60">Evaluation completed but no feedback was returned.</p>
                @endif

                {{-- @if (! empty($evaluation['jd_keywords']))
                    <p class="mt-4 text-sm leading-relaxed text-base-content/80 whitespace-pre-wrap">
                        {{
                        json_encode($evaluation['jd_keywords'])}}
                    </p>
                @else
                    <p class="mt-4 text-sm leading-relaxed text-base-content/80 whitespace-pre-wrap">no jd provided</p>
                @endif --}}
            @else
                <p class="text-sm text-base-content/60">No evaluation run yet. Submit the form above to see results here.</p>
            @endif
        </section>

        <section class="space-y-3">
            <div class="flex items-baseline justify-between gap-2">
                <h2 class="text-lg font-semibold">Scan history</h2>
                <span class="text-sm text-base-content/60">{{ count($workspace['scans']) }} {{ Str::plural('scan', count($workspace['scans'])) }}</span>
            </div>

            <ul class="space-y-3">
                @forelse ($workspace['scans'] as $scan)
                    <li class="rounded-box border border-base-300 bg-base-100">
                        <div class="border-b border-base-300 px-4 py-3 sm:px-5">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        @if ($scan['status'] === 'pending')
                                            <span class="badge badge-warning badge-sm">Pending</span>
                                        @elseif ($scan['status'] === 'failed')
                                            <span class="badge badge-error badge-sm">Failed</span>
                                        @endif
                                        <span class="font-medium">
                                            @if ($scan['job_description_label'])
                                                {{ $scan['job_description_label'] }}
                                            @else
                                                General evaluation
                                            @endif
                                        </span>
                                    </div>
                                    <p class="mt-1 text-xs text-base-content/60">{{ $scan['created_at'] }}</p>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    @if ($scan['status'] === 'completed' && isset($scan['match_percent']))
                                        <span class="badge badge-primary badge-outline">Match {{ $scan['match_percent'] }}%</span>
                                    @endif
                                    @if ($scan['status'] === 'completed' && isset($scan['keyword_match']))
                                        <span class="badge badge-secondary badge-outline">Keywords {{ $scan['keyword_match'] }}%</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="px-4 py-4 sm:px-5">
                            @if ($scan['status'] === 'pending')
                                <p class="text-sm text-base-content/70">Evaluation is running. Refresh to see results.</p>
                            @elseif ($scan['status'] === 'failed')
                                <p class="text-sm text-error">{{ $scan['error_message'] ?? 'Evaluation could not be completed.' }}</p>
                            @else
                                <p class="text-sm leading-relaxed text-base-content/80">{{ $scan['quality_eval'] }}</p>
                            @endif

                            @if ($scan['status'] === 'completed')
                                <details class="mt-4 group">
                                    <summary class="cursor-pointer text-sm text-primary hover:underline">
                                        View inputs used for this scan
                                    </summary>
                                    <div class="mt-3 space-y-3 rounded-box bg-base-200/50 p-3 text-sm">
                                        <div>
                                            <p class="mb-1 text-xs font-medium uppercase tracking-wide text-base-content/50">Resume text</p>
                                            <p class="whitespace-pre-wrap font-mono text-xs text-base-content/80">{{ $scan['resume_text_preview'] }}</p>
                                        </div>
                                        @if ($scan['job_description_preview'])
                                            <div>
                                                <p class="mb-1 text-xs font-medium uppercase tracking-wide text-base-content/50">Job description</p>
                                                <p class="whitespace-pre-wrap text-xs text-base-content/80">{{ $scan['job_description_preview'] }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </details>
                            @endif
                        </div>
                    </li>
                @empty
                    <li class="rounded-box border border-dashed border-base-300 px-4 py-12 text-center text-sm text-base-content/60">
                        No scans yet. Run your first evaluation above.
                    </li>
                @endforelse
            </ul>
        </section>
    </section>
</x-dashboard-layout>
