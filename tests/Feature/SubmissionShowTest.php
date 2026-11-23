<?php

use App\Enums\EvaluationStatus;
use App\Models\Assignment;
use App\Models\Evaluation;
use App\Models\Module;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(fn () => Queue::fake());

/**
 * @return array{module: Module, assignment: Assignment, submission: Submission, instructor: User, student: User}
 */
function submissionShowSetup(): array
{
    $instructor = User::factory()->create();
    $student = User::factory()->create();
    $module = Module::factory()->withInstructor($instructor)->withMembers($student)->create();
    $assignment = Assignment::factory()->forModule($module)->withUsers($student)->create();
    $submission = Submission::factory()->forAssignment($assignment)->withUser($student)->create();

    return compact('module', 'assignment', 'submission', 'instructor', 'student');
}

test('instructors and global admins can view a submission', function () {
    ['module' => $module, 'assignment' => $assignment, 'submission' => $submission, 'instructor' => $instructor, 'student' => $student] = submissionShowSetup();
    Evaluation::factory()->withSubmission($submission)->create();
    $admin = User::factory()->admin()->create();

    $url = route('dashboard.modules.assignments.submissions.show', [$module, $assignment, $submission]);

    $this->actingAs($instructor)->get($url)->assertOk()->assertSee($student->email);
    $this->actingAs($admin)->get($url)->assertOk()->assertSee($student->email);
});

test('the submitting student can view their own submission', function () {
    ['module' => $module, 'assignment' => $assignment, 'submission' => $submission, 'student' => $student] = submissionShowSetup();
    Evaluation::factory()->withSubmission($submission)->create();

    $this->actingAs($student)
        ->get(route('dashboard.modules.assignments.submissions.show', [$module, $assignment, $submission]))
        ->assertOk()
        ->assertSee('Your submission');
});

test('other students and outsiders cannot view a submission', function () {
    ['module' => $module, 'assignment' => $assignment, 'submission' => $submission] = submissionShowSetup();
    $classmate = User::factory()->create();
    $module->memberships()->create([
        'user_id' => $classmate->id,
        'role_in_module' => 'student',
        'status' => 'active',
        'added_by_user_id' => $module->created_by_user_id,
    ]);
    $outsider = User::factory()->create();

    $url = route('dashboard.modules.assignments.submissions.show', [$module, $assignment, $submission]);

    $this->actingAs($classmate)->get($url)->assertForbidden();
    $this->actingAs($outsider)->get($url)->assertForbidden();
});

test('the assignment page links students to their submission', function () {
    ['module' => $module, 'assignment' => $assignment, 'submission' => $submission, 'student' => $student] = submissionShowSetup();
    Evaluation::factory()->withSubmission($submission)->create();

    $this->actingAs($student)
        ->get(route('dashboard.modules.assignments.show', [$module, $assignment]))
        ->assertOk()
        ->assertSee(route('dashboard.modules.assignments.submissions.show', [$module, $assignment, $submission]));
});

test('the status component reloads the page once evaluation finishes', function () {
    ['submission' => $submission] = submissionShowSetup();
    $evaluation = Evaluation::factory()->withSubmission($submission)->withStatus(EvaluationStatus::Processing)->create([
        'evaluation_data' => null,
    ]);

    $component = Livewire::test('evaluation.submission-status', ['evaluationId' => $evaluation->id, 'reloadUrl' => '/back'])
        ->call('checkStatus')
        ->assertNoRedirect();

    $evaluation->update(['status' => EvaluationStatus::Completed]);

    $component->call('checkStatus')->assertRedirect('/back');
});

test('only the submitting student can retry a failed evaluation', function () {
    ['submission' => $submission, 'student' => $student, 'instructor' => $instructor] = submissionShowSetup();
    $evaluation = Evaluation::factory()->withSubmission($submission)->failed()->create();

    Livewire::actingAs($instructor)
        ->test('evaluation.submission-status', ['evaluationId' => $evaluation->id, 'reloadUrl' => '/back'])
        ->call('retry')
        ->assertForbidden();

    Livewire::actingAs($student)
        ->test('evaluation.submission-status', ['evaluationId' => $evaluation->id, 'reloadUrl' => '/back'])
        ->call('retry')
        ->assertRedirect('/back');

    expect($evaluation->fresh()->status)->toBe(EvaluationStatus::Processing);
});

test('a submission from a different assignment is not found', function () {
    ['module' => $module, 'submission' => $submission, 'instructor' => $instructor] = submissionShowSetup();
    $otherAssignment = Assignment::factory()->forModule($module)->create();

    $this->actingAs($instructor)
        ->get(route('dashboard.modules.assignments.submissions.show', [$module, $otherAssignment, $submission]))
        ->assertNotFound();
});

test('shows the failure reason for a failed evaluation', function () {
    ['module' => $module, 'assignment' => $assignment, 'submission' => $submission, 'instructor' => $instructor] = submissionShowSetup();
    Evaluation::factory()->withSubmission($submission)->failed()->create([
        'failure_reason' => 'Resume could not be parsed.',
    ]);

    $this->actingAs($instructor)
        ->get(route('dashboard.modules.assignments.submissions.show', [$module, $assignment, $submission]))
        ->assertOk()
        ->assertSee('Evaluation failed')
        ->assertSee('Resume could not be parsed.');
});

test('shows an in progress message for a processing evaluation', function () {
    ['module' => $module, 'assignment' => $assignment, 'submission' => $submission, 'instructor' => $instructor] = submissionShowSetup();
    Evaluation::factory()->withSubmission($submission)->withStatus(EvaluationStatus::Processing)->create([
        'evaluation_data' => null,
    ]);

    $this->actingAs($instructor)
        ->get(route('dashboard.modules.assignments.submissions.show', [$module, $assignment, $submission]))
        ->assertOk()
        ->assertSee('Evaluation in progress');
});

test('shows a fallback when a completed evaluation has no feedback', function () {
    ['module' => $module, 'assignment' => $assignment, 'submission' => $submission, 'instructor' => $instructor] = submissionShowSetup();
    Evaluation::factory()->withSubmission($submission)->create([
        'evaluation_data' => [],
    ]);

    $this->actingAs($instructor)
        ->get(route('dashboard.modules.assignments.submissions.show', [$module, $assignment, $submission]))
        ->assertOk()
        ->assertSee('Evaluation completed but no feedback was returned.');
});
