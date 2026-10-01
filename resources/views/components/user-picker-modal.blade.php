{{--
    Dialog wrapper around <x-user-picker> for Livewire components using
    App\Support\PicksUsersInModal (users are persisted on "Add").
--}}
@props([
    'id',
    'title',
    'triggerLabel' => 'Add Members',
    'description' => null,
    'dialogIsOpen',
    'userQuery',
    'queryResult',
    'selectedUsers',
    'listSource',
])

<button type="button" class="btn btn-primary btn-sm shrink-0"
    onclick="document.getElementById('{{ $id }}').showModal()"
    wire:click="toggleDialogIsOpen">
    {{ $triggerLabel }}
</button>

<dialog id="{{ $id }}" class="modal"
    @if ($dialogIsOpen)
        open
    @endif
    >
    <div class="modal-box w-[92vw] max-w-2xl overflow-visible">
        <button
            type="button"
            class="btn btn-sm btn-circle btn-outline absolute right-2 top-2"
            wire:click="toggleDialogIsOpen"
            aria-label="Close"
            onclick="document.getElementById('{{ $id }}').close()">
            ×
        </button>

        <header class="space-y-1 pr-10">
            <h3 class="text-1xl font-bold text-primary">{{ $title }}</h3>
            @if ($description)
                <p class="text-sm text-base-content/70">{{ $description }}</p>
            @endif
        </header>

        @isset($fields)
            <div class="mt-4">
                {{ $fields }}
            </div>
        @endisset

        <x-user-picker
            class="mt-4"
            :id="$id"
            :user-query="$userQuery"
            :query-result="$queryResult"
            :selected-users="$selectedUsers"
            :list-source="$listSource"
        >
            <x-slot:find-actions>
                <button type="button"
                    class="btn btn-ghost btn-sm"
                    onclick="document.getElementById('{{ $id }}').close()"
                    wire:click="cancel"
                >
                    Cancel
                </button>
                <button type="button" class="btn btn-primary btn-sm" wire:click="addSelected">
                    Add selected
                </button>
            </x-slot:find-actions>
            <x-slot:import-actions>
                <button type="button"
                    class="btn btn-ghost btn-sm"
                    onclick="document.getElementById('{{ $id }}').close()"
                    wire:click="cancel"
                >
                    Cancel
                </button>
            </x-slot:import-actions>
        </x-user-picker>
    </div>
    <form method="dialog" class="modal-backdrop" wire:click="toggleDialogIsOpen">
        <button type="submit">close</button>
    </form>
</dialog>
