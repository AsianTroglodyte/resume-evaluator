<?php

use App\Enums\RoleInModule;
use App\Models\Module;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('rejects selected users with an id that does not exist', function () {
    $module = Module::factory()->create();

    Livewire::test('add-members-modal', ['module' => $module])
        ->set('selectedUsers', [[
            'id' => -100,
            'email' => 'nobody@example.com',
            'first_name' => 'X',
            'last_name' => 'Y',
        ]])
        ->set('roleInModule', RoleInModule::Student)
        ->call('addSelected')
        ->assertHasErrors(['selectedUsers.0.id']);
});
