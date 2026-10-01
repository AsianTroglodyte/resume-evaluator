<?php
use App\Actions\RetryEvaluation;
use App\Enums\EvaluationStatus;
use App\Models\Evaluation;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component
{
    #[Locked]
    public int $evaluationId;

    #[Locked]
    public string $reloadUrl;

    public ?string $retryError = null;

    public function checkStatus(): void
    {
        $status = Evaluation::query()->whereKey($this->evaluationId)->value('status');

        if ($status !== EvaluationStatus::Processing) {
            $this->redirect($this->reloadUrl);
        }

        $this->skipRender();
    }

    public function retry(): void
    {
        $evaluation = Evaluation::query()->with(['submission', 'workspace'])->findOrFail($this->evaluationId);

        $ownerId = $evaluation->submission?->user_id ?? $evaluation->workspace?->user_id;
        abort_unless($ownerId === auth()->id(), 403);

        try {
            app(RetryEvaluation::class)($evaluation);
        } catch (ValidationException $exception) {
            $this->retryError = $exception->validator->errors()->first('evaluation');

            return;
        }

        $this->redirect($this->reloadUrl);
    }
};
?>

@php
    $status = Evaluation::query()->whereKey($evaluationId)->value('status');
@endphp

<div>
    @if ($status === EvaluationStatus::Processing)
        <div wire:poll.2s.keep-alive="checkStatus" class="flex items-center gap-2 text-sm text-base-content/60">
            <span class="loading loading-spinner loading-xs"></span>
            Checking for results…
        </div>
    @elseif ($status === EvaluationStatus::Failed)
        <div class="flex flex-col items-end gap-1">
            <button
                type="button"
                class="btn btn-outline btn-error btn-sm"
                wire:click="retry"
                wire:loading.attr="disabled"
                wire:target="retry">
                <span wire:loading.remove wire:target="retry">Retry evaluation</span>
                <span wire:loading wire:target="retry" class="loading loading-spinner loading-sm"></span>
            </button>
            @if ($retryError)
                <p class="text-xs text-error">{{ $retryError }}</p>
            @endif
        </div>
    @endif
</div>
