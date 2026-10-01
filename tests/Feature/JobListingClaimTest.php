<?php

use App\Enums\JobListingSource;
use App\Enums\ModuleJobListingScope;
use App\Models\Assignment;
use App\Models\JobListing;
use App\Models\JobListingClaim;
use App\Models\Module;
use App\Models\ModuleGroup;
use App\Models\Submission;
use App\Models\User;
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
 * @param  array<int, ?int>  $capacities  listing id => capacity
 */
function claimableAssignment(Module $module, array $capacities, JobListingSource $source = JobListingSource::Module, ?ModuleGroup $group = null): Assignment
{
    $factory = $group ? Assignment::factory()->forGroup($group) : Assignment::factory()->forModule($module);

    $assignment = $factory->create([
        'job_listing_source' => $source,
        'module_job_listing_scope' => ModuleJobListingScope::Selected,
    ]);

    $assignment->jobListings()->sync(collect($capacities)->map(fn (?int $capacity) => ['capacity' => $capacity])->all());

    return $assignment;
}

function claim(Module $module, Assignment $assignment, JobListing $jobListing): array
{
    return [route('dashboard.modules.assignments.claim.update', [$module, $assignment]), ['job_listing_id' => $jobListing->id]];
}

function resumeUpload(): UploadedFile
{
    return new UploadedFile(evaluationFixture('sample-resume.pdf'), 'sample-resume.pdf', 'application/pdf', null, true);
}

test('students claim a listing and switching releases the old slot', function () {
    [$student, $other] = User::factory(2)->create();
    $module = Module::factory()->withMembers([$student, $other])->create();
    [$first, $second] = JobListing::factory(2)->forModule($module)->create();
    $assignment = claimableAssignment($module, [$first->id => 1, $second->id => 1]);

    $this->actingAs($student)->put(...claim($module, $assignment, $first))
        ->assertRedirect(route('dashboard.modules.assignments.show', [$module, $assignment]));
    expect($assignment->claimFor($student)->sole()->job_listing_id)->toBe($first->id);

    $this->actingAs($student)->put(...claim($module, $assignment, $second));
    expect($assignment->claims()->count())->toBe(1)
        ->and($assignment->claimFor($student)->sole()->job_listing_id)->toBe($second->id);

    $this->actingAs($other)->put(...claim($module, $assignment, $first))
        ->assertSessionHasNoErrors();
});

test('a full listing cannot be claimed', function () {
    [$early, $late] = User::factory(2)->create();
    $module = Module::factory()->withMembers([$early, $late])->create();
    $listing = JobListing::factory()->forModule($module)->create(['name' => 'Data Analyst']);
    $assignment = claimableAssignment($module, [$listing->id => 1]);

    $this->actingAs($early)->put(...claim($module, $assignment, $listing))->assertSessionHasNoErrors();

    $this->actingAs($late)->put(...claim($module, $assignment, $listing))
        ->assertSessionHasErrorsIn('claim', ['job_listing_id' => 'Data Analyst is full. Choose another listing.']);

    expect($assignment->claimFor($late)->exists())->toBeFalse();
});

test('a blank capacity is unlimited and "all module listings" are claimable', function () {
    $students = User::factory(3)->create();
    $module = Module::factory()->withMembers($students)->create();
    $listing = JobListing::factory()->forModule($module)->create();
    $selected = claimableAssignment($module, [$listing->id => null]);
    $all = Assignment::factory()->forModule($module)->create([
        'job_listing_source' => JobListingSource::Module,
        'module_job_listing_scope' => ModuleJobListingScope::All,
    ]);

    foreach ($students as $student) {
        $this->actingAs($student)->put(...claim($module, $selected, $listing))->assertSessionHasNoErrors();
        $this->actingAs($student)->put(...claim($module, $all, $listing))->assertSessionHasNoErrors();
    }

    expect($selected->claims()->count())->toBe(3)
        ->and($all->claims()->count())->toBe(3);
});

test('listings not allowed on the assignment cannot be claimed', function () {
    $student = User::factory()->create();
    $module = Module::factory()->withMembers($student)->create();
    $allowed = JobListing::factory()->forModule($module)->create();
    $notAllowed = JobListing::factory()->forModule($module)->create();
    $assignment = claimableAssignment($module, [$allowed->id => 5]);

    $this->actingAs($student)->put(...claim($module, $assignment, $notAllowed))
        ->assertSessionHasErrorsIn('claim', 'job_listing_id');
});

test('instructors, outsiders, and external-JD assignments cannot claim', function () {
    $instructor = User::factory()->create();
    $student = User::factory()->create();
    $outsider = User::factory()->create();
    $module = Module::factory()->withInstructor($instructor)->withMembers($student)->create();
    $listing = JobListing::factory()->forModule($module)->create();
    $assignment = claimableAssignment($module, [$listing->id => 5]);
    $external = claimableAssignment($module, [$listing->id => 5], JobListingSource::External);

    $this->actingAs($instructor)->put(...claim($module, $assignment, $listing))->assertForbidden();
    $this->actingAs($outsider)->put(...claim($module, $assignment, $listing))->assertForbidden();
    $this->actingAs($student)->put(...claim($module, $external, $listing))->assertForbidden();
});

test('students can release their claim', function () {
    $student = User::factory()->create();
    $module = Module::factory()->withMembers($student)->create();
    $listing = JobListing::factory()->forModule($module)->create();
    $assignment = claimableAssignment($module, [$listing->id => 1]);
    JobListingClaim::factory()->on($assignment, $listing)->by($student)->create();

    $this->actingAs($student)
        ->delete(route('dashboard.modules.assignments.claim.destroy', [$module, $assignment]))
        ->assertRedirect();

    expect($assignment->claims()->exists())->toBeFalse();
});

test('module-listing submissions require a claim and snapshot its job description', function () {
    $student = User::factory()->create();
    $module = Module::factory()->withMembers($student)->create();
    $listing = JobListing::factory()->forModule($module)->create(['description' => 'Mock posting JD']);
    $assignment = claimableAssignment($module, [$listing->id => 1]);
    $submit = route('dashboard.modules.assignments.submissions.store', [$module, $assignment]);

    $this->actingAs($student)->post($submit, ['resume_file' => resumeUpload()])
        ->assertSessionHasErrors(['submission' => 'Claim a job listing before submitting.']);
    expect(Submission::count())->toBe(0);

    JobListingClaim::factory()->on($assignment, $listing)->by($student)->create();

    $this->actingAs($student)->post($submit, [
        'resume_file' => resumeUpload(),
        'job_description' => 'Pasted text that should be ignored',
    ])->assertSessionHasNoErrors();

    $evaluation = Submission::sole()->evaluation;
    expect($evaluation->job_listing_id)->toBe($listing->id)
        ->and($evaluation->job_description_text)->toBe('Mock posting JD');
});

test('"both" assignments accept a pasted job description when nothing is claimed', function () {
    $student = User::factory()->create();
    $module = Module::factory()->withMembers($student)->create();
    $listing = JobListing::factory()->forModule($module)->create();
    $assignment = claimableAssignment($module, [$listing->id => 1], JobListingSource::Both);

    $this->actingAs($student)->post(route('dashboard.modules.assignments.submissions.store', [$module, $assignment]), [
        'resume_file' => resumeUpload(),
        'job_description' => 'External posting',
    ])->assertSessionHasNoErrors();

    $evaluation = Submission::sole()->evaluation;
    expect($evaluation->job_listing_id)->toBeNull()
        ->and($evaluation->job_description_text)->toBe('External posting');
});

test('moving a student out of a group releases unsubmitted claims on that group\'s assignments', function () {
    $instructor = User::factory()->create();
    [$unsubmitted, $submitted] = User::factory(2)->create();
    $module = Module::factory()->withInstructor($instructor)->withMembers([$unsubmitted, $submitted])->create();
    $it = ModuleGroup::factory()->forModule($module)->create(['name' => 'IT']);
    placeInGroup($unsubmitted, $it);
    placeInGroup($submitted, $it);
    $listing = JobListing::factory()->forModule($module)->create();
    $assignment = claimableAssignment($module, [$listing->id => 5], group: $it);
    JobListingClaim::factory()->on($assignment, $listing)->by($unsubmitted)->create();
    JobListingClaim::factory()->on($assignment, $listing)->by($submitted)->create();
    Submission::factory()->create(['assignment_id' => $assignment->id, 'user_id' => $submitted->id]);

    $this->actingAs($instructor)->post(route('dashboard.modules.groups.store', $module), [
        'name' => 'Moved',
        'student_ids' => [$unsubmitted->id, $submitted->id],
    ]);

    expect($assignment->claimFor($unsubmitted)->exists())->toBeFalse()
        ->and($assignment->claimFor($submitted)->exists())->toBeTrue();
});

test('removing a student from the module or group releases their claims', function () {
    $instructor = User::factory()->create();
    [$removed, $ungrouped] = User::factory(2)->create();
    $module = Module::factory()->withInstructor($instructor)->withMembers([$removed, $ungrouped])->create();
    $group = ModuleGroup::factory()->forModule($module)->create();
    placeInGroup($ungrouped, $group);
    $listing = JobListing::factory()->forModule($module)->create();
    $moduleWide = claimableAssignment($module, [$listing->id => 5]);
    $grouped = claimableAssignment($module, [$listing->id => 5], group: $group);
    JobListingClaim::factory()->on($moduleWide, $listing)->by($removed)->create();
    JobListingClaim::factory()->on($grouped, $listing)->by($ungrouped)->create();
    JobListingClaim::factory()->on($moduleWide, $listing)->by($ungrouped)->create();

    $this->actingAs($instructor)->delete(route('dashboard.modules.members.destroy', $module), ['user_id' => $removed->id]);
    $this->actingAs($instructor)->delete(route('dashboard.modules.groups.members.destroy', [$module, $group]), ['user_id' => $ungrouped->id]);

    expect($moduleWide->claimFor($removed)->exists())->toBeFalse()
        ->and($grouped->claimFor($ungrouped)->exists())->toBeFalse()
        ->and($moduleWide->claimFor($ungrouped)->exists())->toBeTrue();
});

test('instructors set per-listing capacity, and detaching a listing releases its claims', function () {
    $instructor = User::factory()->create();
    $student = User::factory()->create();
    $module = Module::factory()->withInstructor($instructor)->withMembers($student)->create();
    [$kept, $dropped] = JobListing::factory(2)->forModule($module)->create();
    $payload = [
        'title' => 'Mock interview',
        'description' => '',
        'due_date_enabled' => '0',
        'job_listing_source' => 'module',
        'module_job_listing_scope' => 'selected',
        'assignee_scope' => 'everyone',
        'allow_resubmission' => '0',
        'job_listing_ids' => [$kept->id, $dropped->id],
        'job_listing_capacities' => [$kept->id => '3', $dropped->id => ''],
    ];

    $this->actingAs($instructor)->post(route('dashboard.modules.assignments.store', $module), $payload)
        ->assertSessionHasNoErrors();

    $assignment = $module->assignments()->sole();
    expect($assignment->jobListings->pluck('pivot.capacity', 'id')->all())
        ->toBe([$kept->id => 3, $dropped->id => null]);

    JobListingClaim::factory()->on($assignment, $dropped)->by($student)->create();

    $this->actingAs($instructor)->patch(route('dashboard.modules.assignments.update', [$module, $assignment]), [
        ...$payload,
        'job_listing_ids' => [$kept->id],
    ])->assertSessionHasNoErrors();

    expect($assignment->claims()->exists())->toBeFalse();
});

test('the assignment page shows claim controls to students and claims to instructors', function () {
    $instructor = User::factory()->create();
    $student = User::factory()->create(['last_name' => 'Claimer']);
    $module = Module::factory()->withInstructor($instructor)->withMembers($student)->create();
    $claimed = JobListing::factory()->forModule($module)->create(['name' => 'Backend Intern']);
    $open = JobListing::factory()->forModule($module)->create(['name' => 'Frontend Intern']);
    $assignment = claimableAssignment($module, [$claimed->id => 2, $open->id => null]);
    JobListingClaim::factory()->on($assignment, $claimed)->by($student)->create();

    $this->actingAs($student)
        ->get(route('dashboard.modules.assignments.show', [$module, $assignment]))
        ->assertOk()
        ->assertSee('Your claim')
        ->assertSee("listing_details_{$claimed->id}.showModal()", false)
        ->assertSee('1 / 2 slots taken')
        ->assertSee('Switch to this')
        ->assertSee('Your resume will be evaluated against your claimed listing');

    $this->actingAs($instructor)
        ->get(route('dashboard.modules.assignments.show', [$module, $assignment]))
        ->assertOk()
        ->assertSee('Claimed listing')
        ->assertSee('Backend Intern')
        ->assertDontSee('Switch to this');
});
