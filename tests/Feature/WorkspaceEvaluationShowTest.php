<?php

use App\Enums\EvaluationStatus;
use App\Models\Evaluation;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(fn () => Queue::fake());

test('the workspace lists evaluations as links to their own page', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->withUser($user)->create();
    $evaluation = Evaluation::factory()->create(['workspace_id' => $workspace->id, 'job_description_text' => 'A posting']);

    $this->actingAs($user)
        ->get(route('dashboard.workspaces.show', $workspace))
        ->assertOk()
        ->assertSee(route('dashboard.workspaces.evaluations.show', [$workspace, $evaluation]))
        ->assertSee('Against a pasted job description');
});

test('the owner sees resume, job description, and results on the evaluation page', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->withUser($user)->create(['name' => 'Fall applications']);
    $evaluation = Evaluation::factory()->create([
        'workspace_id' => $workspace->id,
        'resume_text' => 'Resume body text',
        'job_description_text' => 'Posting body text',
    ]);

    $this->actingAs($user)
        ->get(route('dashboard.workspaces.evaluations.show', [$workspace, $evaluation]))
        ->assertOk()
        ->assertSee('Practice evaluation')
        ->assertSee('Back to Fall applications')
        ->assertSee('Resume body text')
        ->assertSee('Posting body text');
});

test('other users cannot view a workspace evaluation', function () {
    $workspace = Workspace::factory()->withUser(User::factory()->create())->create();
    $evaluation = Evaluation::factory()->create(['workspace_id' => $workspace->id]);

    $this->actingAs(User::factory()->create())
        ->get(route('dashboard.workspaces.evaluations.show', [$workspace, $evaluation]))
        ->assertForbidden();
});

test('an evaluation from another workspace is not found', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->withUser($user)->create();
    $otherWorkspace = Workspace::factory()->withUser($user)->create();
    $evaluation = Evaluation::factory()->create(['workspace_id' => $otherWorkspace->id]);

    $this->actingAs($user)
        ->get(route('dashboard.workspaces.evaluations.show', [$workspace, $evaluation]))
        ->assertNotFound();
});

test('the workspace owner can retry a failed practice evaluation', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->withUser($user)->create();
    $evaluation = Evaluation::factory()->failed()->create(['workspace_id' => $workspace->id]);

    $this->actingAs($user)
        ->get(route('dashboard.workspaces.evaluations.show', [$workspace, $evaluation]))
        ->assertSee('Retry evaluation');

    Livewire::actingAs($user)
        ->test('evaluation.submission-status', ['evaluationId' => $evaluation->id, 'reloadUrl' => '/back'])
        ->call('retry')
        ->assertRedirect('/back');

    expect($evaluation->fresh()->status)->toBe(EvaluationStatus::Processing);

    Livewire::actingAs(User::factory()->create())
        ->test('evaluation.submission-status', ['evaluationId' => $evaluation->id, 'reloadUrl' => '/back'])
        ->call('retry')
        ->assertForbidden();
});
