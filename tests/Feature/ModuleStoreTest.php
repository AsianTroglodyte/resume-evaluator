<?php

use App\Enums\RoleInModule;
use App\Models\Module;
use App\Models\ModuleMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('provisions a module without adding creator membership', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('dashboard.modules.store'), [
            'name' => 'Resume Workshop 2026',
        ])
        ->assertRedirect();

    $module = Module::query()->where('name', 'Resume Workshop 2026')->sole();

    expect($module->created_by_user_id)->toBe($admin->id);

    expect(ModuleMembership::query()
        ->where('module_id', $module->id)
        ->where('user_id', $admin->id)
        ->exists())->toBeFalse();
});

it('redirects to the new module show page after provisioning', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('dashboard.modules.store'), [
            'name' => 'New Module',
        ])
        ->assertRedirect(route('dashboard.modules.show', Module::query()->where('name', 'New Module')->sole()));
});

it('adds the chosen instructors and students when provisioning', function () {
    $admin = User::factory()->admin()->create();
    $instructor = User::factory()->create();
    $students = User::factory(2)->create();

    $this->actingAs($admin)
        ->post(route('dashboard.modules.store'), [
            'name' => 'Senior Seminar',
            'instructor_ids' => [$instructor->id],
            'student_ids' => $students->pluck('id')->all(),
        ])
        ->assertSessionHasNoErrors();

    $module = Module::query()->where('name', 'Senior Seminar')->sole();

    expect($module->instructors()->pluck('users.id')->all())->toBe([$instructor->id]);
    expect($module->assignableMembers()->pluck('users.id')->sort()->values()->all())
        ->toBe($students->pluck('id')->sort()->values()->all());
    expect($module->memberships()->where('added_by_user_id', $admin->id)->count())->toBe(3);
});

it('rejects a user chosen as both instructor and student and creates nothing', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $this->actingAs($admin)
        ->post(route('dashboard.modules.store'), [
            'name' => 'Overlap Module',
            'instructor_ids' => [$user->id],
            'student_ids' => [$user->id],
        ])
        ->assertSessionHasErrors('student_ids.0');

    expect(Module::query()->where('name', 'Overlap Module')->exists())->toBeFalse();
});

it('rejects unknown user ids and creates nothing', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('dashboard.modules.store'), [
            'name' => 'Ghost Module',
            'student_ids' => [999999],
        ])
        ->assertSessionHasErrors('student_ids.0');

    expect(Module::query()->where('name', 'Ghost Module')->exists())->toBeFalse();
    expect(ModuleMembership::query()->where('role_in_module', RoleInModule::Student)->count())->toBe(0);
});

it('lists all modules for global admins on the index', function () {
    $admin = User::factory()->admin()->create();
    $provisionedModule = Module::factory()->createdBy($admin)->create(['name' => 'Provisioned Module']);

    $this->actingAs($admin)
        ->get(route('dashboard.modules.index'))
        ->assertOk()
        ->assertSee('Provisioned Module');

    expect($admin->modulesPartOf)->toHaveCount(0);
    expect($provisionedModule->memberships)->toHaveCount(0);
});
