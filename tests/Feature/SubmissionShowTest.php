<?php

use App\Enums\EvaluationStatus;
use App\Models\Assignment;
use App\Models\Evaluation;
use App\Models\Module;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

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

test('students and outsiders cannot view a submission', function () {
    ['module' => $module, 'assignment' => $assignment, 'submission' => $submission, 'student' => $student] = submissionShowSetup();
    $outsider = User::factory()->create();

    $url = route('dashboard.modules.assignments.submissions.show', [$module, $assignment, $submission]);

    $this->actingAs($student)->get($url)->assertForbidden();
    $this->actingAs($outsider)->get($url)->assertForbidden();
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
