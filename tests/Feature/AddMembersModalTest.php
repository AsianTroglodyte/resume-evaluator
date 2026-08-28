<?php

use App\Enums\RoleInModule;
use App\Models\Module;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

uses(RefreshDatabase::class);

it('adds selected users', function () {
    /** @var TestCase $this */
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);
    $module = Module::factory()->create();
    $userArray = User::factory(5)->create()->toArray();

    $userIdArray = array_map(fn ($user) => $user['id'], $userArray);
    $component = Livewire::test('add-members-modal', ['module' => $module]);

    foreach ($userIdArray as $userId) {
        $component->call('selectUser', $userId);
    }

    $component
        ->call('addSelected')
        ->assertHasNoErrors();

    foreach ($userArray as $user) {
        $this->assertDatabaseHas('module_memberships', [
            'module_id' => $module->id,
            'user_id' => $user['id'],
            'role_in_module' => RoleInModule::Student->value,
            'status' => 'active',
            'added_by_user_id' => $admin->id,
        ]);
    }
});

it('adds users from pasted email list', function () {
    /** @var TestCase $this */
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);
    $module = Module::factory()->create();
    $userArray = User::factory(5)->create()->toArray();

    $userEmailArray = array_map(fn ($user) => $user['email'], $userArray);
    Livewire::test('add-members-modal', ['module' => $module])
        ->set('csvString', implode("\n", $userEmailArray))
        ->set('roleInModule', RoleInModule::Student)
        ->call('addFromImport')
        ->assertHasNoErrors();

    expect($module->memberships()->count())->toBe(5);

    foreach ($userArray as $user) {
        $this->assertDatabaseHas('module_memberships', [
            'module_id' => $module->id,
            'user_id' => $user['id'],
            'role_in_module' => RoleInModule::Student->value,
            'status' => 'active',
            'added_by_user_id' => $admin->id,
        ]);
    }
});

it('adds users from email list file', function () {
    /** @var TestCase $this */
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);
    $module = Module::factory()->create();

    $userCollection = User::factory()->count(5)->create();
    $csv = "email\n".$userCollection->pluck('email')->implode("\n");
    $file = UploadedFile::fake()->createWithContent(
        'email-list.csv',
        $csv,
    );

    $userArray = $userCollection->toArray();
    Livewire::test('add-members-modal', ['module' => $module])
        ->set('listSource', 'file')
        ->set('emails_csv_file', $file)
        ->set('roleInModule', RoleInModule::Student)
        ->call('addFromImport')
        ->assertHasNoErrors();

    expect($module->memberships()->count())->toBe(5);

    foreach ($userArray as $user) {
        $this->assertDatabaseHas('module_memberships', [
            'module_id' => $module->id,
            'user_id' => $user['id'],
            'role_in_module' => RoleInModule::Student->value,
            'status' => 'active',
            'added_by_user_id' => $admin->id,
        ]);
    }
});
