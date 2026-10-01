<?php
use App\Enums\ModuleMembershipStatus;
use App\Enums\RoleInModule;
use App\Models\Module;
use App\Models\ModuleMembership;
use App\Models\User;
use App\Support\PicksUsers;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

new class extends Component {
    use PicksUsers;

    public Module $module;
    public RoleInModule $roleInModule = RoleInModule::Student;

    public function mount(Module $module): void
    {
        $this->module = $module;
    }

    protected function candidateUsers(): Builder
    {
        return User::query()->whereNotIn('users.id', ModuleMembership::query()
            ->select('user_id')
            ->where('module_id', $this->module->id)
            ->where('status', ModuleMembershipStatus::Active));
    }

    protected function addUsers(array $emails): mixed
    {
        $this->authorize('manageUsers', $this->module);

        $emails = $this->validateEmails($emails);
        $users = User::query()->whereIn('email', $emails)->get();

        $unknownEmails = array_diff($emails, $users->pluck('email')->all());
        if ($unknownEmails !== []) {
            throw ValidationException::withMessages([
                'emails' => 'No account exists for: '.implode(', ', $unknownEmails),
            ]);
        }

        $memberships = ModuleMembership::query()
            ->where('module_id', $this->module->id)
            ->whereIn('user_id', $users->pluck('id'))
            ->get()
            ->keyBy('user_id');

        $alreadyActive = $users->filter(
            fn (User $user) => $memberships->get($user->id)?->status === ModuleMembershipStatus::Active
        );
        if ($alreadyActive->isNotEmpty()) {
            throw ValidationException::withMessages([
                'emails' => 'Already active in this module: '.$alreadyActive->pluck('email')->implode(', '),
            ]);
        }

        DB::transaction(function () use ($users, $memberships): void {
            foreach ($users as $user) {
                $membership = $memberships->get($user->id);

                if ($membership) {
                    $membership->update([
                        'role_in_module' => $this->roleInModule,
                        'status' => ModuleMembershipStatus::Active,
                        'removed_by_user_id' => null,
                        'removed_at' => null,
                        'added_by_user_id' => auth()->id(),
                    ]);
                } else {
                    ModuleMembership::create([
                        'module_id' => $this->module->id,
                        'user_id' => $user->id,
                        'role_in_module' => $this->roleInModule,
                        'status' => ModuleMembershipStatus::Active,
                        'added_by_user_id' => auth()->id(),
                    ]);
                }
            }
        });

        return redirect()
            ->route('dashboard.modules.members.index', $this->module)
            ->with('membershipStatus', [
                'message' => 'Users Added',
                'type' => 'success',
            ]);
    }
};
?>

<div>
    <x-user-picker-modal
        id="add_members"
        title="Add members"
        :dialog-is-open="$dialogIsOpen"
        :user-query="$userQuery"
        :query-result="$queryResult"
        :selected-users="$selectedUsers"
        :list-source="$listSource"
    >
        <x-slot:fields>
            <x-role-in-module-select id="add-members-role" />
        </x-slot:fields>
    </x-user-picker-modal>
</div>
