<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Livewire\WithFileUploads;

/**
 * Shared state and actions for the "add users" modals: search-and-select plus
 * pasted / uploaded email lists. Pair with the `<x-user-picker-modal>` view.
 */
trait PicksUsers
{
    use WithFileUploads;

    public string $userQuery = '';

    public bool $dialogIsOpen = false;

    /** @var list<array{id: int, first_name: string, last_name: string, email: string, picker_note: ?string}> */
    public array $selectedUsers = [];

    public string $csvString = '';

    public $emails_csv_file;

    public string $listSource = 'paste';

    /**
     * Users who may be offered in search and selected.
     *
     * @return Builder<User>
     */
    abstract protected function candidateUsers(): Builder;

    /**
     * Validate and persist the given emails; return a redirect on success.
     *
     * @param  list<string>  $emails
     */
    abstract protected function addUsers(array $emails): mixed;

    public function with(): array
    {
        $selectedIds = collect($this->selectedUsers)->pluck('id')->all();

        $queryResult = filled($this->userQuery)
            ? $this->candidateUsers()
                ->addSelect('users.*')
                ->whereRaw(
                    "CONCAT(users.first_name, ' ', users.last_name, '; ', users.email) LIKE ?",
                    ['%'.$this->userQuery.'%']
                )
                ->when($selectedIds !== [], fn (Builder $query) => $query->whereNotIn('users.id', $selectedIds))
                ->orderBy('users.last_name')
                ->orderBy('users.first_name')
                ->limit(101)
                ->get()
            : collect();

        return [
            'queryResult' => $queryResult,
        ];
    }

    public function selectUser(int $id): void
    {
        if (collect($this->selectedUsers)->contains('id', $id)) {
            return;
        }

        $user = $this->candidateUsers()->addSelect('users.*')->find($id);

        if ($user) {
            $this->selectedUsers[] = [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'picker_note' => $user->picker_note,
            ];
        }
    }

    public function deselectUser(int $id): void
    {
        $this->selectedUsers = array_values(array_filter(
            $this->selectedUsers,
            fn (array $selectedUser) => $selectedUser['id'] !== $id,
        ));
    }

    public function addSelected(): mixed
    {
        if ($this->selectedUsers === []) {
            throw ValidationException::withMessages([
                'no_selected_users' => 'You did not select any users.',
            ]);
        }

        return $this->addUsers(array_column($this->selectedUsers, 'email'));
    }

    public function addFromImport(): mixed
    {
        if ($this->listSource === 'file') {
            Validator::make(['emails_csv_file' => $this->emails_csv_file], [
                'emails_csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:1024'],
            ])->validate();

            $stream = fopen($this->emails_csv_file->getRealPath(), 'rb');
        } else {
            $stream = fopen('php://memory', 'r+');
            fwrite($stream, $this->csvString);
            rewind($stream);
        }

        return $this->addUsers((new ParseEmailList)($stream));
    }

    public function toggleDialogIsOpen(): void
    {
        $this->dialogIsOpen = ! $this->dialogIsOpen;
    }

    public function cancel(): void
    {
        $this->toggleDialogIsOpen();
        $this->userQuery = '';
        $this->selectedUsers = [];
        $this->resetErrorBag();
    }

    /**
     * Base email validation shared by every picker.
     *
     * @param  list<string>  $emails
     * @return list<string>
     */
    protected function validateEmails(array $emails): array
    {
        return Validator::make(['emails' => $emails], [
            'emails' => ['required', 'array', 'min:1'],
            'emails.*' => ['required', 'email', 'distinct'],
        ])->validate()['emails'];
    }
}
