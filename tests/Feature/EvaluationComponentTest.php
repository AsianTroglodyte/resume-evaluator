<?php

use App\Actions\RetryEvaluation;
use App\Enums\EvaluationStatus;
use App\Jobs\EvaluateJob;
use App\Models\Evaluation;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('toggles expanded state without fighting native details behavior', function () {
    $evaluation = Evaluation::factory()->create([
        'status' => EvaluationStatus::Completed,
    ]);

    Livewire::test('evaluation.evaluation', ['evaluation' => $evaluation])
        ->assertSet('isOpen', false)
        ->call('toggleExpanded')
        ->assertSet('isOpen', true)
        ->call('toggleExpanded')
        ->assertSet('isOpen', false);
});

it('refreshes evaluation status while polling', function () {
    $evaluation = Evaluation::factory()->create([
        'status' => EvaluationStatus::Processing,
        'evaluation_data' => null,
    ]);

    $component = Livewire::test('evaluation.evaluation', ['evaluation' => $evaluation]);

    $evaluation->update(['status' => EvaluationStatus::Completed]);

    $component
        ->call('loadEvaluation')
        ->assertSet('evaluation.status', EvaluationStatus::Completed)
        ->assertSee('completed', false);
});

it('dispatches evaluation-blocked when retry is blocked by a processing evaluation', function () {
    $workspace = Workspace::factory()->create();

    Evaluation::factory()
        ->withWorkspace($workspace)
        ->withStatus(EvaluationStatus::Processing)
        ->create();

    $failedEvaluation = Evaluation::factory()
        ->failed()
        ->withWorkspace($workspace)
        ->create();

    Livewire::test('evaluation.evaluation', [
        'evaluation' => $failedEvaluation,
    ])
        ->call('retryEvaluation')
        ->assertDispatched('evaluation-blocked', message: 'An evaluation is already processing. Wait for it to complete.');
});

it('polls from a stable root element while processing', function () {
    $evaluation = Evaluation::factory()->create([
        'status' => EvaluationStatus::Processing,
    ]);

    $html = Livewire::test('evaluation.evaluation', ['evaluation' => $evaluation])->html();

    expect($html)->toContain('wire:poll.1s.keep-alive="loadEvaluation"');
    expect($html)->not->toMatch('/<details[^>]*wire:poll/');
});

// --- Evaluation::ensureCanRetry() ---
it('blocks workspace retry when another evaluation is processing', function() {
    $workspace = Workspace::factory()->create();
    Evaluation::factory()
        ->withWorkspace($workspace)
        ->withStatus(EvaluationStatus::Processing)
        ->create();
    $failedEvaluation = Evaluation::factory()->failed()->withWorkspace($workspace)->create();

    $failedEvaluation->ensureCanRetry();    
})->throws(
    ValidationException::class, 
    'An evaluation is already processing. Wait for it to complete.'
);

it('allows workspace retry when none are processing', function() {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->withUser($user)->create();
    $failedEvaluation = Evaluation::factory()
        ->withWorkspace($workspace)
        ->withStatus(EvaluationStatus::Failed)->create();

    expect(fn () => $failedEvaluation->ensureCanRetry())->not->toThrow(ValidationException::class);
});

it('blocks submission retry while processing', function() {
    $processingEvaluation = Evaluation::factory()
        ->withSubmission()
        ->withStatus(EvaluationStatus::Processing)->create();
        $processingEvaluation->ensureCanRetry();
})->throws(
    ValidationException::class,
    'This evaluation is already processing.'
);

it('rejects retry when evaluation has no context', function() {
    
})->toDo();

// --- RetryEvaluation ---
it('retries a failed workspace evaluation', function() {
    Queue::fake();
    $user = User::factory()->create();
    $workspace = Workspace::factory()->withUser($user)->create();
    $failedEvaluation = Evaluation::factory()
        ->withWorkspace($workspace)
        ->withStatus(EvaluationStatus::Failed)->create();

    app(RetryEvaluation::class)($failedEvaluation);

    expect($failedEvaluation->fresh()->status)->toBe(EvaluationStatus::Processing);
    Queue::assertPushed(
        EvaluateJob::class,
        fn (EvaluateJob $job) => $job->evaluation->is($failedEvaluation)
    );
});

it('retries a failed submission evaluation', function() {

})->toDo();


