<?php

use App\Enums\EvaluationStatus;
use App\Jobs\EvaluateJob;
use App\Models\Evaluation;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

uses(RefreshDatabase::class);

it('creates processing evaluation; queues the job.', 
    function (string $format, string $mime) {
    /** @var TestCase $this */

    Queue::fake();

    $user = User::factory()->createOne();
    $workspace = Workspace::factory()->user($user->id)->createOne();

    // foreach ($fileExtensions as $fileExtension) {

    $job_description_text = file_get_contents(evaluationFixture('sample-job-listing.txt'));

    $this->actingAs($user)
        ->post(route('dashboard.workspaces.evaluations.store', $workspace), [
            'resume_file' => new UploadedFile(
                evaluationFixture("sample-resume.{$format}"),
                "sample-resume.{$format}",
                $mime,
                null,
                true,
            ),
            'job_description' => $job_description_text,
        ])
        ->assertRedirect(route('dashboard.workspaces.show', $workspace));

    $evaluation = $workspace->evaluations()->sole();

    expect($evaluation->status)->toBe(EvaluationStatus::Processing)
        ->and(trim($evaluation->job_description_text))
        ->toBe(trim($job_description_text));
    
    Queue::assertPushed(
        EvaluateJob::class,
        fn (EvaluateJob $job) => $job->evaluation->is($evaluation),
        );
})->with([
    'PDF' => ['pdf', 'application/pdf'],
    'legacy Word' => ['doc', 'application/msword'],
    'Word document' => [
        'docx',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ],
    'plain text' => ['txt', 'text/plain'],
]);

it ('prunes the evaluations beyond the latest five', function () {
    /** @var TestCase $this */
    $user = User::factory()->createOne();
    $workspace = Workspace::factory()->user($user->id)->createOne();

    $job_description_text = file_get_contents(evaluationFixture('sample-job-listing.txt'));

    for ($i = 0; $i < 5; $i++) {
        Evaluation::factory()
            ->withWorkspace($workspace->id)
            ->create();
    }

    $this->actingAs($user)
        ->post(route('dashboard.workspaces.evaluations.store', $workspace), 
            ['resume_file' => new UploadedFile(
                evaluationFixture("sample-resume.pdf"),
                "sample-resume.pdf",
                "application/pdf",
                null,
                true,
            ),
            'job_description' => $job_description_text]);

    $this->assertDatabaseCount('evaluations', 5);
});

it ('rejects a new run while one is processing', function () {
    /** @var TestCase $this */
    $user = User::factory()->createOne();
    $workspace = Workspace::factory()->user($user->id)->createOne();

    Evaluation::factory()
        ->withWorkspace($workspace->id)
        ->withStatus(EvaluationStatus::Processing)
        ->create();
    
    $response = $this->actingAs($user)
        ->post(route('dashboard.workspaces.evaluations.store', $workspace),
            ['resume_file' => new UploadedFile(
                evaluationFixture("sample-resume.pdf"),
                "sample-resume.pdf",
                "application/pdf",
                null,
                true
            )]);

    $this->assertDatabaseCount('evaluations', 1);
    $response->assertInvalid([
        'rate_limit' => 'An evaluation is already processing. Wait for it to complete'
    ]);
});
