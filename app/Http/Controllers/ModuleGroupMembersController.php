<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\ModuleGroup;
use App\Models\ModuleMembership;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;

class ModuleGroupMembersController extends Controller
{
    public function destroy(Module $module, ModuleGroup $group): RedirectResponse
    {
        $validated = request()->validate([
            'user_id' => [
                'required',
                'integer',
                Rule::exists('module_memberships', 'user_id')
                    ->where('module_id', $module->id)
                    ->where('module_group_id', $group->id),
            ],
        ]);

        ModuleMembership::query()
            ->where('module_id', $module->id)
            ->where('module_group_id', $group->id)
            ->where('user_id', $validated['user_id'])
            ->update(['module_group_id' => null]);

        return redirect()
            ->route('dashboard.modules.groups.show', [$module, $group])
            ->with('groupStatus', [
                'message' => 'Student removed from group.',
                'type' => 'success',
            ]);
    }
}
