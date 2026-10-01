<?php
use App\Enums\ModuleMembershipStatus;
use App\Enums\RoleInModule;
use App\Models\ModuleGroup;
use App\Models\ModuleMembership;
use App\Support\ModuleStudentCandidates;
use App\Support\PicksUsersInModal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

new class extends Component {
    use PicksUsersInModal;

    public ModuleGroup $group;

    public function mount(ModuleGroup $group): void
    {
        $this->group = $group;
    }

    protected function candidateUsers(): Builder
    {
        return (new ModuleStudentCandidates)($this->group->module_id, exceptGroupId: $this->group->id);
    }

    protected function addUsers(array $emails): mixed
    {
        $this->authorize('update', $this->group);

        $emails = $this->validateEmails($emails);

        $memberships = ModuleMembership::query()
            ->where('module_id', $this->group->module_id)
            ->where('status', ModuleMembershipStatus::Active)
            ->where('role_in_module', RoleInModule::Student)
            ->whereHas('user', fn (Builder $query) => $query->whereIn('email', $emails))
            ->with('user:id,email')
            ->get();

        $notStudents = array_diff($emails, $memberships->pluck('user.email')->all());
        if ($notStudents !== []) {
            throw ValidationException::withMessages([
                'emails' => 'Not a student in this module: '.implode(', ', $notStudents),
            ]);
        }

        ModuleMembership::query()
            ->whereKey($memberships->modelKeys())
            ->update(['module_group_id' => $this->group->id]);

        return redirect()
            ->route('dashboard.modules.groups.show', [$this->group->module_id, $this->group])
            ->with('groupStatus', [
                'message' => 'Students added to '.$this->group->name.'.',
                'type' => 'success',
            ]);
    }
};
?>

<div>
    <x-user-picker-modal
        id="add_group_members"
        title="Add students to {{ $group->name }}"
        description="Only students in this module are listed. Students already in another group will be moved."
        trigger-label="Add Students"
        :dialog-is-open="$dialogIsOpen"
        :user-query="$userQuery"
        :query-result="$queryResult"
        :selected-users="$selectedUsers"
        :list-source="$listSource"
    />
</div>
