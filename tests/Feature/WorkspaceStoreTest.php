<?php

use App\Jobs\EvaluateJob;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

uses(RefreshDatabase::class);

function evaluationFixture(string $filename): string
{
    return base_path("tests/Fixtures/evaluations/{$filename}");
}

it('accepts a pdf resume upload', function () {
    /** @var TestCase $this */
    Queue::fake();

    $user = User::factory()->createOne();
    $workspace = Workspace::factory()->user($user->id)->createOne();

    $this->actingAs($user)
        ->post(route('workspaces.evaluations.store', $workspace), [
            'resume_file' => new UploadedFile(
                evaluationFixture('sample-resume.pdf'),
                'sample-resume.pdf',
                'application/pdf',
                null,
                true,
            ),
            'job_description' => file_get_contents(evaluationFixture('sample-job-listing.txt')),
        ]);

    Queue::assertPushed(EvaluateJob::class);
});
