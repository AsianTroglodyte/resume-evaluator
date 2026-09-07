<?php

use App\Enums\EvaluationStatus;
use App\Jobs\EvaluateJob;
use App\Models\Evaluation;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Storage::fake('local');
    Queue::fake();
});

it('creates processing evaluation; queues the job.',
    function (string $format, string $mime) {
        /** @var TestCase $this */
        Queue::fake();

        $user = User::factory()->createOne();
        $workspace = Workspace::factory()->withUser($user)->createOne();

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
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ],
        'plain text' => ['txt', 'text/plain'],
    ]);

it('prunes the evaluations beyond the latest five', function () {
    /** @var TestCase $this */
    $user = User::factory()->createOne();
    $workspace = Workspace::factory()->withUser($user)->createOne();

    $job_description_text = file_get_contents(evaluationFixture('sample-job-listing.txt'));

    for ($i = 0; $i < 5; $i++) {
        Evaluation::factory()
            ->withWorkspace($workspace)
            ->create();
    }

    $this->actingAs($user)
        ->post(route('dashboard.workspaces.evaluations.store', $workspace),
            ['resume_file' => new UploadedFile(
                evaluationFixture('sample-resume.pdf'),
                'sample-resume.pdf',
                'application/pdf',
                null,
                true,
            ),
                'job_description' => $job_description_text]);

    $this->assertDatabaseCount('evaluations', 5);
});

it('rejects a new run while one is processing', function () {
    /** @var TestCase $this */
    $user = User::factory()->createOne();
    $workspace = Workspace::factory()->withUser($user)->createOne();

    Evaluation::factory()
        ->withWorkspace($workspace)
        ->withStatus(EvaluationStatus::Processing)
        ->create();

    $response = $this->actingAs($user)
        ->post(route('dashboard.workspaces.evaluations.store', $workspace),
            ['resume_file' => new UploadedFile(
                evaluationFixture('sample-resume.pdf'),
                'sample-resume.pdf',
                'application/pdf',
                null,
                true
            )]);

    $this->assertDatabaseCount('evaluations', 1);
    $response->assertInvalid([
        'evaluation' => 'An evaluation is already processing. Wait for it to complete.',
    ]);
});

it("authorizes workspace create evaluation properly", function () {
    /** @var TestCase $this*/
    $unauthorizedUser = User::factory()->create();
    $authorizedUser = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $workspace = Workspace::factory()->withUser($authorizedUser)->create();

    foreach ([$unauthorizedUser, $admin] as $user) {
        $this->actingAs($user)
            ->post(route('dashboard.workspaces.evaluations.store', $workspace),
                ['resume_file' => new UploadedFile(
                    evaluationFixture('sample-resume.pdf'),
                    'sample-resume.pdf',
                    'application/pdf',
                    null,
                    true
            )])->assertForbidden();
    }

    Queue::assertCount(0);
    expect(Storage::disk('local')->allFiles())->toBeEmpty();

    $this->actingAs($authorizedUser)
        ->post(route('dashboard.workspaces.evaluations.store', $workspace),
            ['resume_file' => new UploadedFile(
                evaluationFixture('sample-resume.pdf'),
                'sample-resume.pdf',
                'application/pdf',
                null,
                true
        )])->assertRedirect();

    Queue::assertCount(1);
    expect(count(Storage::disk('local')->allFiles()))->toBe(1);
});

it("authorizes workspace deletion properly", function () {
    /** @var TestCase $this*/
    $unauthorizedUser = User::factory()->create();
    $authorizedUser = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $workspace = Workspace::factory()->withUser($authorizedUser)->create();

    foreach ([$unauthorizedUser, $admin] as $user) {
        $this->actingAs($user)
            ->delete(route('dashboard.workspaces.destroy', $workspace))
            ->assertForbidden();
    }
    $this->assertDatabaseHas('workspaces', ['id' => $workspace->id]);

    $this->actingAs($authorizedUser)
        ->delete(route('dashboard.workspaces.destroy', $workspace))
        ->assertRedirect();
    
    $this->assertDatabaseMissing('workspaces', ['id' => $workspace->id]);
});

it("authorizes workspace update properly", function () {
    /** @var TestCase $this*/
    $unauthorizedUser = User::factory()->create();
    $authorizedUser = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $workspace = Workspace::factory()->withUser($authorizedUser)->create();

    foreach ([$unauthorizedUser, $admin] as $user) {
        $this->actingAs($user)
            ->patch(route('dashboard.workspaces.update', $workspace),
            ["workspace_name" => "new name"])
            ->assertForbidden();
    }

    $this->actingAs($authorizedUser)
        ->patch(route('dashboard.workspaces.update', $workspace),
        ["workspace_name" => "new name"])
        ->assertRedirect();

    expect($workspace->fresh()->name)->toBe("new name");
});

it("authorizes workspace show properly", function () {
    /** @var TestCase $this*/
    $unauthorizedUser = User::factory()->create();
    $authorizedUser = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $workspace = Workspace::factory()->withUser($authorizedUser)->create();

    foreach ([$unauthorizedUser, $admin] as $user) {
        $this->actingAs($user)
            ->get(route('dashboard.workspaces.show', $workspace))
            ->assertForbidden();
    }

    $this->actingAs($authorizedUser)
        ->get(route('dashboard.workspaces.show', $workspace))
        ->assertSuccessful();
});

