@php
    use App\Enums\EvaluationStatus;

    $student = $submission->user;
    $studentName = $student->first_name.' '.$student->last_name;
    $assignment = $submission->assignment;
    $evaluation = $submission->evaluation;

    $statusBadgeClass = match ($evaluation?->status) {
        EvaluationStatus::Completed => 'badge-success',
        EvaluationStatus::Failed => 'badge-error',
        default => 'badge-ghost',
    };
    $evaluationStatus = $evaluation?->status?->value ?? 'incomplete';
    $isOwnSubmission = $submission->user_id === auth()->id();
@endphp

<x-dashboard-layout>
    <x-slot:title>Submission · {{ $isOwnSubmission ? $assignment->title : $studentName }}</x-slot:title>

    <section class="space-y-6">
        {{-- Header --}}
        <header class="space-y-1">
            <a href="{{ route('dashboard.modules.assignments.show', 
                [$assignment->module, $assignment]) }}" class="link link-primary text-sm">
                &larr; Back to {{ $assignment->title }}
            </a>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    @if ($isOwnSubmission)
                    <h2 class="text-2xl font-semibold">Your submission</h2>
                    <p class="mt-1 text-sm text-base-content/70">
                        {{ $assignment->title }} · {{ $assignment->module->name }}
                    </p>
                    @else
                    <h2 class="text-2xl font-semibold">{{ $studentName }}</h2>
                    <p class="mt-1 text-sm text-base-content/70">
                        {{ $student->email }} · {{ $assignment->module->name }}
                    </p>
                    @endif
                </div>
                <span class="badge badge-lg {{ $statusBadgeClass }} shrink-0">
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

        <x-evaluation.details :$evaluation :can-retry="$isOwnSubmission" />
    </section>
</x-dashboard-layout>
