<?php

use App\Enums\EvaluationStatus;
use App\Models\Workspace;
use Livewire\Component;

new class extends Component
{
    public Workspace $workspace;

    /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Evaluation> */
    public $evaluations;

    public function mount(Workspace $workspace): void
    {
        $this->workspace = $workspace;
        $this->loadEvaluations();
    }

    public function loadEvaluations(): void
    {
        $this->evaluations = $this->workspace
            ->evaluations()
            ->latest()
            ->limit(5)
            ->get();
    }

    public function refreshEvaluations(): void
    {
        $this->loadEvaluations();
    }

    public function hasProcessing(): bool
    {
        return $this->evaluations->contains(
            fn ($evaluation) => $evaluation->status === EvaluationStatus::Processing
        );
    }
};
?>

<div
    class="space-y-4"
    @if ($this->hasProcessing()) wire:poll.1s="refreshEvaluations" @endif
>
    @include('dashboard.workspaces._evaluations', ['evaluations' => $evaluations])
</div>
