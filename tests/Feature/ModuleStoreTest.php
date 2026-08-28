<?php

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
