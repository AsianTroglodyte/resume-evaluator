<?php

use App\Models\Module;
use App\Models\ModuleGroup;
use App\Models\ModuleMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

uses(RefreshDatabase::class);

function groupIdOf(User $user, Module $module): ?int
{
    return ModuleMembership::query()
        ->where('module_id', $module->id)
        ->where('user_id', $user->id)
        ->value('module_group_id');
}

it('adds selected students to the group', function () {
    /** @var TestCase $this */
    $instructor = User::factory()->create();
    $students = User::factory(3)->create();
    $module = Module::factory()->withInstructor($instructor)->withMembers($students)->create();
    $group = ModuleGroup::factory()->forModule($module)->create();
    $this->actingAs($instructor);

    $component = Livewire::test('add-group-members-modal', ['group' => $group]);
    foreach ($students as $student) {
        $component->call('selectUser', $student->id);
    }

    $component
        ->call('addSelected')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard.modules.groups.show', [$module, $group]));

    foreach ($students as $student) {
        expect(groupIdOf($student, $module))->toBe($group->id);
    }
});

it('moves a student who is already in another group', function () {
    /** @var TestCase $this */
    $instructor = User::factory()->create();
    $student = User::factory()->create();
    $module = Module::factory()->withInstructor($instructor)->withMembers($student)->create();
    $fromGroup = ModuleGroup::factory()->forModule($module)->create(['name' => 'IT']);
    $toGroup = ModuleGroup::factory()->forModule($module)->create(['name' => 'CS']);
    placeInGroup($student, $fromGroup);
    $this->actingAs($instructor);

    Livewire::test('add-group-members-modal', ['group' => $toGroup])
        ->call('selectUser', $student->id)
        ->assertSet('selectedUsers.0.picker_note', 'IT')
        ->call('addSelected')
        ->assertHasNoErrors();

    expect(groupIdOf($student, $module))->toBe($toGroup->id);
});

it('only offers active students of the module who are not already in the group', function () {
    /** @var TestCase $this */
    $instructor = User::factory()->create(['first_name' => 'Ivy']);
    $ungrouped = User::factory()->create(['first_name' => 'Uma']);
    $inGroup = User::factory()->create(['first_name' => 'Gus']);
    $outsider = User::factory()->create(['first_name' => 'Otto']);
    $module = Module::factory()->withInstructor($instructor)->withMembers([$ungrouped, $inGroup])->create();
    $group = ModuleGroup::factory()->forModule($module)->create();
    placeInGroup($inGroup, $group);
    $this->actingAs($instructor);

    $component = Livewire::test('add-group-members-modal', ['group' => $group])
        ->set('userQuery', '@');

    $offeredIds = $component->viewData('queryResult')->pluck('id')->all();
    expect($offeredIds)->toBe([$ungrouped->id]);

    $component->call('selectUser', $outsider->id)
        ->call('selectUser', $instructor->id)
        ->assertSet('selectedUsers', []);
});

it('rejects an import containing someone who is not a student in the module', function () {
    /** @var TestCase $this */
    $instructor = User::factory()->create();
    $student = User::factory()->create();
    $outsider = User::factory()->create();
    $module = Module::factory()->withInstructor($instructor)->withMembers($student)->create();
    $group = ModuleGroup::factory()->forModule($module)->create();
    $this->actingAs($instructor);

    Livewire::test('add-group-members-modal', ['group' => $group])
        ->set('csvString', $student->email."\n".$outsider->email)
        ->call('addFromImport')
        ->assertHasErrors('emails');

    expect(groupIdOf($student, $module))->toBeNull();
});

it('adds students from a pasted email list', function () {
    /** @var TestCase $this */
    $instructor = User::factory()->create();
    $students = User::factory(2)->create();
    $module = Module::factory()->withInstructor($instructor)->withMembers($students)->create();
    $group = ModuleGroup::factory()->forModule($module)->create();
    $this->actingAs($instructor);

    Livewire::test('add-group-members-modal', ['group' => $group])
        ->set('csvString', $students->pluck('email')->implode("\n"))
        ->call('addFromImport')
        ->assertHasNoErrors();

    expect($group->members()->count())->toBe(2);
});

it('forbids students from adding group members', function () {
    /** @var TestCase $this */
    $student = User::factory()->create();
    $module = Module::factory()->withMembers($student)->create();
    $group = ModuleGroup::factory()->forModule($module)->create();
    $this->actingAs($student);

    Livewire::test('add-group-members-modal', ['group' => $group])
        ->set('csvString', $student->email)
        ->call('addFromImport')
        ->assertForbidden();

    expect(groupIdOf($student, $module))->toBeNull();
});
