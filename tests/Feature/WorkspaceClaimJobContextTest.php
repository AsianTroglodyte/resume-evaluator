<?php

use App\Enums\JobListingSource;
use App\Enums\ModuleJobListingScope;
use App\Models\Assignment;
use App\Models\Evaluation;
use App\Models\JobListing;
use App\Models\JobListingClaim;
use App\Models\Module;
use App\Models\ModuleGroup;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Storage::fake('local');
    Queue::fake();
});

/**
 * @return array{student: User, workspace: Workspace, module: Module, assignment: Assignment, listing: JobListing, claim: JobListingClaim}
 */
function practiceClaimSetup(): array
{
    $student = User::factory()->create();
    $workspace = Workspace::factory()->withUser($student)->create();
    $module = Module::factory()->withMembers($student)->create();
    $listing = JobListing::factory()->forModule($module)->create(['name' => 'Backend Intern', 'description' => 'Mock posting JD']);
    $assignment = Assignment::factory()->forModule($module)->withJobListings($listing)->create([
        'title' => 'Mock interview',
        'job_listing_source' => JobListingSource::Module,
        'module_job_listing_scope' => ModuleJobListingScope::Selected,
    ]);
    $claim = JobListingClaim::factory()->on($assignment, $listing)->by($student)->create();

    return compact('student', 'workspace', 'module', 'assignment', 'listing', 'claim');
}

function practiceResume(): UploadedFile
{
    return new UploadedFile(evaluationFixture('sample-resume.pdf'), 'sample-resume.pdf', 'application/pdf', null, true);
}

test('the workspace offers the student\'s current claims as job context', function () {
    ['student' => $student, 'workspace' => $workspace] = practiceClaimSetup();

    $this->actingAs($student)
        ->get(route('dashboard.workspaces.show', $workspace))
        ->assertOk()
        ->assertSee('Job context')
        ->assertSee('Backend Intern — Mock interview');
});

test('practising against a claim snapshots its JD without touching the claim', function () {
    ['student' => $student, 'workspace' => $workspace, 'listing' => $listing, 'claim' => $claim] = practiceClaimSetup();

    $this->actingAs($student)
        ->post(route('dashboard.workspaces.evaluations.store', $workspace), [
            'resume_file' => practiceResume(),
            'claim_id' => $claim->id,
            'job_description' => 'Ignored pasted text',
        ])
        ->assertSessionHasNoErrors();

    $evaluation = Evaluation::sole();
    expect($evaluation->workspace_id)->toBe($workspace->id)
        ->and($evaluation->job_listing_id)->toBe($listing->id)
        ->and($evaluation->job_description_text)->toBe('Mock posting JD')
        ->and(JobListingClaim::sole()->is($claim))->toBeTrue()
        ->and($claim->fresh()->updated_at->equalTo($claim->updated_at))->toBeTrue();
});

test('pasted job descriptions still work when no claim is chosen', function () {
    ['student' => $student, 'workspace' => $workspace] = practiceClaimSetup();

    $this->actingAs($student)
        ->post(route('dashboard.workspaces.evaluations.store', $workspace), [
            'resume_file' => practiceResume(),
            'claim_id' => '',
            'job_description' => 'Pasted posting',
        ])
        ->assertSessionHasNoErrors();

    expect(Evaluation::sole())
        ->job_listing_id->toBeNull()
        ->job_description_text->toBe('Pasted posting');
});

test('another student\'s claim cannot be used', function () {
    ['workspace' => $workspace, 'claim' => $claim] = practiceClaimSetup();
    $intruder = User::factory()->create();
    $intruderWorkspace = Workspace::factory()->withUser($intruder)->create();

    $this->actingAs($intruder)
        ->post(route('dashboard.workspaces.evaluations.store', $intruderWorkspace), [
            'resume_file' => practiceResume(),
            'claim_id' => $claim->id,
        ])
        ->assertSessionHasErrors('claim_id');

    expect(Evaluation::count())->toBe(0);
});

test('claims on assignments the student has lost access to are not offered', function () {
    ['student' => $student, 'workspace' => $workspace, 'module' => $module, 'assignment' => $assignment, 'claim' => $claim] = practiceClaimSetup();
    $group = ModuleGroup::factory()->forModule($module)->create();
    $assignment->update(['assignee_scope' => 'group', 'module_group_id' => $group->id]);

    $this->actingAs($student)
        ->get(route('dashboard.workspaces.show', $workspace))
        ->assertOk()
        ->assertDontSee('Backend Intern — Mock interview');

    $this->actingAs($student)
        ->post(route('dashboard.workspaces.evaluations.store', $workspace), [
            'resume_file' => practiceResume(),
            'claim_id' => $claim->id,
        ])
        ->assertSessionHasErrors('claim_id');
});
