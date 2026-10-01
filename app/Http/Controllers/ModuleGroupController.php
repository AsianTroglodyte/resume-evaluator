<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\ModuleGroup;
use Illuminate\Http\RedirectResponse;
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
        ]);

        $module->groups()->create($validated);

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
