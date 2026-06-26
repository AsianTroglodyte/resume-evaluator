<?php

namespace App\Http\Controllers;

use App\Enums\RoleInModule;
use App\Models\Module;
use App\Models\ModuleMembership;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ModuleMembersController extends Controller
{
    //
    public function index(Module $module)
    {
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

    public function store(Module $module)
    {
        // dd(request('new_member_email'));

        $validated = request()->validate([
            'role_in_module' => [
                'required',
                Rule::enum(RoleInModule::class),
            ],
            'new_member_email' => [
                'required',
                'email',
                Rule::exists('users', 'email'),
            ],
        ]);

        $newUser = User::where('email', request('new_member_email'))->firstOrFail();

        // check if new potential user was previously a member
        // if was, then we get the membership record. else null
        $moduleMembership = ModuleMembership::where('module_id', $module->id)
            ->where('user_id', $newUser->id)
            ->first();

        // if never was a member we create the membership
        if ($moduleMembership) {
            if ($moduleMembership->status === 'active') {
                throw ValidationException::withMessages([
                    'new_member_email' => 'This user is already an active member of the module.',
                ]);
            }

            $moduleMembership->update([
                'role_in_module' => RoleInModule::from($validated['role_in_module']),
                'status' => 'active',
                'removed_by_user_id' => null,
                'removed_at' => null,
                'added_by_user_id' => auth()->id(),
            ]);
        } else {
            ModuleMembership::create([
                'module_id' => $module->id,
                'user_id' => $newUser->id,
                'role_in_module' => RoleInModule::tryFrom(request('role_in_module')),
                'status' => 'active',
                'added_by_user_id' => auth()->id(),
                'removed_by_user_id' => null,
            ]);
        }

        $members = $module->users()
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return redirect()->route('dashboard.modules.members.index', [
            'module' => $module,
            'members' => $members,
        ]);
    }

    public function destroy(Module $module)
    {
        $validated = request()->validate([
            'user_id' => [
                'required',
                'integer',
                Rule::exists('module_memberships', 'user_id')
                    ->where('module_id', $module->id),
            ],
        ]);

        ModuleMembership::where('module_id', $module->id)
            ->where('user_id', $validated['user_id'])
            ->update([
                'status' => 'removed',
                'removed_by_user_id' => auth()->id(),
                'removed_at' => now(),
            ]);

        return redirect()->route('dashboard.modules.members.index', $module);
    }
}
