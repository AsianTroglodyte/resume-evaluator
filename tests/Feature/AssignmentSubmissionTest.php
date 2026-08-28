<?php

use App\Enums\AssigneeScope;
use App\Enums\EvaluationStatus;
use App\Enums\ModuleMembershipStatus;
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



test('Remove Submission', function () {
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

    $this->actingAs($user)
        ->delete(route('dashboard.modules.assignments.submissions.destroy', [$module, $assignment]))
        ->assertRedirect(route('dashboard.modules.assignments.show', [$module, $assignment]));

    expect(Submission::where('id', $submission->id)->first())->toBe(null)
        ->and(Evaluation::where('id', $evaluation->id)->first())->toBe(null);

    Storage::assertMissing($evalFilePath);
});


test("denies submission to an unassigned student", function () {
    /** @var TestCase **/
    $admin = User::factory()->admin()->create();
    $unassignedMember = User::factory()->create();
    $module = Module::factory()->createdBy($admin)->withMembers($unassignedMember)->create();
    // NOTE: we did not assign the Member to the assignment
    $assignment = Assignment::factory()->forModule($module)->create([
        'assignee_scope' => AssigneeScope::Selected
    ]);

    $job_description_text = file_get_contents(evaluationFixture("sample-job-listing.txt"));

    $this->actingAs($unassignedMember)
        ->post(route('dashboard.modules.assignments.submissions.store', [$module, $assignment]), [
            'resume_file' => new UploadedFile(
                evaluationFixture("sample-resume.pdf"),
                "sample-resume.pdf",
                'application/pdf',
                null,
                true,),
                'job_description' => $job_description_text,
            ])
        ->assertForbidden();
});


test("denies submission to a removed student", function () {
    /** @var TestCase **/
    $admin = User::factory()->admin()->create();
    $removedMember = User::factory()->create();
    $module = Module::factory()->createdBy($admin)->withMembers($removedMember)->create();
    // NOTE: we did not assign the Member to the assignment
    $assignment = Assignment::factory()->forModule($module)->withUsers($removedMember)->create([
        'assignee_scope' => AssigneeScope::Selected
    ]);

    // $membership
    $membershipRecord = ModuleMembership::where('id', $removedMember->id)->first();
    // in an actual removal we do a ton more changes. only concerned about status here.
    $membershipRecord->update(['status' => ModuleMembershipStatus::Removed]);

    // dd($membershipRecord);

    $job_description_text = file_get_contents(evaluationFixture("sample-job-listing.txt"));

    $this->actingAs($removedMember)
        ->post(route('dashboard.modules.assignments.submissions.store', [$module, $assignment]), [
            'resume_file' => new UploadedFile(
                evaluationFixture("sample-resume.pdf"),
                "sample-resume.pdf",
                'application/pdf',
                null,
                true,),
                'job_description' => $job_description_text,
            ])
        ->assertForbidden();
});


test("denies insructors and global admins from submitting and deleting submissions.", function () {
    /** @var TestCase **/
    $admin = User::factory()->admin()->create();
    $instructor = User::factory()->create();
    $module = Module::factory()->createdBy($admin)->withInstructor($instructor)->create();
    $assignment = Assignment::factory()->forModule($module)->create();

    $job_description_text = file_get_contents(evaluationFixture("sample-job-listing.txt"));
    $this->actingAs($admin)
        ->post(route('dashboard.modules.assignments.submissions.store', [$module, $assignment]), [
            'resume_file' => new UploadedFile(
                evaluationFixture("sample-resume.pdf"), "sample-resume.pdf", 'application/pdf', null, true,),
                'job_description' => $job_description_text,
            ])
        ->assertForbidden();

    $this->actingAs($instructor)
        ->post(route('dashboard.modules.assignments.submissions.store', [$module, $assignment]), [
            'resume_file' => new UploadedFile(
                evaluationFixture("sample-resume.pdf"), "sample-resume.pdf", 'application/pdf', null, true,),
                'job_description' => $job_description_text,
            ])
        ->assertForbidden();
    // NOTE: we did not assign the Member to the assignment
})->toDo();

test("cannote delete another another student's submission", function () {
    /** @var TestCase **/
    $admin = User::factory()->admin()->create();
    $removedMember = User::factory()->create();
    $module = Module::factory()->createdBy($admin)->withMembers($removedMember)->create();

    // $this->actingAs(admin)
    //     ->delete();
    // NOTE: we did not assign the Member to the assignment

})->todo();

test("returns 404 when assignemtn is request beneath a different module", function () {
    /** @var TestCase **/
    $admin = User::factory()->admin()->create();
    $removedMember = User::factory()->create();
    $module = Module::factory()->createdBy($admin)->withMembers($removedMember)->create();
    // NOTE: we did not assign the Member to the assignment
    $assignment = Assignment::factory()->forModule($module)->withUsers($removedMember)->create([
        'assignee_scope' => AssigneeScope::Selected
    ]);
})->todo();

// test("")