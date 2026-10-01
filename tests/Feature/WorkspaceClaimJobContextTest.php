<?php

use App\Enums\JobListingSource;
use App\Enums\ModuleJobListingScope;
use App\Models\Assignment;
use App\Models\Evaluation;
use App\Models\JobListing;
use App\Models\JobListingClaim;
use App\Models\Module;
use App\Models\ModuleGroup;
use App\Models\Submission;
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

test('the dropdown offers claims; the browse modal groups every listing by module and assignment', function () {
    ['student' => $student, 'workspace' => $workspace, 'module' => $module, 'assignment' => $assignment] = practiceClaimSetup();
    $module->update(['name' => 'Senior Seminar']);
    $unclaimed = JobListing::factory()->forModule($module)->create(['name' => 'Alpha Analyst']);
    $assignment->jobListings()->attach($unclaimed->id);

    $this->actingAs($student)
        ->get(route('dashboard.workspaces.show', $workspace))
        ->assertOk()
        ->assertSee('Backend Intern — Mock interview (your claim)')
        ->assertSee('Browse all listings')
        ->assertSeeInOrder(['Practice job listings', 'Senior Seminar', 'Mock interview', 'Backend Intern', 'Your claim', 'Alpha Analyst']);
});

test('students can practise against an unclaimed listing without claiming it', function () {
    ['student' => $student, 'workspace' => $workspace, 'module' => $module, 'assignment' => $assignment] = practiceClaimSetup();
    $unclaimed = JobListing::factory()->forModule($module)->create(['description' => 'Other posting JD']);
    $assignment->jobListings()->attach($unclaimed->id);

    $this->actingAs($student)
        ->post(route('dashboard.workspaces.evaluations.store', $workspace), [
            'resume_file' => practiceResume(),
            'job_listing_id' => $unclaimed->id,
        ])
        ->assertSessionHasNoErrors();

    expect(Evaluation::sole()->job_description_text)->toBe('Other posting JD')
        ->and($assignment->claimFor($student)->sole()->job_listing_id)->not->toBe($unclaimed->id);
});

test('practising against a claim snapshots its JD without touching the claim', function () {
    ['student' => $student, 'workspace' => $workspace, 'listing' => $listing, 'claim' => $claim] = practiceClaimSetup();

    $this->actingAs($student)
        ->post(route('dashboard.workspaces.evaluations.store', $workspace), [
            'resume_file' => practiceResume(),
            'job_listing_id' => $listing->id,
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
            'job_listing_id' => '',
            'job_description' => 'Pasted posting',
        ])
        ->assertSessionHasNoErrors();

    expect(Evaluation::sole())
        ->job_listing_id->toBeNull()
        ->job_description_text->toBe('Pasted posting');
});

test('listings on assignments the student is not given cannot be used', function () {
    ['listing' => $listing] = practiceClaimSetup();
    $outsider = User::factory()->create();
    $outsiderWorkspace = Workspace::factory()->withUser($outsider)->create();

    $this->actingAs($outsider)
        ->post(route('dashboard.workspaces.evaluations.store', $outsiderWorkspace), [
            'resume_file' => practiceResume(),
            'job_listing_id' => $listing->id,
        ])
        ->assertSessionHasErrors('job_listing_id');

    expect(Evaluation::count())->toBe(0);
});

test('listings on assignments the student has lost access to are not offered', function () {
    ['student' => $student, 'workspace' => $workspace, 'module' => $module, 'assignment' => $assignment, 'listing' => $listing] = practiceClaimSetup();
    $group = ModuleGroup::factory()->forModule($module)->create();
    $assignment->update(['assignee_scope' => 'group', 'module_group_id' => $group->id]);

    $this->actingAs($student)
        ->get(route('dashboard.workspaces.show', $workspace))
        ->assertOk()
        ->assertDontSee('Backend Intern');

    $this->actingAs($student)
        ->post(route('dashboard.workspaces.evaluations.store', $workspace), [
            'resume_file' => practiceResume(),
            'job_listing_id' => $listing->id,
        ])
        ->assertSessionHasErrors('job_listing_id');
});

test('students can claim from the browse modal and return to their workspace', function () {
    ['student' => $student, 'workspace' => $workspace, 'module' => $module, 'assignment' => $assignment] = practiceClaimSetup();
    $other = JobListing::factory()->forModule($module)->create(['name' => 'Data Analyst']);
    $assignment->jobListings()->attach($other->id, ['capacity' => 2]);

    $this->actingAs($student)
        ->get(route('dashboard.workspaces.show', $workspace))
        ->assertSee('Switch claim')
        ->assertSee('0 / 2 slots taken');

    $this->actingAs($student)
        ->put(route('dashboard.modules.assignments.claim.update', [$module, $assignment]), [
            'job_listing_id' => $other->id,
            'workspace_id' => $workspace->id,
        ])
        ->assertRedirect(route('dashboard.workspaces.show', $workspace))
        ->assertSessionHas('job_listing_id', $other->id);

    expect($assignment->claimFor($student)->sole()->job_listing_id)->toBe($other->id);

    $this->actingAs($student)
        ->delete(route('dashboard.modules.assignments.claim.destroy', [$module, $assignment]), ['workspace_id' => $workspace->id])
        ->assertRedirect(route('dashboard.workspaces.show', $workspace));

    expect($assignment->claimFor($student)->exists())->toBeFalse();
});

test('claim redirects only return to the student\'s own workspace', function () {
    ['student' => $student, 'module' => $module, 'assignment' => $assignment, 'listing' => $listing] = practiceClaimSetup();
    $someoneElsesWorkspace = Workspace::factory()->withUser(User::factory()->create())->create();
    $assignment->claimFor($student)->delete();

    $this->actingAs($student)
        ->put(route('dashboard.modules.assignments.claim.update', [$module, $assignment]), [
            'job_listing_id' => $listing->id,
            'workspace_id' => $someoneElsesWorkspace->id,
        ])
        ->assertRedirect(route('dashboard.modules.assignments.show', [$module, $assignment]));
});

test('instructors browsing listings in a workspace get no claim controls', function () {
    $instructor = User::factory()->create();
    $workspace = Workspace::factory()->withUser($instructor)->create();
    $module = Module::factory()->withInstructor($instructor)->create();
    $listing = JobListing::factory()->forModule($module)->create();
    $assignment = Assignment::factory()->forModule($module)->withJobListings($listing)->create([
        'job_listing_source' => JobListingSource::Module,
        'module_job_listing_scope' => ModuleJobListingScope::Selected,
    ]);

    $this->actingAs($instructor)
        ->get(route('dashboard.workspaces.show', $workspace))
        ->assertOk()
        ->assertSee('Browse all listings')
        ->assertDontSee(route('dashboard.modules.assignments.claim.update', [$module, $assignment]));
});

test('browse modal headings show due date and submitted status', function () {
    ['student' => $student, 'workspace' => $workspace, 'assignment' => $assignment] = practiceClaimSetup();
    $assignment->update(['due_date' => now()->addDays(3)->setTime(17, 0)]);

    $this->actingAs($student)
        ->get(route('dashboard.workspaces.show', $workspace))
        ->assertSee('Due '.$assignment->due_date->format('M j, g:i A'))
        ->assertSee('Not submitted');

    Submission::factory()->for($assignment)->for($student)->create();

    $this->actingAs($student)
        ->get(route('dashboard.workspaces.show', $workspace))
        ->assertSee('Submitted')
        ->assertDontSee('Not submitted');
});
