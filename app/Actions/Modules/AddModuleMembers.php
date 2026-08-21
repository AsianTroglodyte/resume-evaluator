<?php

namespace App\Actions\Modules;

use App\Enums\RoleInModule;
use App\Models\Module;

class AddModuleMembers
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function handle(
        Module $module, 
        array $email, 
        RoleInModule $role, 
        $user)
    {

    }
}
