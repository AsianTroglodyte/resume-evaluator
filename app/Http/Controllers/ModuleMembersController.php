<?php

namespace App\Http\Controllers;

use App\Enums\RoleInModule;
use App\Models\Module;
use App\Models\ModuleMembership;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ModuleMembersController extends Controller
{
    //
    public function index(Module $module) {
        $members = $module
        ->users()
        ->orderBy('last_name')
        ->orderBy('first_name')
        ->get();

        return view('dashboard.modules.members.index', [
            'module' => $module,
            'members' => $members,
        ]);
    }

    public function store(Module $module) {
        // dd(request('new_member_email'));

        request()->validate([
            'role_in_module' => [
                'required',
                Rule::enum(RoleInModule::class)
            ],
            'new_member_email' => [
                'required',
                'email',
                Rule::exists('users', 'email')->where(function ($query) use ($module) {
                    $query->whereNotIn('id', function ($query) use ($module) {
                        $query->select('user_id')
                            ->from('module_memberships')
                            ->where('module_id', $module->id);
                    });
                }),
            ]
        ]);

        $new_user = User::where('email', request('new_member_email'))->firstOrFail();

        ModuleMembership::create([
            'module_id' => $module->id,
            'user_id' => $new_user->id,
            'role_in_module' => RoleInModule::tryFrom(request('role_in_module')),
            'status' =>  "active",
            'added_by_user_id' => 1,
            'removed_by_user_id' => null,
            ]);

        $members = $module->users()
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return view('dashboard.modules.members.index', [
            'module' => $module,
            'members' => $members,
        ]);
    }
}
