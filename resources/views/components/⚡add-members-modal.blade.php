<?php
use Livewire\Component;
use App\Models\User;
use App\Models\Module;


new class extends Component {
    public Module $module;
    public string $userQuery = "";
    public bool $dialogIsOpen = false;
    public $queryResult = [];
    public string $results = "";
    public array $selectedUsers = [];

    public function mount(Module $module): void
    {
        $this->module = $module;
    }

    public function selectUser(int $id): void
    {
        $user = User::query()
            ->selectRaw("id, CONCAT(first_name, ' ', last_name, '; ', email) AS full_identifier")
            ->find($id);

        if ($user && ! collect($this->selectedUsers)->contains('id', $id)) {
            $this->selectedUsers[] = $user;
        }
    }

    public function with(): array
    {
        return [
            'queryResult' => filled($this->userQuery)
                ? User::query()
                    ->selectRaw("id, CONCAT(first_name, ' ', last_name, '; ', email) AS full_identifier")
                    ->whereRaw(
                        "CONCAT(first_name, ' ', last_name, '; ', email) LIKE ?",
                        ['%'.$this->userQuery.'%']
                    )
                    ->limit(101)
                    ->get()
                : collect(),
        ];
    }

    public function deselectUser() {

    }

    public function toggleDialogIsOpen(): void
    {
        $this->dialogIsOpen = !$this->dialogIsOpen;
    }
};

?>
{{-- dialog markup… wire:model.live="userQuery" on the search input --}}

@php
    use App\Enums\RoleInModule;
@endphp
<div>
<button type="button" class="btn btn-primary btn-sm shrink-0"
    onclick="add_members.showModal()"
    wire:click="toggleDialogIsOpen">
    Add Members
</button>

<dialog id="add_members" class="modal overflow-visible"
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
            onclick="add_members.close()">
            ×
        </button>
    

        <header class="space-y-1 pr-10">
            <h3 class="text-2xl font-bold text-primary">Add members</h3>
        </header>

        <div class="mt-4 tabs tabs-lift overflow-visible">
            <input
                type="radio"
                name="add_members_tab"
                role="tab"
                class="tab"
                aria-label="Find people"
                @checked(old('add_mode', session('add_members_tab', 'find')) !== 'paste')
            />
            <div role="tabpanel" class="tab-content space-y-0 overflow-visible border-base-300 bg-base-100 p-4">
                <form
                    method="POST"
                    action="{{ route('dashboard.modules.members.store', $module) }}"
                    class="flex flex-col gap-4 overflow-visible"
                >
                    @csrf
                    <fieldset class="rounded-box border border-base-300 bg-base-200/30 p-3">
                        <legend class="px-1 text-sm font-medium">Selected</legend>
                        <ul class="max-h-48 space-y-1 overflow-y-auto" data-user-search-results>
                        @if (filled($selectedUsers))
                            @foreach ($selectedUsers as $selectedUser)
                            <li 
                                class="px-2  text-sm text-base-content/50">
                                {{ $selectedUser}}
                            </li>
                            @endforeach
                        @elseif (!filled($selectedUsers))
                            <li 
                                class="px-2 text-center text-sm text-base-content/50">
                                Have not selected any users yet
                            </li>
                        @endif
                        </ul>
                    </fieldset>
                    
                    <input type="hidden" name="add_mode" value="find" />

                    <label class="form-control w-full">
                        <span class="label-text mb-1 font-medium">Search users</span>
                        <div class="relative w-full focus-within:[&_#user-combobox-list]:block">
                            <input
                                id="add-members-user-search"
                                type="search"
                                name="new_member_email"
                                wire:model.live.debounce.300ms="userQuery"
                                placeholder="Search by name or email…"
                                class="input input-bordered w-full @error('user_query') input-error @enderror"
                                autocomplete="off"
                                role="combobox"
                                aria-autocomplete="list"
                                aria-controls="user-combobox-list"
                                aria-expanded="{{ filled($userQuery) ? 'true' : 'false' }}"
                            />

                            @error('"new_member_email')
                                <span class="label-text-alt mt-1 text-error">{{ $message }}</span>
                            @enderror

                            @if (filled($userQuery))
                                <div
                                    id="user-combobox-list"
                                    role="listbox"
                                    class="absolute left-0 right-0 top-full z-50 mt-1 hidden max-h-48
                                    overflow-y-auto rounded-box border border-base-300 bg-base-100 shadow"
                                >
                                    <ul class="menu menu-sm w-full p-0">
                                        @if (filled($queryResult))
                                            @foreach ($queryResult as $user)
                                            {{-- wire:key="user-option-{{ $user->id }}" --}}
                                            {{-- wire:click="selectUser({{ $user->id }})" --}}
                                                <li role="option" >
                                                    <button
                                                        type="button"
                                                        class="rounded-none"
                                                    >
                                                        {{ $user->full_identifier }}
                                                    </button>
                                                </li>
                                            @endforeach
                                        @elseif (count($queryResult) > 100)
                                            <li class="px-3 py-2 text-sm text-base-content/70">
                                                Too many matches — refine your search.
                                            </li>
                                        @elseif (count($queryResult) === 0)
                                            <li class="px-3 py-2 text-sm text-base-content/70">
                                                No matches
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </label>


                    {{-- Wire: selected users mirrored here if you prefer chips over checkboxes. --}}
                    <div class="hidden flex-wrap gap-2" data-selected-users>
                        {{-- <span class="badge badge-outline gap-1">Name <button type="button" aria-label="Remove">×</button></span> --}}
                    </div>

                    <label class="form-control w-full">
                        <span class="label-text mb-1 font-medium">Role in module</span>
                        <select
                            name="role_in_module"
                            class="select select-bordered w-full @error('role_in_module') select-error @enderror"
                            required
                        >
                            <option value="{{ RoleInModule::Student->value }}" @selected(old('role_in_module', RoleInModule::Student->value) === RoleInModule::Student->value)>
                                Student
                            </option>
                            <option value="{{ RoleInModule::Instructor->value }}" @selected(old('role_in_module') === RoleInModule::Instructor->value)>
                                Instructor
                            </option>
                        </select>
                        @error('role_in_module')
                            <span class="label-text-alt mt-1 text-error">{{ $message }}</span>
                        @enderror
                    </label>

                    <div class="flex justify-end gap-2">
                        <button type="button" 
                        class="btn btn-ghost btn-sm" 
                        wire:click="toggleDialogIsOpen"
                        onclick="add_members.close()">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary btn-sm">
                            Add selected
                        </button>
                    </div>
                </form>
            </div>

            <input
                type="radio"
                name="add_members_tab"
                role="tab"
                class="tab"
                aria-label="Paste emails"
                @checked(old('add_mode', session('add_members_tab')) === 'paste')
            />
            <div role="tabpanel" class="tab-content border-base-300 bg-base-100 p-4">
                <form
                    method="POST"
                    action="{{ route('dashboard.modules.members.store', $module) }}"
                    class="flex flex-col gap-4"
                    enctype="multipart/form-data"
                >
                    @csrf
                    <input type="hidden" name="add_mode" value="paste" />

                    <label class="form-control w-full">
                        <div class="label-text mb-1 font-medium">Emails</div>
                        <textarea
                            name="emails"
                            rows="8"
                            class="textarea textarea-bordered font-mono text-sm @error('emails') textarea-error @enderror"
                            placeholder="one@southern.edu&#10;two@southern.edu&#10;&#10;Or paste a CSV column of emails…"
                        >{{ old('emails') }}</textarea>
                        <div class="label-text-alt mt-1 text-base-content/60">
                            Existing accounts are added; unknowns are reported for invites later.
                        </div>
                        @error('emails')
                            <span class="label-text-alt mt-1 text-error">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="form-control w-full">
                        <span class="label-text mb-1 font-medium">
                            CSV file <span class="font-normal text-base-content/50">(optional)</span>
                        </span>
                        <input
                            type="file"
                            name="emails_csv"
                            accept=".csv,text/csv"
                            class="file-input file-input-bordered w-full @error('emails_csv') file-input-error @enderror"
                        />
                        <span class="label-text-alt mt-1 text-base-content/60">
                            Prefer a file with an <code class="text-xs">email</code> column, or a single column of addresses.
                        </span>
                        @error('emails_csv')
                            <span class="label-text-alt mt-1 text-error">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="form-control w-full">
                        <span class="label-text mb-1 font-medium">Role in module</span>
                        <select
                            name="role_in_module"
                            class="select select-bordered w-full"
                            required
                        >
                            <option value="{{ RoleInModule::Student->value }}" @selected(old('role_in_module', RoleInModule::Student->value) === RoleInModule::Student->value)>
                                Student
                            </option>
                            <option value="{{ RoleInModule::Instructor->value }}" @selected(old('role_in_module') === RoleInModule::Instructor->value)>
                                Instructor
                            </option>
                        </select>
                    </label>

                    <div class="flex justify-end gap-2">
                        <button type="button" class="btn btn-ghost btn-sm" onclick="add_members.close()">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary btn-sm">
                            Add from list
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button type="submit">close</button>
    </form>
</dialog>

@if ($errors->hasAny(['role_in_module', 'user_query', 'user_ids', 'emails', 'emails_csv', 'new_member_email', 'add_mode']))
    <script>
        document.getElementById('add_members')?.showModal();
    </script>
@endif
</div>