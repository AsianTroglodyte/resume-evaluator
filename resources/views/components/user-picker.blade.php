{{--
    Inline picker panel for Livewire components using App\Support\PicksUsers.
    Contains no <form> elements and only type="button" buttons, so it can sit
    inside a parent form; wire: directives bind to the enclosing component.
--}}
@props([
    'id',
    'userQuery',
    'queryResult',
    'selectedUsers',
    'listSource',
    'importLabel' => null,
])

@php
    $emailErrorKeys = ['emails', 'emails.*'];
    $importLabel ??= $listSource === 'file' ? 'Add from file' : 'Add from pasted list';
@endphp

<div {{ $attributes->class(['tabs tabs-lift']) }}>
    <input
        type="radio"
        name="{{ $id }}_tab"
        role="tab"
        class="tab"
        aria-label="Find people"
        form="{{ $id }}_detached"
        checked
    />
    <div role="tabpanel" class="tab-content space-y-4 border-base-300 bg-base-100 p-4">
        <div class="form-control w-full">
            <label for="{{ $id }}-user-search" class="label-text mb-1 w-fit font-medium">Search users</label>
            <div class="relative w-full focus-within:[&_[role=listbox]]:block">
                <input
                    id="{{ $id }}-user-search"
                    type="search"
                    form="{{ $id }}_detached"
                    wire:model.live.debounce.300ms="userQuery"
                    wire:keydown.enter.prevent
                    placeholder="Search by name or email…"
                    class="input input-bordered w-full"
                    autocomplete="off"
                    role="combobox"
                    aria-autocomplete="list"
                    aria-controls="{{ $id }}-combobox-list"
                    aria-expanded="{{ filled($userQuery) ? 'true' : 'false' }}"
                />

                @if (filled($userQuery))
                    <div
                        id="{{ $id }}-combobox-list"
                        role="listbox"
                        class="absolute left-0 right-0 top-full z-50 mt-1 hidden max-h-48
                        overflow-y-auto rounded-box border border-base-300 bg-base-100 shadow"
                    >
                        <ul class="menu menu-sm w-full p-0">
                            @if (count($queryResult) > 100)
                                <li class="px-3 py-2 text-sm text-base-content/70">
                                    Too many matches — refine your search.
                                </li>
                            @elseif (filled($queryResult))
                                @foreach ($queryResult as $user)
                                    <li role="option" wire:key="{{ $id }}-candidate-{{ $user->id }}">
                                        <button
                                            type="button"
                                            class="rounded-none"
                                            wire:click="selectUser({{ $user->id }})"
                                        >
                                            {{ $user->first_name }} {{ $user->last_name }}; {{ $user->email }}
                                            @if ($user->picker_note)
                                                <span class="badge badge-ghost badge-sm">{{ $user->picker_note }}</span>
                                            @endif
                                        </button>
                                    </li>
                                @endforeach
                            @else
                                <li class="px-3 py-2 text-sm text-base-content/70">
                                    No matches
                                </li>
                            @endif
                        </ul>
                    </div>
                @endif
            </div>
        </div>

        <fieldset class="rounded-box border border-base-300 bg-base-200/30 p-3">
            <legend class="px-1 text-sm font-medium">Selected ({{ count($selectedUsers) }})</legend>
            <ul class="max-h-72 space-y-0 overflow-y-auto pr-1">
                @forelse ($selectedUsers as $selectedUser)
                    <li wire:key="{{ $id }}-selected-{{ $selectedUser['id'] }}">
                        <button
                            type="button"
                            class="flex w-full cursor-pointer items-center justify-between gap-2 rounded px-2 py-0.5 text-left text-sm text-base-content/80 hover:bg-base-200"
                            wire:click="deselectUser({{ $selectedUser['id'] }})"
                            aria-label="Remove {{ $selectedUser['first_name'] }} {{ $selectedUser['last_name'] }}"
                        >
                            <span class="min-w-0 truncate">
                                {{ $selectedUser['first_name'] }} {{ $selectedUser['last_name'] }}
                                <span class="text-base-content/50">{{ $selectedUser['email'] }}</span>
                                @if ($selectedUser['picker_note'] ?? null)
                                    <span class="badge badge-ghost badge-sm">{{ $selectedUser['picker_note'] }}</span>
                                @endif
                            </span>
                            <span class="shrink-0 text-base leading-none" aria-hidden="true">×</span>
                        </button>
                    </li>
                @empty
                    <li class="px-2 text-center text-sm text-base-content/50">
                        Have not selected any users yet
                    </li>
                @endforelse
            </ul>
        </fieldset>

        @error('no_selected_users')
            <span class="block text-sm text-error">{{ $message }}</span>
        @enderror
        @foreach ($emailErrorKeys as $errorKey)
            @error($errorKey)
                <span class="block text-sm text-error">{{ $message }}</span>
            @enderror
        @endforeach

        @isset($findActions)
            <div class="flex justify-end gap-2">
                {{ $findActions }}
            </div>
        @endisset
    </div>

    <input
        type="radio"
        name="{{ $id }}_tab"
        role="tab"
        class="tab"
        aria-label="Import emails"
        form="{{ $id }}_detached"
    />
    <div role="tabpanel" class="tab-content space-y-4 border-base-300 bg-base-100 p-4">
        <div class="form-control w-full">
            <span class="label-text mb-2 font-medium">Import source</span>
            <div class="join">
                <input
                    type="radio"
                    name="{{ $id }}_list_source"
                    value="paste"
                    form="{{ $id }}_detached"
                    class="btn join-item btn-sm"
                    aria-label="Paste text"
                    wire:model.live="listSource"
                />
                <input
                    type="radio"
                    name="{{ $id }}_list_source"
                    value="file"
                    form="{{ $id }}_detached"
                    class="btn join-item btn-sm"
                    aria-label="Upload file"
                    wire:model.live="listSource"
                />
            </div>
        </div>

        <div @class(['hidden' => $listSource !== 'paste'])>
            <label class="form-control w-full">
                <div class="label-text mb-1 font-medium">Emails</div>
                <textarea
                    rows="6"
                    form="{{ $id }}_detached"
                    class="textarea textarea-bordered w-full font-mono text-sm @error('email_list') textarea-error @enderror"
                    wire:model="csvString"
                    placeholder="one@southern.edu&#10;two@southern.edu&#10;&#10;Or paste a CSV column of emails…"
                ></textarea>
                <div class="label-text-alt mt-1 text-base-content/60">
                    One email per line, or a single CSV column. Optional header: <code class="text-xs">email</code>.
                </div>
            </label>
        </div>
        <div @class(['hidden' => $listSource !== 'file'])>
            <label class="form-control w-full">
                <span class="label-text mb-1 font-medium">CSV file</span>
                <input
                    type="file"
                    form="{{ $id }}_detached"
                    wire:model="emails_csv_file"
                    accept=".csv,.txt,text/csv,text/plain"
                    class="file-input file-input-bordered w-full @error('emails_csv_file') file-input-error @enderror"
                />
                <span class="label-text-alt mt-1 text-base-content/60">
                    One column of addresses, or a header named <code class="text-xs">email</code>.
                </span>
            </label>
            <div wire:loading wire:target="emails_csv_file" class="text-sm text-base-content/60">
                Uploading…
            </div>
        </div>

        @foreach (['email_list', 'emails_csv_file', ...$emailErrorKeys] as $errorKey)
            @error($errorKey)
                <span class="block text-sm text-error">{{ $message }}</span>
            @enderror
        @endforeach

        <div class="flex justify-end gap-2">
            {{ $importActions ?? '' }}
            <button type="button" class="btn btn-primary btn-sm" wire:click="addFromImport">
                {{ $importLabel }}
            </button>
        </div>
    </div>
</div>
