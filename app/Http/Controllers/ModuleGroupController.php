<?php

namespace App\Http\Controllers;

use App\Enums\ModuleMembershipStatus;
use App\Enums\RoleInModule;
use App\Models\Module;
use App\Models\ModuleGroup;
use App\Models\ModuleMembership;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ModuleGroupController extends Controller
{
    public function index(Module $module): View
    {
        $groups = $module->groups()
            ->withCount('members')
            ->orderBy('name')
            ->get();

        return view('dashboard.modules.groups.index', [
            'module' => $module,
            'groups' => $groups,
        ]);
    }

    public function show(Module $module, ModuleGroup $group): View
    {
        $members = $group->members()
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return view('dashboard.modules.groups.show', [
            'module' => $module,
            'group' => $group,
            'members' => $members,
        ]);
    }

    public function store(Module $module): RedirectResponse
    {
        $validated = request()->validateWithBag('createGroup', [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('module_groups')->where('module_id', $module->id),
            ],
            'student_ids' => ['array'],
            'student_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('module_memberships', 'user_id')
                    ->where('module_id', $module->id)
                    ->where('status', ModuleMembershipStatus::Active->value)
                    ->where('role_in_module', RoleInModule::Student->value),
            ],
        ], [
            'student_ids.*.exists' => 'Only active students of this module can be added to a group.',
        ]);

        DB::transaction(function () use ($module, $validated): void {
            $group = $module->groups()->create(['name' => $validated['name']]);

            if (! empty($validated['student_ids'])) {
                ModuleMembership::query()
                    ->where('module_id', $module->id)
                    ->whereIn('user_id', $validated['student_ids'])
                    ->update(['module_group_id' => $group->id]);
            }
        });

        return redirect()->route('dashboard.modules.groups.index', ['module' => $module]);
    }

    public function update(Module $module, ModuleGroup $group): RedirectResponse
    {
        $validated = request()->validateWithBag("updateGroup{$group->id}", [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('module_groups')->where('module_id', $module->id)->ignore($group),
            ],
        ]);

        $group->update($validated);

        return redirect()->route('dashboard.modules.groups.index', ['module' => $module]);
    }

    public function destroy(Module $module, ModuleGroup $group): RedirectResponse
    {
        $group->delete();

        return redirect()->route('dashboard.modules.groups.index', ['module' => $module]);
    }
}
