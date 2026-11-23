<?php

namespace App\Support;

use App\Enums\ModuleMembershipStatus;
use App\Enums\RoleInModule;
use App\Models\ModuleGroup;
use App\Models\ModuleMembership;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class ModuleStudentCandidates
{
    /**
     * Active students of the module, optionally excluding one group's members.
     * `picker_note` carries the name of the student's current group, if any.
     *
     * @return Builder<User>
     */
    public function __invoke(int $moduleId, ?int $exceptGroupId = null): Builder
    {
        return User::query()
            ->whereIn('users.id', ModuleMembership::query()
                ->select('user_id')
                ->where('module_id', $moduleId)
                ->where('status', ModuleMembershipStatus::Active)
                ->where('role_in_module', RoleInModule::Student)
                ->when($exceptGroupId !== null, fn ($query) => $query->where(fn ($query) => $query
                    ->whereNull('module_group_id')
                    ->orWhere('module_group_id', '!=', $exceptGroupId))))
            ->addSelect(['picker_note' => ModuleGroup::query()
                ->select('module_groups.name')
                ->join('module_memberships', 'module_memberships.module_group_id', '=', 'module_groups.id')
                ->whereColumn('module_memberships.user_id', 'users.id')
                ->where('module_groups.module_id', $moduleId)
                ->limit(1)]);
    }
}
