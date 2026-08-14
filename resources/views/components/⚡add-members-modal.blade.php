@props([
    "module"
    ])

<?php
use Livewire\Component;
use App\Models\User;
use App\Models\Module;
use App\Models\ModuleMembership;
use Illuminate\Validation\Rule;
use App\Enums\RoleInModule;
use Illuminate\Validation\ValidationException;

new class extends Component {
    public Module $module;
    public string $userQuery = "";
    public bool $dialogIsOpen = false;
    public $queryResult = [];
    public string $results = "";
    public array $selectedUsers = [];
    public RoleInModule $roleInModule = RoleInModule::Student;

    public function mount(Module $module): void
    {
        $this->module = $module;
    }

    public function selectUser(int $id): void
    {
        $user = User::query()
            ->find($id);

        if ($user && ! collect($this->selectedUsers)->contains('id', $id)) {
            $this->selectedUsers[] = $user;
        }
    }

    public function with(): array
    {
        $selectedIds = collect($this->selectedUsers)->pluck('id')->filter()->all();
        $memberIds = $this->module->members()->pluck('users.id')->all();

        $queryResult = filled($this->userQuery)
            ? User::query()
                ->selectRaw("id, CONCAT(first_name, ' ', last_name, '; ', email) AS full_identifier")
                ->whereRaw(
                    "CONCAT(first_name, ' ', last_name, '; ', email) LIKE ?",
                    ['%'.$this->userQuery.'%']
                )
                ->when($selectedIds !== [], fn ($query) => $query->whereNotIn('id', $selectedIds))
                ->when($memberIds !== [], fn ($query) => $query->whereNotIn('id', $memberIds))
                ->limit(101)
                ->get()
            : collect();

        return [
            'queryResult' => $queryResult,
        ];
    }

    public function deselectUser(int $id): void
    {
        $filteredArray = array_filter($this->selectedUsers, fn ($selectedUser) => $selectedUser["id"] !== $id);
        $this->selectedUsers = $filteredArray;
    }

    public function addSelected()
    {
        if ($this->selectedUsers === []) {
            throw ValidationException::withMessages([
                'no_selected_users' => "you did not select any users",
        ]);}

        forEach ($this->selectedUsers as $selectedUser) {            
            $this->validate([
                'selectedUsers' => ['required', 'array', 'min:1'],
                'selectedUsers.*.id' => ['required', 'integer', 'exists:users,id'],
                'roleInModule' => ['required', Rule::enum(RoleInModule::class)],
            ]);

            $newUser = User::where('email', $selectedUser["email"])->firstOrFail();

            // check if new potential user was previously a member
            // if was, then we get the membership record. else null
            $moduleMembership = ModuleMembership::where('module_id', $this->module->id)
                ->where('user_id', $newUser->id)
                ->first();

            // if never was a member we create the membership
            if ($moduleMembership) {
                if ($moduleMembership->status === 'active') {
                    throw ValidationException::withMessages([
                        'new_member_email' => 'This user is already an active member of the module.',
                    ]);
                }

                $moduleMembership->update([
                    'role_in_module' => $this->roleInModule,
                    'status' => 'active',
                    'removed_by_user_id' => null,
                    'removed_at' => null,
                    'added_by_user_id' => auth()->id(),
                ]);
            } 
            // If never was a members then we create a module Membership
            else {
                ModuleMembership::create([
                    'module_id' => $this->module->id,
                    'user_id' => $newUser->id,
                    'role_in_module' => $this->roleInModule,
                    'status' => 'active',
                    'added_by_user_id' => auth()->id(),
                    'removed_by_user_id' => null,
                ]);
            }
        }

        $members = $this->module->members()
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return redirect()->route('dashboard.modules.members.index', [
            'module' => $this->module,
            'members' => $members,
        ]);
    }

    public function clearComponentState() {
        $this->toggleDialogIsOpen();
        $this->userQuery = "";
        $this->selectedUsers = [];
    }

    public function toggleDialogIsOpen(): void
    {
        $this->dialogIsOpen = !$this->dialogIsOpen;
    }
};

?>

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
                    wire:submit="addSelected"
                    class="flex flex-col gap-4 overflow-visible"
                >
                    @csrf
                    <fieldset class="rounded-box border border-base-300 bg-base-200/30 p-3">
                        <legend class="px-1 text-sm font-medium">Selected</legend>
                        <ul class="max-h-100 space-y-0 overflow-y-auto" data-user-search-results>
                        @if (filled($selectedUsers))
                            @foreach ($selectedUsers as $selectedUser)
                            <li wire:key="selected-user-{{ $selectedUser['id'] }}">
                                <button
                                    type="button"
                                    class="flex w-full items-center justify-between gap-2 rounded px-2 cursor-pointer
                                    py-0.5 text-left text-sm text-base-content/80 hover:bg-base-200"
                                    wire:click="deselectUser({{ $selectedUser['id'] }})"
                                    aria-label="Remove {{ $selectedUser['first_name'] }} {{ $selectedUser['last_name'] }}"
                                >
                                    <span class="min-w-0 truncate">
                                        {{ $selectedUser['first_name'] }} {{ $selectedUser['last_name'] }}
                                        <span class="text-base-content/50">{{ $selectedUser['email'] }}</span>
                                    </span>
                                    <span class="shrink-0 text-base leading-none" aria-hidden="true">×</span>
                                </button>
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

                    @error("no_selected_users")
                        <span class="label-text-alt text-xs mt-1 text-error">{{ $message }}</span>
                    @enderror
                    
                    <input type="hidden" name="add_mode" value="find" />

                    <div class="form-control w-full">
                        <label for="add-members-user-search" class="label-text mb-1 w-fit font-medium">Search users</label>
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

                            @error('new_member_email')
                                <span class="label-text-alt mt-1 text-error">{{ $message }}</span>
                            @enderror

                            @if (filled($userQuery))
                                <div
                                    id="user-combobox-list"
                                    role="listbox"
                                    class="absolute left-0 right-0 top-full z-50 mt-1 hidden max-h-48
                                    overflow-y-auto rounded-box border border-base-300 bg-base-100 shadow"
                                >
                                {{-- wire:key="user-option-{{ $user->id }}" --}}
                                    <ul class="menu menu-sm w-full p-0">
                                        @if (count($queryResult) > 100)
                                        <li class="px-3 py-2 text-sm text-base-content/70">
                                            Too many matches — refine your search.
                                        </li>
                                        @elseif (filled($queryResult))
                                            @foreach ($queryResult as $user)
                                            <li role="option" 
                                            >
                                            <button
                                                type="button"
                                                class="rounded-none"
                                                wire:click="selectUser({{ $user->id }})"
                                            >
                                                    {{ $user->full_identifier }}
                                                </button>
                                            </li>
                                            @endforeach
                                        @elseif (count($queryResult) === 0)
                                            <li class="px-3 py-2 text-sm text-base-content/70">
                                                No matches
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="form-control w-full">
                        <label for="add-members-role-find" class="label-text mb-1 w-fit font-medium">Role in module</label>
                        <select
                            id="add-members-role-find"
                            name="roleInModule"
                            wire:model="roleInModule"
                            class="select select-bordered w-full @error('role_in_module') select-error @enderror"
                            required
                        >
                            <option value="{{ RoleInModule::Student->value }}">
                                Student
                            </option>
                            <option value="{{ RoleInModule::Instructor->value }}">
                                Instructor
                            </option>
                        </select>
                        @error('roleInModule')
                            <span class="label-text-alt mt-1 text-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" 
                            class="btn btn-ghost btn-sm" 
                            onclick="add_members.close()"
                            wire:click="clearComponentState" 
                        >
                            Cancel
                        </button>
                        <button 
                            wire:submit="addSelected" 
                            class="btn btn-primary btn-sm">
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

                    <div class="form-control w-full">
                        <label for="add-members-role-paste" class="label-text mb-1 w-fit font-medium">Role in module</label>
                        <select
                            id="add-members-role-paste"
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
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" class="btn btn-ghost btn-sm" onclick="add_members.close()">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary btn-sm" wire:click="toggleDialogIsOpen">
                            Add from list
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop" wire:click="toggleDialogIsOpen">
        <button type="submit">close</button>
    </form>
</dialog>

@if ($errors->hasAny(['role_in_module', 'user_query', 'user_ids', 'emails', 'emails_csv', 'new_member_email', 'add_mode']))
    <script>
        document.getElementById('add_members')?.showModal();
    </script>
@endif
</div>