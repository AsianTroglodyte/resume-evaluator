<?php

use App\Models\Module;
use App\Models\ModuleGroup;
use App\Models\ModuleMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('instructors and global admins can view the groups page', function () {
    $instructor = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $module = Module::factory()->withInstructor($instructor)->create();
    ModuleGroup::factory()->forModule($module)->create(['name' => 'Computer Science']);

    $this->actingAs($instructor)
        ->get(route('dashboard.modules.groups.index', $module))
        ->assertOk()
        ->assertSee('Computer Science');

    $this->actingAs($admin)
        ->get(route('dashboard.modules.groups.index', $module))
        ->assertOk()
        ->assertSee('Computer Science');
});

test('instructors can create, rename, and delete groups', function () {
    $instructor = User::factory()->create();
    $module = Module::factory()->withInstructor($instructor)->create();

    $this->actingAs($instructor)
        ->post(route('dashboard.modules.groups.store', $module), ['name' => 'IT'])
        ->assertRedirect(route('dashboard.modules.groups.index', $module));

    $group = $module->groups()->sole();
    expect($group->name)->toBe('IT');

    $this->actingAs($instructor)
        ->patch(route('dashboard.modules.groups.update', [$module, $group]), ['name' => 'Information Technology'])
        ->assertRedirect(route('dashboard.modules.groups.index', $module));

    expect($group->fresh()->name)->toBe('Information Technology');

    $this->actingAs($instructor)
        ->delete(route('dashboard.modules.groups.destroy', [$module, $group]))
        ->assertRedirect(route('dashboard.modules.groups.index', $module));

    $this->assertModelMissing($group);
});

test('global admins can create groups in any module', function () {
    $admin = User::factory()->admin()->create();
    $module = Module::factory()->create();

    $this->actingAs($admin)
        ->post(route('dashboard.modules.groups.store', $module), ['name' => 'CS'])
        ->assertRedirect(route('dashboard.modules.groups.index', $module));

    expect($module->groups()->pluck('name')->all())->toBe(['CS']);
});

test('students and outsiders cannot view or manage groups', function () {
    $student = User::factory()->create();
    $outsider = User::factory()->create();
    $module = Module::factory()->withMembers($student)->create();
    $group = ModuleGroup::factory()->forModule($module)->create();

    foreach ([$student, $outsider] as $user) {
        $this->actingAs($user)
            ->get(route('dashboard.modules.groups.index', $module))
            ->assertForbidden();
        $this->actingAs($user)
            ->post(route('dashboard.modules.groups.store', $module), ['name' => 'Sneaky'])
            ->assertForbidden();
        $this->actingAs($user)
            ->patch(route('dashboard.modules.groups.update', [$module, $group]), ['name' => 'Sneaky'])
            ->assertForbidden();
        $this->actingAs($user)
            ->delete(route('dashboard.modules.groups.destroy', [$module, $group]))
            ->assertForbidden();
    }

    expect($module->groups()->count())->toBe(1);
    expect($group->fresh()->name)->not->toBe('Sneaky');
});

test('a group from another module is not found', function () {
    $instructor = User::factory()->create();
    $module = Module::factory()->withInstructor($instructor)->create();
    $otherGroup = ModuleGroup::factory()->create();

    $this->actingAs($instructor)
        ->patch(route('dashboard.modules.groups.update', [$module, $otherGroup]), ['name' => 'Renamed'])
        ->assertNotFound();
    $this->actingAs($instructor)
        ->delete(route('dashboard.modules.groups.destroy', [$module, $otherGroup]))
        ->assertNotFound();

    $this->assertModelExists($otherGroup);
});

test('group names must be unique within a module but not across modules', function () {
    $instructor = User::factory()->create();
    $module = Module::factory()->withInstructor($instructor)->create();
    ModuleGroup::factory()->forModule($module)->create(['name' => 'CS']);
    ModuleGroup::factory()->create(['name' => 'IT']);

    $this->actingAs($instructor)
        ->post(route('dashboard.modules.groups.store', $module), ['name' => 'CS'])
        ->assertSessionHasErrors('name', null, 'createGroup');

    $this->actingAs($instructor)
        ->post(route('dashboard.modules.groups.store', $module), ['name' => 'IT'])
        ->assertSessionHasNoErrors();

    expect($module->groups()->count())->toBe(2);
});

test('renaming a group to its current name is allowed', function () {
    $instructor = User::factory()->create();
    $module = Module::factory()->withInstructor($instructor)->create();
    $group = ModuleGroup::factory()->forModule($module)->create(['name' => 'CS']);

    $this->actingAs($instructor)
        ->patch(route('dashboard.modules.groups.update', [$module, $group]), ['name' => 'CS'])
        ->assertSessionHasNoErrors();
});

test('instructors can view a group and remove a student from it', function () {
    $instructor = User::factory()->create();
    $student = User::factory()->create(['first_name' => 'Grace']);
    $module = Module::factory()->withInstructor($instructor)->withMembers($student)->create();
    $group = ModuleGroup::factory()->forModule($module)->create();
    $membership = ModuleMembership::where('module_id', $module->id)->where('user_id', $student->id)->sole();
    $membership->update(['module_group_id' => $group->id]);

    $this->actingAs($instructor)
        ->get(route('dashboard.modules.groups.show', [$module, $group]))
        ->assertOk()
        ->assertSee('Grace');

    $this->actingAs($instructor)
        ->delete(route('dashboard.modules.groups.members.destroy', [$module, $group]), ['user_id' => $student->id])
        ->assertRedirect(route('dashboard.modules.groups.show', [$module, $group]));

    expect($membership->fresh()->module_group_id)->toBeNull();
});

test('students cannot view a group or remove its members', function () {
    $student = User::factory()->create();
    $module = Module::factory()->withMembers($student)->create();
    $group = ModuleGroup::factory()->forModule($module)->create();
    $membership = ModuleMembership::where('module_id', $module->id)->where('user_id', $student->id)->sole();
    $membership->update(['module_group_id' => $group->id]);

    $this->actingAs($student)
        ->get(route('dashboard.modules.groups.show', [$module, $group]))
        ->assertForbidden();
    $this->actingAs($student)
        ->delete(route('dashboard.modules.groups.members.destroy', [$module, $group]), ['user_id' => $student->id])
        ->assertForbidden();

    expect($membership->fresh()->module_group_id)->toBe($group->id);
});

test('removing a student from the module also ungroups them', function () {
    $instructor = User::factory()->create();
    $student = User::factory()->create();
    $module = Module::factory()->withInstructor($instructor)->withMembers($student)->create();
    $group = ModuleGroup::factory()->forModule($module)->create();
    $membership = ModuleMembership::where('module_id', $module->id)->where('user_id', $student->id)->sole();
    $membership->update(['module_group_id' => $group->id]);

    $this->actingAs($instructor)
        ->delete(route('dashboard.modules.members.destroy', $module), ['user_id' => $student->id]);

    expect($membership->fresh()->module_group_id)->toBeNull();
});

test('deleting a group leaves its students in the module, ungrouped', function () {
    $instructor = User::factory()->create();
    $student = User::factory()->create();
    $module = Module::factory()->withInstructor($instructor)->withMembers($student)->create();
    $group = ModuleGroup::factory()->forModule($module)->create();

    $membership = ModuleMembership::where('module_id', $module->id)->where('user_id', $student->id)->sole();
    $membership->update(['module_group_id' => $group->id]);

    $this->actingAs($instructor)
        ->delete(route('dashboard.modules.groups.destroy', [$module, $group]));

    expect($membership->fresh())
        ->not->toBeNull()
        ->module_group_id->toBeNull();
});
