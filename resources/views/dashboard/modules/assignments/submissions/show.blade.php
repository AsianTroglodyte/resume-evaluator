@php
    use App\Enums\EvaluationStatus;

    $student = $submission->user;
    $studentName = $student->first_name.' '.$student->last_name;
    $assignment = $submission->assignment;
    $evaluation = $submission->evaluation;

    $data = $evaluation?->evaluation_data;
    $data = is_array($data) ? $data : [];

    $matchedKeywords = $data['matched_keywords'] ?? [];
    $missingKeywords = $data['missing_keywords'] ?? [];
    $aiPhrases = $data['ai_phrases'] ?? [];
    $enrichment = is_array($data['enrichment'] ?? null) ? $data['enrichment'] : null;
    $warnings = $data['warnings'] ?? [];
    $keywordMatch = $data['keyword_match'] ?? null;
    $summary = data_get($enrichment, 'analysis_summary');
    $itemsToEnrich = data_get($enrichment, 'items_to_enrich', []);
    $questions = data_get($enrichment, 'questions', []);

    $statusBadgeClass = match ($evaluation?->status) {
        EvaluationStatus::Completed => 'badge-success',
        EvaluationStatus::Failed => 'badge-error',
        default => 'badge-ghost',
    };
    $evaluationStatus = $evaluation?->status?->value ?? 'incomplete';
@endphp

<x-dashboard-layout>
    <x-slot:title>Submission · {{ $studentName}}</x-slot:title>

    <section class="space-y-6">
        {{-- Header --}}
        <header class="space-y-1">
            <a href="{{ route('dashboard.modules.assignments.show', 
                [$assignment->module, $assignment]) }}" class="link link-primary text-sm">
                &larr; Back to {{ $assignment->title }}
            </a>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-2xl font-semibold">{{ $studentName }}</h2>
                    <p class="mt-1 text-sm text-base-content/70">
                        {{ $student->email }} · {{ $assignment->module->name }}
                    </p>
                </div>
                <span class="badge badge-outline {{ $statusBadgeClass }} shrink-0">
                    {{ ucfirst($evaluationStatus) }}
                </span>
            </div>
        </header>

        {{-- Submission metadata --}}
        <article class="rounded-box border border-base-300 bg-base-100 p-6">
            <header class="mb-4 space-y-1 border-b border-base-300 pb-4">
                <h3 class="text-lg font-semibold">Submission</h3>
                <p class="text-sm text-base-content/70">Turn-in record and policy snapshot.</p>
            </header>

            <dl class="grid gap-4 text-sm sm:grid-cols-3">
                <div>
                    <dt class="text-base-content/60">Submitted on</dt>
                    <dd class="mt-1 font-medium">{{ $submission->created_at->format('M j, Y g:i A') }}</dd>
                </div>
                <div>
                    <dt class="text-base-content/60">Assignment version</dt>
                    <dd class="mt-1 font-medium">{{ $submission->assignment_version }}</dd>
                </div>
                <div>
                    <dt class="text-base-content/60">Due date (snapshot)</dt>
                    <dd class="mt-1 font-medium">
                        {{ $submission->due_date_snapshot?->format('M j, Y g:i A') ?? '—' }}
                    </dd>
                </div>
            </dl>
        </article>

        {{-- Resume + job context --}}
        <div class="grid gap-6 lg:grid-cols-2">
            <article class="rounded-box border border-base-300 bg-base-100 p-6">
                <header class="mb-4 space-y-1 border-b border-base-300 pb-4">
                    <h3 class="text-lg font-semibold">Resume</h3>
                    <p class="text-sm text-base-content/70">Extracted resume text.</p>
                </header>
                <pre class="max-h-80 overflow-auto whitespace-pre-wrap text-sm leading-relaxed text-base-content/90">
                    {{ $evaluation?->resume_text }}
                </pre>
            </article>

            <article class="rounded-box border border-base-300 bg-base-100 p-6">
                <header class="mb-4 space-y-1 border-b border-base-300 pb-4">
                    <h3 class="text-lg font-semibold">Job description</h3>
                    <p class="text-sm text-base-content/70">Job context used for this evaluation.</p>
                </header>
                <pre class="max-h-80 overflow-auto whitespace-pre-wrap text-sm leading-relaxed text-base-content/90">
                    {{ $evaluation?->job_description_text }}
                </pre>
            </article>
        </div>

        {{-- Evaluation results --}}
        <article class="rounded-box border border-base-300 bg-base-100 p-6">
            <header class="mb-4 flex flex-wrap items-center justify-between gap-3 border-b border-base-300 pb-4">
                <div class="space-y-1">
                    <h3 class="text-lg font-semibold">Evaluation</h3>
                    <p class="text-sm text-base-content/70">Automated feedback for this submission.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="badge badge-sm {{ $statusBadgeClass }}">{{ $evaluationStatus}}</span>
                    @if (is_numeric($keywordMatch))
                        <span class="badge badge-outline badge-primary">Keyword match {{ $keywordMatch}}%</span>
                    @endif
                </div>
            </header>

            <div class="space-y-4">
                {{-- Completeness checks --}}
                @if (! empty($warnings))
                    <div class="rounded-box border border-base-300 bg-base-200/40 p-4">
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

                {{-- Resume analysis --}}
                @if ($summary || ! empty($itemsToEnrich) || ! empty($questions))
                <div class="rounded-box border border-primary/20 bg-primary/5 p-4">
                    <p class="text-sm font-semibold text-primary">Resume analysis</p>
                    @if ($summary)
                        <p class="mt-2 text-sm leading-relaxed text-base-content/90">{{ $summary }}</p>
                    @endif

                    @if (! empty($itemsToEnrich))
                        <div class="mt-4 space-y-3">
                            <p class="text-xs font-medium uppercase tracking-wide text-base-content/50">
                                Items to strengthen ({{ count($itemsToEnrich) }})
                            </p>
                            @foreach ($itemsToEnrich as $item)
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

                    @if (! empty($questions))
                        <div class="mt-4">
                            <p class="text-xs font-medium uppercase tracking-wide text-base-content/50">
                                Questions to consider ({{ count($questions) }})
                            </p>
                            <ul class="mt-2 space-y-3">
                                @foreach ($questions as $question)
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

                {{-- Keyword analysis --}}
                <x-evaluation.keyword-analysis
                    :matched-keywords="$matchedKeywords"
                    :missing-keywords="$missingKeywords"
                />

                {{-- AI-sounding phrases --}}
                @if (! empty($aiPhrases))
                    <div class="rounded-box border border-base-300 bg-base-200/40 p-4">
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
            </div>
        </article>
    </section>
</x-dashboard-layout>
