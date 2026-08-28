<?php

use App\Enums\EvaluationStatus;
use App\Models\Evaluation;
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
        ->assertSet('evaluation.status', EvaluationStatus::Completed);
});
