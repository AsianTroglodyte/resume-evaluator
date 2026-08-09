@php
    $matched = array_values(array_filter($matchedKeywords ?? [], fn ($keyword) => is_string($keyword) && $keyword !== ''));
    $missing = array_values(array_filter($missingKeywords ?? [], fn ($keyword) => is_string($keyword) && $keyword !== ''));
    $missingVisible = array_slice($missing, 0, 8);
    $missingRest = array_slice($missing, 8);
@endphp

@if (! empty($matched) || ! empty($missing))
    <div @class(['grid gap-4 md:grid-cols-2', $class ?? 'mt-6'])>
        @if (! empty($matched))
            <div class="rounded-box border border-success/30 bg-success/5 p-4">
                <p class="text-sm font-semibold">
                    Matched keywords ({{ count($matched) }})
                </p>
                <p class="mt-1 text-xs text-base-content/60">
                    Terms from the job description found in your resume.
                </p>
                <p class="mt-3 text-sm leading-relaxed text-base-content/90">
                    {{ implode(', ', $matched) }}
                </p>
            </div>
        @endif

        @if (! empty($missing))
            <div class="rounded-box border border-warning/30 bg-warning/5 p-4">
                <p class="text-sm font-semibold">
                    Missing ({{ count($missing) }})
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
