<?php

use App\Enums\EvaluationStatus;
use App\Models\Evaluation;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        ->withWorkspace($workspace->id)
        ->withStatus(EvaluationStatus::Processing)
        ->create();

    $failedEvaluation = Evaluation::factory()
        ->failed()
        ->withWorkspace($workspace->id)
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
