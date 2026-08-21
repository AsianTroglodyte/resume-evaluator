<?php

use App\Enums\RoleInModule;
use App\Models\Module;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

uses(RefreshDatabase::class);


it('adds selected users', function () {
    /** @var TestCase $this */
    $admin = User::factory()->admin();
    $module = Module::factory()->create();
    $userArray = User::factory(5)->create()->toArray();
    
    $user_id_array = array_map(fn ($user) => $user["id"], $userArray);
    $component = Livewire::test('add-members-modal', ['module' => $module]);

    // dump([
    //     "user_id_array: " => $user_id_array,
    //     "module: " => $module,
    //     "users: " => $users,
    //     "component" => $component
    //     ]);
    foreach ($user_id_array as $user_id) {
        $component->call('selectUser', $user_id);
    }

    $component->call('addSelected');

    $user_email_array = array_map(fn ($user) => $user["email"], $userArray);
    dump($user_email_array);
    // $this->assertDatabaseHas('users', [
    //     'email' => '',
    // ]);
});

it('adds users from pasted email list', function () {

});

it('adds users from email list file', function () {

});

// function printer(array $thingsToPrint) {
//     foreach ($thingsToPrint as $key => $thingToPrint) {
//         dump("$key: $thingToPrint", );
//     }
// }