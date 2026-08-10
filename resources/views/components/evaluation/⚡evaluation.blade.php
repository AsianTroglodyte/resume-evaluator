<?php
use App\Enums\EvaluationStatus;
use App\Models\Evaluation;
use Livewire\Component;
new class extends Component
{
    public Evaluation $evaluation;

    public bool $isOpen = false;

    public function mount(Evaluation $evaluation): void
    {
        $this->evaluation = $evaluation;
    }

    public function loadEvaluation(): void
    {
        $this->evaluation->refresh();
    }

    public function toggleExpanded(): void
    {
        $this->isOpen = ! $this->isOpen;
    }
};
?>

@php
    $data = is_array($evaluation->evaluation_data) ? $evaluation->evaluation_data : [];
    $matchedKeywords = $data['matched_keywords'] ?? [];
    $missingKeywords = $data['missing_keywords'] ?? [];
    $aiPhrases = $data['ai_phrases'] ?? [];
    $enrichment = $data['enrichment'] ?? null;
    $warnings = $data['warnings'] ?? [];
    $keywordMatch = $data['keyword_match'] ?? null;
    $hasKeywordFeedback = count(array_filter($matchedKeywords, 'is_string')) > 0
        || count(array_filter($missingKeywords, 'is_string')) > 0;
    $summary = $enrichment['analysis_summary'] ?? null;

    $statusBadgeClass = match ($evaluation->status) {
        EvaluationStatus::Completed => 'badge-success',
        EvaluationStatus::Failed => 'badge-error',
        default => 'badge-ghost',
    };
@endphp

<details
    class="collapse collapse-arrow w-full rounded-box border border-base-300 bg-base-100"
    @if ($evaluation->status === EvaluationStatus::Processing)
        wire:poll.1s="loadEvaluation"
    @endif
    @if ($isOpen)
        open
    @endif
>
    <summary class="collapse-title min-h-0 cursor-pointer py-4"
        wire:click="toggleExpanded"
    >
        <div class="flex flex-wrap items-center justify-between gap-3 pr-6">
            <div class="flex min-w-0 flex-col gap-1">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="font-semibold">
                        {{ $evaluation->created_at->toDayDateTimeString() }}
                    </span>
                    <span class="status-badge-{{ $evaluation->id }} badge badge-sm {{ $statusBadgeClass }}">
                        {{ $evaluation->status->value }}
                    </span>
                </div>
                @if ($summary)
                    <p class="truncate text-sm font-normal text-base-content/60">
                        {{ $summary }}
                    </p>
                @elseif ($evaluation->status === EvaluationStatus::Failed)
                    <p class="truncate text-sm font-normal text-error">
                        {{ $evaluation->failure_reason ?: 'Evaluation failed.' }}
                    </p>
                @endif
            </div>
            @if (isset($keywordMatch) && is_numeric($keywordMatch))
                <span @class([
                    'badge badge-outline',
                    $compact ?? false ? 'badge-secondary' : 'badge-primary',
                ])>
                    @if ($compact ?? false)
                        {{ (int) round($keywordMatch) }}%
                    @else
                        Keyword match {{ (int) round($keywordMatch) }}%
                    @endif
                </span>
            @endif
        </div>
    </summary>

    <div class="collapse-content space-y-4">
        @if ($evaluation->status === EvaluationStatus::Failed)
        <div class="rounded-box border border-base-300 bg-base-200/40 p-4">
            <p class="text-sm text-error">
                {{ $evaluation->failure_reason ?: 'Evaluation failed.' }}
            </p>
        </div>
        @else
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

            @if ($evaluation->status === EvaluationStatus::Processing)
                <p class="text-sm text-base-content/60">Processing.</p>
            @elseif (empty($enrichment) && empty($warnings) && empty($aiPhrases) && ! $hasKeywordFeedback)
                <p class="text-sm text-base-content/60">Evaluation completed but no feedback was returned.</p>
            @endif

            @if (! empty($enrichment))
                <div class="rounded-box border border-primary/20 bg-primary/5 p-4">
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

            <x-evaluation.keyword-analysis
                :matched-keywords="$matchedKeywords"
                :missing-keywords="$missingKeywords"
            />

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
        @endif
    </div>
</details>