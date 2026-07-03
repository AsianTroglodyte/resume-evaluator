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
                    >{{session('job_description')}}</textarea>
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
            @else
                @php
                    $evaluationIsPreview = ! session()->has('evaluation');
                    $evaluation = session('evaluation', $previewEvaluation ?? null);
                    $keywordMatchPercent = $evaluation['keyword_match'] ?? $evaluation['match_percent'] ?? null;
                    $matchedKeywords = $evaluation['matched_keywords'] ?? [];
                    $missingKeywords = $evaluation['missing_keywords'] ?? [];
                    $missingVisible = array_slice($missingKeywords, 0, 8);
                    $missingRest = array_slice($missingKeywords, 8);
                    $aiPhrases = $evaluation['ai_phrases'] ?? [];
                    $enrichment = $evaluation['enrichment'] ?? null;
                    $warnings = $evaluation['warnings'] ?? [];
                @endphp

                @if ($evaluation)
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="font-semibold">
                            {{ $evaluationIsPreview ? 'Example evaluation' : 'Latest evaluation result' }}
                        </h2>
                        @if ($evaluationIsPreview)
                            <span class="badge badge-ghost badge-sm">Sample data</span>
                        @endif
                    </div>
                    @if ($keywordMatchPercent !== null)
                        <span class="badge badge-primary badge-outline">
                            Keyword match {{ is_numeric($keywordMatchPercent) ? (int) round($keywordMatchPercent) : $keywordMatchPercent }}%
                        </span>
                    @endif
                </div>

                @if (! empty($warnings))
                    <div class="mt-4 rounded-box border border-base-300 bg-base-200/40 p-4">
                        <p class="text-sm font-semibold text-base-content">
                            Completeness checks ({{ count($warnings) }})
                        </p>
                        <p class="mt-1 text-xs text-base-content/60">
                            Quick checks for common gaps — no AI, same rules every time.
                        </p>
                        <ul class="mt-3 list-disc space-y-1 pl-5 text-sm text-base-content/90">
                            @foreach ($warnings as $warning)
                                <li>{{ $warning }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (empty($enrichment) && empty($warnings) && empty($aiPhrases) && $keywordMatchPercent === null)
                    <p class="mt-4 text-sm text-base-content/60">Evaluation completed but no feedback was returned.</p>
                @endif

                @if (! empty($enrichment))
                    <div class="mt-6 rounded-box border border-primary/20 bg-primary/5 p-4">
                        <p class="text-sm font-semibold text-primary">Resume analysis</p>
                        @if (! empty($enrichment['analysis_summary']))
                            <p class="mt-2 text-sm leading-relaxed text-base-content/90">{{ $enrichment['analysis_summary'] }}</p>
                        @endif

                        @if (! empty($enrichment['items_to_enrich']))
                            <div class="mt-4 space-y-3">
                                <p class="text-xs font-medium uppercase tracking-wide text-base-content/50">
                                    Items to strengthen ({{ count($enrichment['items_to_enrich']) }})
                                </p>
                                @foreach ($enrichment['items_to_enrich'] as $item)
                                    <div class="rounded-box border border-base-300/60 bg-base-100/80 p-3">
                                        <p class="text-sm font-medium text-base-content">
                                            {{ $item['title'] }}
                                            @if (! empty($item['subtitle']))
                                                <span class="font-normal text-base-content/60">· {{ $item['subtitle'] }}</span>
                                            @endif
                                        </p>
                                        @if (! empty($item['current_description']))
                                            <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-base-content/80">
                                                @foreach ($item['current_description'] as $bullet)
                                                    <li>{{ $bullet }}</li>
                                                @endforeach
                                            </ul>
                                        @endif
                                        @if (! empty($item['weakness_reason']))
                                            <p class="mt-2 text-sm text-warning">{{ $item['weakness_reason'] }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if (! empty($enrichment['questions']))
                            <div class="mt-4">
                                <p class="text-xs font-medium uppercase tracking-wide text-base-content/50">
                                    Questions to consider ({{ count($enrichment['questions']) }})
                                </p>
                                <ul class="mt-2 space-y-3">
                                    @foreach ($enrichment['questions'] as $question)
                                        <li class="text-sm text-base-content/90">
                                            <p>{{ $question['question'] }}</p>
                                            @if (! empty($question['placeholder']))
                                                <p class="mt-1 text-xs text-base-content/60">e.g. {{ $question['placeholder'] }}</p>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                @endif

                @if (! empty($matchedKeywords) || ! empty($missingKeywords))
                    <div class="mt-6 grid gap-4 md:grid-cols-2">
                        @if (! empty($matchedKeywords))
                            <div class="rounded-box border border-success/30 bg-success/5 p-4">
                                <p class="text-sm font-semibold">
                                    Matched keywords ({{ count($matchedKeywords) }})
                                </p>
                                <p class="mt-1 text-xs text-base-content/60">
                                    Terms from the job description found in your resume.
                                </p>
                                <p class="mt-3 text-sm leading-relaxed text-base-content/90">
                                    {{ implode(', ', $matchedKeywords) }}
                                </p>
                            </div>
                        @endif

                        @if (! empty($missingKeywords))
                            <div class="rounded-box border border-warning/30 bg-warning/5 p-4">
                                <p class="text-sm font-semibold">
                                    Missing ({{ count($missingKeywords) }})
                                </p>
                                <p class="mt-1 text-xs text-base-content/60">
                                    Consider adding these if you have relevant experience.
                                </p>
                                <p class="mt-3 text-sm leading-relaxed text-base-content/90">
                                    {{ implode(', ', $missingVisible) }}
                                </p>
                                @if (! empty($missingRest))
                                    <details class="mt-2">
                                        <summary class="cursor-pointer text-sm text-primary hover:underline">
                                            Show {{ count($missingRest) }} more
                                        </summary>
                                        <p class="mt-2 text-sm leading-relaxed text-base-content/90">
                                            {{ implode(', ', $missingRest) }}
                                        </p>
                                    </details>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif

                @if (! empty($aiPhrases))
                    <div class="mt-6 rounded-box border border-base-300 bg-base-200/40 p-4">
                        <p class="text-sm font-semibold text-base-content">
                            AI-sounding phrases ({{ count($aiPhrases) }})
                        </p>
                        <p class="mt-1 text-xs text-base-content/60">
                            These words often read as generic or machine-written. Consider simpler alternatives where noted.
                        </p>
                        <ul class="mt-3 space-y-2 text-sm text-base-content/90">
                            @foreach ($aiPhrases as $hit)
                                <li>
                                    <span class="font-medium">{{ $hit['phrase'] }}</span>
                                    @if (! empty($hit['suggestion']))
                                        <span class="text-base-content/60">→ try</span>
                                        <span class="italic">{{ $hit['suggestion'] }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @else
                <p class="text-sm text-base-content/60">No evaluation run yet. Submit the form above to see results here.</p>
                @endif
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
                                    @if ($scan['status'] === 'completed' && (isset($scan['keyword_match']) || isset($scan['match_percent'])))
                                        @php($scanMatch = $scan['keyword_match'] ?? $scan['match_percent'])
                                        <span class="badge badge-primary badge-outline">
                                            Keyword match {{ is_numeric($scanMatch) ? (int) round($scanMatch) : $scanMatch }}%
                                        </span>
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
                                @if (! empty($scan['enrichment']['analysis_summary']))
                                    <p class="text-sm leading-relaxed text-base-content/80">{{ $scan['enrichment']['analysis_summary'] }}</p>
                                @else
                                    <p class="text-sm text-base-content/60">Evaluation completed.</p>
                                @endif
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
