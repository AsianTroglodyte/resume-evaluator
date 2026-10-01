<?php

use App\Models\Module;
use App\Models\ModuleGroup;
use App\Models\ModuleMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Tests\TestCase;

uses(RefreshDatabase::class);

beforeEach(function () {
    /** @var TestCase $this */
    $this->actingAs(User::factory()->admin()->create());
});

it('renders selected users as hidden inputs without persisting anything', function () {
    $user = User::factory()->create();

    Livewire::test('user-picker-field', ['name' => 'student_ids'])
        ->call('selectUser', $user->id)
        ->assertSeeHtml('name="student_ids[]" value="'.$user->id.'"');

    expect(ModuleMembership::count())->toBe(0);
});

it('removes a deselected user from the submitted value', function () {
    $user = User::factory()->create();

    Livewire::test('user-picker-field', ['name' => 'student_ids'])
        ->call('selectUser', $user->id)
        ->call('deselectUser', $user->id)
        ->assertDontSeeHtml('name="student_ids[]"');
});

it('only offers and accepts active students when scoped to a module', function () {
    $student = User::factory()->create();
    $instructor = User::factory()->create();
    $outsider = User::factory()->create();
    $module = Module::factory()->withMembers($student)->withInstructor($instructor)->create();

    $component = Livewire::test('user-picker-field', ['name' => 'student_ids', 'module' => $module])
        ->set('userQuery', '@');

    expect($component->viewData('queryResult')->pluck('id')->all())->toBe([$student->id]);

    $component
        ->call('selectUser', $outsider->id)
        ->call('selectUser', $instructor->id)
        ->assertSet('selectedUsers', []);
});

it('labels students with their current group', function () {
    $student = User::factory()->create();
    $module = Module::factory()->withMembers($student)->create();
    $group = ModuleGroup::factory()->forModule($module)->create(['name' => 'CS']);
    ModuleMembership::where('module_id', $module->id)->where('user_id', $student->id)
        ->update(['module_group_id' => $group->id]);

    Livewire::test('user-picker-field', ['name' => 'student_ids', 'module' => $module])
        ->call('selectUser', $student->id)
        ->assertSet('selectedUsers.0.picker_note', 'CS');
});

it('adds imported emails to the selection', function () {
    $users = User::factory(2)->create();

    Livewire::test('user-picker-field', ['name' => 'instructor_ids'])
        ->set('csvString', $users->pluck('email')->implode("\n"))
        ->call('addFromImport')
        ->assertHasNoErrors()
        ->assertSet('csvString', '')
        ->assertCount('selectedUsers', 2);

    expect(ModuleMembership::count())->toBe(0);
});

it('rejects an import containing someone outside the candidate pool', function () {
    $student = User::factory()->create();
    $outsider = User::factory()->create();
    $module = Module::factory()->withMembers($student)->create();

    Livewire::test('user-picker-field', ['name' => 'student_ids', 'module' => $module])
        ->set('csvString', $student->email."\n".$outsider->email)
        ->call('addFromImport')
        ->assertHasErrors('emails')
        ->assertSet('selectedUsers', []);
});

it('restores its selection from old input after a failed submit', function () {
    /** @var TestCase $this */
    $user = User::factory()->create();

    $this->withSession(['_old_input' => ['name' => 'x', 'student_ids' => [(string) $user->id]]])
        ->get(route('dashboard.modules.create'))
        ->assertOk()
        ->assertSeeHtml('name="student_ids[]" value="'.$user->id.'"')
        ->assertDontSeeHtml('name="instructor_ids[]"');
});

it('does not let the client change the field name', function () {
    Livewire::test('user-picker-field', ['name' => 'student_ids'])
        ->set('name', 'instructor_ids');
})->throws(CannotUpdateLockedPropertyException::class);
