<?php

use App\Enums\EvaluationStatus;
use App\Jobs\EvaluateJob;
use App\Models\Assignment;
use App\Models\Module;
use App\Models\ModuleMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
// use Tests\TestCase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Storage::fake('local');
    Queue::fake();
});

test('Submission', function (string $format, string $mime) {
    /** @var TestCase $this */

    $user = User::factory()->create();
    $module = Module::factory()->create();
    $assignment = Assignment::factory()->forModule($module)->withUsers($user)->create();

    ModuleMembership::factory()
        ->module($module)
        ->user($user)
        ->create();

    $job_description_text = file_get_contents(evaluationFixture("sample-job-listing.txt"));

    $this->actingAs($user)
        ->post(route('dashboard.modules.assignments.submissions.store', [$module, $assignment]), [
            'resume_file' => new UploadedFile(
                evaluationFixture("sample-resume.{$format}"),
                "sample-resume.{$format}",
                $mime,
                null,
                true,
            ),
            'job_description' => $job_description_text,
        ])
        ->assertRedirect(route('dashboard.modules.assignments.show', [$module, $assignment]));
    
    $evaluation = $assignment->evaluationFor($user)->sole(); 
    // dd($evaluation);

    expect($evaluation->status)->toBe(EvaluationStatus::Processing)
        ->and(trim($evaluation->job_description_text))->toBe(trim($job_description_text));;

    Queue::assertPushed(
        EvaluateJob::class,
        fn (EvaluateJob $job) => $job->evaluation->is($evaluation)
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



test('Remove Submission', function () {
    /** @var TestCase $this **/
    $user = User::factory()->create();
    $module = Module::factory()->create();
    $assignment = Assignment::factory()->forModule($module)->withUsers($user)->create();
    
    ModuleMembership::factory()
        ->module($module)
        ->user($user)
        ->create();

    $job_description_text = file_get_contents(evaluationFixture("sample-job-listing.txt"));

    $this->actingAs($user);
        // ->post
    // $
})->todo();


