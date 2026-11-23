@php
    use App\Enums\EvaluationStatus;

    $statusBadgeClass = match ($evaluation->status) {
        EvaluationStatus::Completed => 'badge-success',
        EvaluationStatus::Failed => 'badge-error',
        default => 'badge-ghost',
    };
@endphp

<x-dashboard-layout>
    <x-slot:title>Evaluation · {{ $workspace->name }}</x-slot:title>

    <section class="space-y-6">
        <header class="space-y-1">
            <a href="{{ route('dashboard.workspaces.show', $workspace) }}" class="link link-primary text-sm">
                &larr; Back to {{ $workspace->name }}
            </a>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-2xl font-semibold">Practice evaluation</h2>
                    <p class="mt-1 text-sm text-base-content/70">
                        {{ $evaluation->created_at->toDayDateTimeString() }} · {{ $workspace->name }}
                    </p>
                </div>
                <span class="badge badge-lg {{ $statusBadgeClass }} shrink-0">
                    {{ ucfirst($evaluation->status->value) }}
                </span>
            </div>
        </header>

        <x-evaluation.details :$evaluation :can-retry="true" />
    </section>
</x-dashboard-layout>
