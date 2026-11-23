<?php
use App\Models\Module;
use App\Models\User;
use App\Support\ModuleStudentCandidates;
use App\Support\PicksUsers;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Select-only user picker for use inside a regular form. The selection is
 * submitted as hidden `{name}[]` inputs; nothing is persisted here.
 */
new class extends Component {
    use PicksUsers;

    #[Locked]
    public string $name;

    /** When set, only the module's active students can be picked. */
    #[Locked]
    public ?Module $module = null;

    public function mount(string $name, ?Module $module = null): void
    {
        $this->name = $name;
        $this->module = $module?->exists ? $module : null;

        foreach ((array) old($name, []) as $id) {
            if (is_numeric($id)) {
                $this->selectUser((int) $id);
            }
        }
    }

    protected function candidateUsers(): Builder
    {
        return $this->module
            ? (new ModuleStudentCandidates)($this->module->id)
            : User::query();
    }

    protected function importEmails(array $emails): mixed
    {
        $emails = $this->validateEmails($emails);

        $users = $this->candidateUsers()
            ->addSelect('users.*')
            ->whereIn('users.email', $emails)
            ->get();

        $unavailable = array_diff($emails, $users->pluck('email')->all());
        if ($unavailable !== []) {
            throw ValidationException::withMessages([
                'emails' => ($this->module ? 'Not a student in this module: ' : 'No account exists for: ')
                    .implode(', ', $unavailable),
            ]);
        }

        $users->each(fn (User $user) => $this->pushSelectedUser($user));
        $this->reset('csvString', 'emails_csv_file');

        return null;
    }
};
?>

<div>
    @foreach ($selectedUsers as $selectedUser)
        <input type="hidden" name="{{ $name }}[]" value="{{ $selectedUser['id'] }}" wire:key="{{ $name }}-value-{{ $selectedUser['id'] }}">
    @endforeach

    <x-user-picker
        :id="'picker_'.$name"
        :user-query="$userQuery"
        :query-result="$queryResult"
        :selected-users="$selectedUsers"
        :list-source="$listSource"
        import-label="Add to selection"
    />
</div>
