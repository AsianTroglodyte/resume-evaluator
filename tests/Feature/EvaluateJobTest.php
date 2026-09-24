<?php

use App\Enums\EvaluationStatus;
use App\Jobs\EvaluateJob;
use App\Models\Evaluation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Storage::fake('local');
});

test('Evaluate Job goes through.', function () {
    Http::fake([
        '*evaluate' => Http::response([
            'resume_text' => 'Extracted resume...',
            'match_percent' => 80,
            'evaluation_data' => null
        ], 200)
    ]);

    $evaluation = Evaluation::factory()
        ->withStatus(EvaluationStatus::Processing)
        ->create();

    Storage::disk('local')->put(
        'resumes/tmp/sample-resume.pdf',
        file_get_contents(evaluationFixture('sample-resume.pdf'))
    );

    EvaluateJob::dispatchSync( // or ->handle() — see below
        'resumes/tmp/sample-resume.pdf',
        'test job description',
        $evaluation
    );

    $evaluation->refresh();
    expect($evaluation->status)->toBe(EvaluationStatus::Completed)
        ->and($evaluation->resume_text)->toBe("Extracted resume...");
});


test('Failed connection.', function () {
    Http::fake([
        '*evaluate' => Http::failedConnection()
    ]);

    $evaluation = Evaluation::factory()
        ->withStatus(EvaluationStatus::Processing)
        ->create();

    Storage::disk('local')->put(
        'resumes/tmp/sample-resume.pdf',
        file_get_contents(evaluationFixture('sample-resume.pdf'))
    );

    EvaluateJob::dispatchSync( // or ->handle() — see below
        'resumes/tmp/sample-resume.pdf',
        'test job description',
        $evaluation
    );

    $evaluation->refresh();

    expect($evaluation->status)->toBe(EvaluationStatus::Failed)
        ->and($evaluation->failure_reason)->toBe('Evaluation service seems to be down.')
        ->and($evaluation->resume_text)->toBe('')
        ->and($evaluation->evaluation_data)->toBeNull();
});


test('Response Failed.', function () {
    Http::fake([
        '*evaluate' => Http::response([
            'resume_text' => 'partial text',
        ], 500)
    ]);

    $evaluation = Evaluation::factory()
        ->withStatus(EvaluationStatus::Processing)
        ->create();

    Storage::disk('local')->put(
        'resumes/tmp/sample-resume.pdf',
        file_get_contents(evaluationFixture('sample-resume.pdf'))
    ); 

    EvaluateJob::dispatchSync( // or ->handle() — see below
        'resumes/tmp/sample-resume.pdf',
        'test job description',
        $evaluation
    );

    $evaluation->refresh();

    expect($evaluation->status)->toBe(EvaluationStatus::Failed)
        ->and($evaluation->failure_reason)->toBe('Evaluation Service was unable to process your request.')
        ->and($evaluation->resume_text)->toBe('partial text')
        ->and($evaluation->evaluation_data)->toMatchArray([
            'resume_text' => 'partial text'
        ]);
});