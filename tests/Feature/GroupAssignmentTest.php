<?php

use App\Enums\AssigneeScope;
use App\Models\Assignment;
use App\Models\JobListing;
use App\Models\Module;
use App\Models\ModuleGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * @return array<string, mixed>
 */
function assignmentPayload(array $overrides = []): array
{
    return [
        'title' => 'Mock interview',
        'description' => '',
        'due_date_enabled' => '0',
        'job_listing_source' => 'external',
        'module_job_listing_scope' => 'all',
        'assignee_scope' => 'everyone',
        'allow_resubmission' => '0',
        ...$overrides,
    ];
}

test('group-scoped assignments are only given to students currently in that group', function () {
    $instructor = User::factory()->create();
    [$itStudent, $csStudent, $ungrouped] = User::factory(3)->create();
    $module = Module::factory()->withInstructor($instructor)->withMembers([$itStudent, $csStudent, $ungrouped])->create();
    $it = ModuleGroup::factory()->forModule($module)->create(['name' => 'IT']);
    $cs = ModuleGroup::factory()->forModule($module)->create(['name' => 'CS']);
    placeInGroup($itStudent, $it);
    placeInGroup($csStudent, $cs);

    $itAssignment = Assignment::factory()->forGroup($it)->create();
    $moduleWide = Assignment::factory()->forModule($module)->create();

    expect($itStudent->can('view', $itAssignment))->toBeTrue()
        ->and($itStudent->can('submit', $itAssignment))->toBeTrue()
        ->and($csStudent->can('view', $itAssignment))->toBeFalse()
        ->and($ungrouped->can('view', $itAssignment))->toBeFalse()
        ->and($instructor->can('view', $itAssignment))->toBeTrue()
        ->and($ungrouped->can('view', $moduleWide))->toBeTrue()
        ->and($csStudent->can('view', $moduleWide))->toBeTrue();

    placeInGroup($itStudent, $cs);

    expect($itStudent->can('view', $itAssignment))->toBeFalse();
});

test('job listings are visible only through assignments the student is given', function () {
    [$itStudent, $csStudent] = User::factory(2)->create();
    $module = Module::factory()->withMembers([$itStudent, $csStudent])->create();
    $it = ModuleGroup::factory()->forModule($module)->create(['name' => 'IT']);
    placeInGroup($itStudent, $it);
    $listing = JobListing::factory()->forModule($module)->create();
    Assignment::factory()->forGroup($it)->withJobListings($listing)->create();

    expect($itStudent->can('view', $listing))->toBeTrue()
        ->and($csStudent->can('view', $listing))->toBeFalse();
});

test('instructors can create a group-scoped assignment, ignoring any selected assignees', function () {
    $instructor = User::factory()->create();
    $student = User::factory()->create();
    $module = Module::factory()->withInstructor($instructor)->withMembers($student)->create();
    $group = ModuleGroup::factory()->forModule($module)->create();

    $this->actingAs($instructor)
        ->post(route('dashboard.modules.assignments.store', $module), assignmentPayload([
            'assignee_scope' => 'group',
            'module_group_id' => $group->id,
            'assignee_ids' => [$student->id],
        ]))
        ->assertRedirect(route('dashboard.modules.show', $module));

    $assignment = $module->assignments()->sole();
    expect($assignment->assignee_scope)->toBe(AssigneeScope::Group)
        ->and($assignment->module_group_id)->toBe($group->id)
        ->and($assignment->allAssignees()->count())->toBe(0);
});

test('group scope requires a group from the same module', function () {
    $instructor = User::factory()->create();
    $module = Module::factory()->withInstructor($instructor)->create();
    $otherModuleGroup = ModuleGroup::factory()->create();

    $this->actingAs($instructor)
        ->post(route('dashboard.modules.assignments.store', $module), assignmentPayload(['assignee_scope' => 'group']))
        ->assertSessionHasErrors('module_group_id');

    $this->actingAs($instructor)
        ->post(route('dashboard.modules.assignments.store', $module), assignmentPayload([
            'assignee_scope' => 'group',
            'module_group_id' => $otherModuleGroup->id,
        ]))
        ->assertSessionHasErrors('module_group_id');

    expect($module->assignments()->count())->toBe(0);
});

test('re-targeting an assignment to everyone clears its group', function () {
    $instructor = User::factory()->create();
    $module = Module::factory()->withInstructor($instructor)->create();
    $group = ModuleGroup::factory()->forModule($module)->create();
    $assignment = Assignment::factory()->forGroup($group)->create();

    $this->actingAs($instructor)
        ->patch(route('dashboard.modules.assignments.update', [$module, $assignment]), assignmentPayload([
            'module_group_id' => $group->id,
        ]))
        ->assertRedirect();

    $assignment->refresh();
    expect($assignment->assignee_scope)->toBe(AssigneeScope::Everyone)
        ->and($assignment->module_group_id)->toBeNull();
});

test('the instructor roster for a group-scoped assignment lists only that group', function () {
    $instructor = User::factory()->create();
    $inGroup = User::factory()->create(['last_name' => 'Ingroup']);
    $outside = User::factory()->create(['last_name' => 'Outsider']);
    $module = Module::factory()->withInstructor($instructor)->withMembers([$inGroup, $outside])->create();
    $group = ModuleGroup::factory()->forModule($module)->create(['name' => 'IT']);
    placeInGroup($inGroup, $group);
    $assignment = Assignment::factory()->forGroup($group)->create();

    $this->actingAs($instructor)
        ->get(route('dashboard.modules.assignments.show', [$module, $assignment]))
        ->assertOk()
        ->assertSee('Ingroup')
        ->assertDontSee('Outsider');
});

test('assignment create and edit pages offer the module groups', function () {
    $instructor = User::factory()->create();
    $module = Module::factory()->withInstructor($instructor)->create();
    $group = ModuleGroup::factory()->forModule($module)->create(['name' => 'Computer Science']);
    $assignment = Assignment::factory()->forGroup($group)->create();

    $this->actingAs($instructor)
        ->get(route('dashboard.modules.assignments.create', $module))
        ->assertOk()
        ->assertSee('Computer Science');

    $this->actingAs($instructor)
        ->get(route('dashboard.modules.assignments.edit', [$module, $assignment]))
        ->assertOk()
        ->assertSee('Computer Science');
});

test('a group targeted by an assignment cannot be deleted', function () {
    $instructor = User::factory()->create();
    $module = Module::factory()->withInstructor($instructor)->create();
    $group = ModuleGroup::factory()->forModule($module)->create();
    Assignment::factory()->forGroup($group)->create();

    $this->actingAs($instructor)
        ->delete(route('dashboard.modules.groups.destroy', [$module, $group]))
        ->assertSessionHasErrorsIn("deleteGroup{$group->id}", 'group');

    $this->assertModelExists($group);
});
