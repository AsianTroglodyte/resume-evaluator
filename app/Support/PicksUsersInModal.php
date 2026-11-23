<?php

namespace App\Support;

use Illuminate\Validation\ValidationException;

/**
 * A user picker in a dialog that persists the chosen users immediately.
 * Pair with the `<x-user-picker-modal>` view.
 */
trait PicksUsersInModal
{
    use PicksUsers;

    public bool $dialogIsOpen = false;

    /**
     * Validate and persist the given emails; return a redirect on success.
     *
     * @param  list<string>  $emails
     */
    abstract protected function addUsers(array $emails): mixed;

    public function addSelected(): mixed
    {
        if ($this->selectedUsers === []) {
            throw ValidationException::withMessages([
                'no_selected_users' => 'You did not select any users.',
            ]);
        }

        return $this->addUsers(array_column($this->selectedUsers, 'email'));
    }

    protected function importEmails(array $emails): mixed
    {
        return $this->addUsers($emails);
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
}
