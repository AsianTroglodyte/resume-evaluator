<?php

namespace App\Http\Controllers;

use App\Enums\ModuleMembershipStatus;
use App\Enums\RoleInModule;
use App\Models\JobListing;
use App\Models\Module;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ModuleController extends Controller
{
    //
    public function index()
    {
        $user = request()->user();

        $modules = $user->isGlobalAdmin()
            ? Module::query()->orderBy('name')->get()
            : $user->modulesPartOf()->get();

        return view('dashboard.modules.index', [
            'modules' => $modules,
        ]);
    }

    public function create()
    {

        return view('dashboard.modules.create', []);
    }

    public function show(Module $module)
    {
        $jobListings = $module->jobListings->filter(
            fn (JobListing $jobListing): bool => Gate::allows('view', $jobListing)
        );

        $assignments = $module
            ->assignments()
            ->with('assignees', 'jobListings')
            ->get();

        return view('dashboard.modules.show', [
            'jobListings' => $jobListings,
            'module' => $module,
            'assignments' => $assignments,
        ]);
    }

    public function store()
    {
        $validated = request()->validate([
            'name' => ['required', 'min:3'],
            'instructor_ids' => ['array'],
            'instructor_ids.*' => ['integer', 'distinct', Rule::exists('users', 'id')],
            'student_ids' => ['array'],
            'student_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('users', 'id'),
                Rule::notIn(array_filter((array) request('instructor_ids', []), 'is_scalar')),
            ],
        ], [
            'student_ids.*.not_in' => 'A user cannot be both an instructor and a student.',
        ]);

        $module = DB::transaction(function () use ($validated): Module {
            $module = Module::create([
                'name' => $validated['name'],
                'created_by_user_id' => auth()->id(),
            ]);

            $roles = [
                RoleInModule::Instructor->value => $validated['instructor_ids'] ?? [],
                RoleInModule::Student->value => $validated['student_ids'] ?? [],
            ];

            foreach ($roles as $role => $userIds) {
                foreach ($userIds as $userId) {
                    $module->memberships()->create([
                        'user_id' => $userId,
                        'role_in_module' => $role,
                        'status' => ModuleMembershipStatus::Active,
                        'added_by_user_id' => auth()->id(),
                    ]);
                }
            }

            return $module;
        });

        return redirect()->route('dashboard.modules.show', $module);
    }

    public function destroy(Module $module)
    {
        $module->delete();

        return redirect()->route('dashboard.modules.index');
    }
}
