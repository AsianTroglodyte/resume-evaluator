<x-dashboard-layout>
    <x-slot:title>{{ $workspace->name }}</x-slot:title>

    <section class="space-y-6">
        <header>
            <a
                href="{{ route('dashboard.workspaces.index') }}"
                class="text-sm text-base-content/60 hover:text-base-content"
            >
                ← Back to workspaces
            </a>
            <h1 class="mt-2 text-2xl font-semibold">{{ $workspace->name }}</h1>
            <p class="mt-1 text-sm text-base-content/70">
                Paste resume text, optionally add a job description, and run an evaluation. Each run is saved in your evaluation history below.
            </p>
        </header>

        <section class="rounded-box border border-base-300 bg-base-100">
            <div class="border-b border-base-300 px-4 py-3 sm:px-6">
                <h2 class="font-semibold">New evaluation</h2>
                <p class="text-sm text-base-content/60">Resume text is required. Job description is optional.</p>
            </div>
            <form class="flex flex-col gap-4 px-4 py-5 sm:px-6" 
                method="POST" 
                enctype="multipart/form-data"
                {{-- accept=".pdf,.docx,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document" --}}
                action="{{ route('dashboard.workspaces.evaluations.store', $workspace) }}">
                @csrf
                <label class="form-control w-full">
                    <div class="label-text mb-1 font-medium">Resume file</div>
                    <input 
                        type="file" 
                        name="resume_file" 
                        class="file-input">
                    </input>
                    @error('resume_file')
                    <span class="label-text-alt mt-1 text-error">{{ $message }}</span>
                    @enderror
                    {{-- <textarea
                        name="resume_text"
                        class="textarea textarea-bordered min-h-40 w-full font-mono text-sm"
                        placeholder="Paste your resume content here..."
                        required
                    ></textarea> --}}
                </label>
                <label class="form-control w-full">
                    <span class="label-text mb-1 font-medium">Job description <span class="font-normal text-base-content/50">(optional)</span></span>
                    <textarea
                        name="job_description"
                        class="textarea textarea-bordered min-h-28 max-h-60 w-full text-sm"
                        placeholder="Paste a role description for targeted feedback and keyword analysis."
                    >{{session('job_description')}}</textarea>
                    <span class="label-text-alt text-sm text-base-content/60">
                        Leave blank for a general quality evaluation without keyword analysis.
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
                    $evaluation = session('evaluation') ?? ($previewEvaluation ?? null);
                    $matchedKeywords = is_array($evaluation) ? ($evaluation['matched_keywords'] ?? []) : [];
                    $missingKeywords = is_array($evaluation) ? ($evaluation['missing_keywords'] ?? []) : [];
                    $aiPhrases = is_array($evaluation) ? ($evaluation['ai_phrases'] ?? []) : [];
                    $enrichment = is_array($evaluation) ? ($evaluation['enrichment'] ?? null) : null;
                    $warnings = is_array($evaluation) ? ($evaluation['warnings'] ?? []) : [];
                    $keywordMatch = is_array($evaluation) ? ($evaluation['keyword_match'] ?? null) : null;
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
                    @include('dashboard.workspaces._keyword-match-badge', ['keywordMatch' => $keywordMatch])
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

                @php
                    $hasKeywordFeedback = count(array_filter($matchedKeywords, 'is_string')) > 0
                        || count(array_filter($missingKeywords, 'is_string')) > 0;
                @endphp

                @if (empty($enrichment) && empty($warnings) && empty($aiPhrases) && ! $hasKeywordFeedback)
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

                @include('dashboard.workspaces._keyword-analysis', [
                    'matchedKeywords' => $matchedKeywords,
                    'missingKeywords' => $missingKeywords,
                ])

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
                <h2 class="text-lg font-semibold">Evaluation history</h2>
                <span class="text-sm text-base-content/60">{{ count($evaluations) }} {{ Str::plural('evaluation', count($evaluations)) }}</span>
            </div>

            <ul class="space-y-3">
                @forelse ($evaluations as $evaluationRun)
                    <li class="rounded-box border border-base-300 bg-base-100">
                        <div class="border-b border-base-300 px-4 py-3 sm:px-5">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        @if ($evaluationRun['status'] === 'pending')
                                            <span class="badge badge-warning badge-sm">Pending</span>
                                        @elseif ($evaluationRun['status'] === 'failed')
                                            <span class="badge badge-error badge-sm">Failed</span>
                                        @endif
                                        <span class="font-medium">
                                            @if ($evaluationRun['job_description_label'])
                                                {{ $evaluationRun['job_description_label'] }}
                                            @else
                                                General evaluation
                                            @endif
                                        </span>
                                    </div>
                                    <p class="mt-1 text-xs text-base-content/60">{{ $evaluationRun['created_at'] }}</p>
                                </div>
                                @if ($evaluationRun['status'] === 'completed')
                                    @include('dashboard.workspaces._keyword-match-badge', [
                                        'keywordMatch' => $evaluationRun['keyword_match'] ?? null,
                                    ])
                                @endif
                            </div>
                        </div>

                        <div class="px-4 py-4 sm:px-5">
                            @if ($evaluationRun['status'] === 'pending')
                                <p class="text-sm text-base-content/70">Evaluation is running. Refresh to see results.</p>
                            @elseif ($evaluationRun['status'] === 'failed')
                                <p class="text-sm text-error">{{ $evaluationRun['error_message'] ?? 'Evaluation could not be completed.' }}</p>
                            @else
                                @if (! empty($evaluationRun['enrichment']['analysis_summary']))
                                    <p class="text-sm leading-relaxed text-base-content/80">{{ $evaluationRun['enrichment']['analysis_summary'] }}</p>
                                @else
                                    <p class="text-sm text-base-content/60">Evaluation completed.</p>
                                @endif

                                @include('dashboard.workspaces._keyword-analysis', [
                                    'matchedKeywords' => $evaluationRun['matched_keywords'] ?? [],
                                    'missingKeywords' => $evaluationRun['missing_keywords'] ?? [],
                                    'class' => 'mt-4',
                                ])
                            @endif

                            @if ($evaluationRun['status'] === 'completed')
                                <details class="mt-4 group">
                                    <summary class="cursor-pointer text-sm text-primary hover:underline">
                                        View inputs used for this evaluation
                                    </summary>
                                    <div class="mt-3 space-y-3 rounded-box bg-base-200/50 p-3 text-sm">
                                        <div>
                                            <p class="mb-1 text-xs font-medium uppercase tracking-wide text-base-content/50">Resume text</p>
                                            <p class="whitespace-pre-wrap font-mono text-xs text-base-content/80">{{ $evaluationRun['resume_text_preview'] }}</p>
                                        </div>
                                        @if ($evaluationRun['job_description_preview'])
                                            <div>
                                                <p class="mb-1 text-xs font-medium uppercase tracking-wide text-base-content/50">Job description</p>
                                                <p class="whitespace-pre-wrap text-xs text-base-content/80">{{ $evaluationRun['job_description_preview'] }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </details>
                            @endif
                        </div>
                    </li>
                @empty
                    <li class="rounded-box border border-dashed border-base-300 px-4 py-12 text-center text-sm text-base-content/60">
                        No evaluations yet. Run your first evaluation above.
                    </li>
                @endforelse
            </ul>
        </section>
    </section>
</x-dashboard-layout>
