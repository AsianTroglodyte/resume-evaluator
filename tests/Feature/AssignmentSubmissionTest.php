<?php

use App\Enums\EvaluationStatus;
use App\Jobs\EvaluateJob;
use App\Models\Assignment;
use App\Models\Evaluation;
use App\Models\Module;
use App\Models\ModuleMembership;
use App\Models\Submission;
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
    $module = Module::factory()->withMembers($user)->create();
    $assignment = Assignment::factory()->forModule($module)->withUsers($user)->create();

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
    $submission = $assignment->submissionFor($user)->sole(); 
    // dd($evaluation);

    expect($evaluation->status)->toBe(EvaluationStatus::Processing)
        ->and(trim($evaluation->job_description_text))->toBe(trim($job_description_text))
        ->and($submission->id)->toBe($evaluation->submission_id);

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



test('Remove Submission', function (string $format, string $mime) {
    /** @var TestCase $this **/

    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    $module = Module::factory()->createdBy($admin)->withMembers([$user])->create();
    $assignment = Assignment::factory()->forModule($module)->withUsers($user)->create();
    $submission = Submission::factory()->withAssignment($assignment)->withUser($user)->create();
    $evaluation = Evaluation::factory()->withSubmission($submission)->create();
    

    $evalFilePath = $evaluation->resume_file_path;
    Storage::put($evalFilePath, 'Contents');
    
    Storage::assertExists($evalFilePath);
    // dd($evalFilePath);
    // expect()

    $this->actingAs($user)
        ->delete(route('dashboard.modules.assignments.submissions.destroy', [$module, $assignment]))
        ->assertRedirect(route('dashboard.modules.assignments.show', [$module, $assignment]));

    expect(Submission::where('id', $submission->id)->first())->toBe(null)
        ->and(Evaluation::where('id', $evaluation->id)->first())->toBe(null);

    Storage::assertMissing($evalFilePath);
});


