@php
use App\Enums\EvaluationStatus;

$submission = $assignment->submissionFor(auth()->user())->first();
$evaluation = $submission?->evaluation;
@endphp

<x-dashboard-layout>
    <x-slot:title>{{ $assignment->title }}</x-slot:title>

    <section class="space-y-6">
        <header class="space-y-1">
            <a href="{{ route('dashboard.modules.show', $module) }}" class="link link-primary text-sm">
                &larr; Back to {{ $module->name }}
            </a>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-2xl font-semibold">{{ $assignment->title }}</h2>
                    <p class="mt-1 text-sm text-base-content/70">
                        Due: {{ $assignment->due_date?->format('M j, Y g:i A') ?? 'No due date' }}
                    </p>
                </div>

                {{-- Instructor/admin only --}}
                @can('update', $assignment)
                <a
                    href="{{ route('dashboard.modules.assignments.edit', [$module, $assignment]) }}"
                    class="btn btn-outline btn-sm shrink-0">
                    Edit assignment
                </a>
                @endcan
            </div>
        </header>

        @if (isset($submissionRows))
        <div role="tablist" class="tabs tabs-lift">
            <input type="radio" name="assignment_tabs" role="tab" class="tab" aria-label="Overview" checked="checked" />
            <div role="tabpanel" class="tab-content space-y-6 bg-base-100 border-base-300 p-6">
                @include('dashboard.modules.assignments.partials.overview')
            </div>

            <input type="radio" name="assignment_tabs" role="tab" class="tab" aria-label="Submissions" />
            <div role="tabpanel" class="tab-content bg-base-100 border-base-300 p-6">
                <div class="overflow-x-auto">
                    <table class="table table-zebra">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Submitted on</th>
                                <th>Resubmissions</th>
                                <th>Status</th>
                                <th>Score</th>
                                <th>Evaluated on</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($submissionRows as $row)
                            @php
                                $rowSubmission = $row['submission'];
                                $rowEvaluation = $rowSubmission?->evaluation;
                                $rowKeywordMatch = $rowEvaluation?->evaluation_data['keyword_match'] ?? null;
                                $rowStatusBadgeClass = match ($rowEvaluation?->status) {
                                    EvaluationStatus::Completed => 'badge-success',
                                    EvaluationStatus::Failed => 'badge-error',
                                    EvaluationStatus::Processing => 'badge-ghost',
                                    default => 'badge-outline',
                                };
                            @endphp
                            <tr>
                                <td>
                                    <div class="font-medium">{{ $row['user']->first_name }} {{ $row['user']->last_name }}</div>
                                    <div class="text-xs text-base-content/60">{{ $row['user']->email }}</div>
                                </td>
                                <td>{{ $rowSubmission?->created_at->format('M j, Y g:i A') ?? '—' }}</td>
                                <td>{{ $rowSubmission?->resubmission_count ?? '—' }}</td>
                                <td>
                                    <span class="badge badge-sm {{ $rowStatusBadgeClass }}">
                                        {{ $rowEvaluation?->status->value ?? 'incomplete' }}
                                    </span>
                                </td>
                                <td>{{ is_numeric($rowKeywordMatch) ? ((int) round($rowKeywordMatch)).'%' : '—' }}</td>
                                <td>
                                    {{ $rowEvaluation?->status === EvaluationStatus::Completed
                                        ? $rowEvaluation->updated_at->format('M j, Y g:i A')
                                        : '—' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-base-content/60">No students to show.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @else
        <div class="space-y-6">
            @include('dashboard.modules.assignments.partials.overview')
        </div>
        @endif
    </section>
</x-dashboard-layout>
