@props([
    "module"
    ])

<?php
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\User;
use App\Models\Module;
use App\Models\ModuleMembership;
use App\Enums\RoleInModule;
use App\Enums\ModuleMembershipStatus;
use App\Support\ParseEmailList;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;


new class extends Component {
    use WithFileUploads;

    public Module $module;
    public string $userQuery = "";
    public bool $dialogIsOpen = false;
    public $queryResult = [];
    public string $results = "";
    public array $selectedUsers = [];
    public RoleInModule $roleInModule = RoleInModule::Student;
    public string $csvString = "";
    public $emails_csv_file;
    public string $listSource = 'paste';

    public function mount(Module $module): void
    {
        $this->module = $module;
    }

    public function updatedListSource(string $value): void
    {
        if ($value === 'paste') {
            $this->emails_csv_file = null;
        } else {
            $this->csvString = '';
        }
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

        $email_array = array_map(fn (array $selectedUser) => $selectedUser['email'], $this->selectedUsers);
        $this->addUsers($this->selectedUsers);
    }

    public function addUsers(array $emails) {

        $validated = Validator::make(
            [
                'emails' => $emails,
                'roleInModule' => $this->roleInModule,
            ],
            [
                'emails' => ['required', 'array', 'min:1'],
                'emails.*' => [
                    'required',
                    'email',
                    'distinct',
                    Rule::exists('users', 'email'),
                ],
                'roleInModule' => [
                    'required',
                    Rule::enum(RoleInModule::class),
                ],
            ])->validate();

        forEach ($validated['emails'] as $email) {
            $newUser = User::where('email', $email)->firstOrFail();

            // check if new potential user was previously a member
            // if was, then we get the membership record. else null
            $moduleMembership = ModuleMembership::where('module_id', $this->module->id)
                ->where('user_id', $newUser->id)
                ->first();

            // if never was a member we create the membership
            if ($moduleMembership) {
                if ($moduleMembership->status === ModuleMembershipStatus::Active) {
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
        $this->userQuery = "";
        $this->selectedUsers = [];
    }

    public function cancel() {
        $this->toggleDialogIsOpen();
        $this->clearComponentState();
    }

    public function addFromPastedList() 
    {
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $this->csvString);
        rewind($stream);

        $email_array = (new ParseEmailList)($stream);
        $this->addUsers($email_array);
        // dd($email_array);
    }

    public function addFromFile() 
    {
        Validator::make(['emails_csv_file' => $this->emails_csv_file],
        [
            'emails_csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:1024']
        ]);

        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $this->emails_csv_file);
        rewind($stream);
        $email_array = (new ParseEmailList)($stream);
        dd("addFromFile");
        $this->addUsers($email_array);
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
                    @error('selectedUsers')
                        <span class="text-sm text-error">{{ $message }}</span>
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

                    <x-role-in-module-select id="add-members-role-find" />

                    <div class="flex justify-end gap-2">
                        <button type="button" 
                            class="btn btn-ghost btn-sm" 
                            onclick="add_members.close()"
                            wire:click="cancel" 
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
                aria-label="Import emails"
                @checked(old('add_mode', session('add_members_tab')) === 'paste')
            />
            <div role="tabpanel" class="tab-content border-base-300 bg-base-100 p-4">
                <form
                    class="flex flex-col gap-4"
                    enctype="multipart/form-data"
                    @if ($listSource === "paste")
                        wire:submit="addFromPastedList"
                    @elseif ($listSource === "file")
                        wire:submit="addFromFile"
                    @endif
                >
                    <div class="form-control w-full">
                        <span class="label-text mb-2 font-medium">Import source</span>
                        <div class="join">
                            <input
                                type="radio"
                                name="list_source"
                                value="paste"
                                class="btn join-item btn-sm"
                                aria-label="Paste text"
                                wire:model.live="listSource"
                            />
                            <input
                                type="radio"
                                name="list_source"
                                value="file"
                                class="btn join-item btn-sm"
                                aria-label="Upload file"
                                wire:model.live="listSource"
                            />
                        </div>
                    </div>

                    @if ($listSource === 'paste')
                        <label class="form-control w-full">
                            <div class="label-text mb-1 font-medium">Emails</div>
                            <textarea
                                name="emails_paste"
                                rows="8"
                                class="textarea textarea-bordered font-mono text-sm @error('emails_paste') textarea-error @enderror"
                                wire:model="csvString"
                                placeholder="one@southern.edu&#10;two@southern.edu&#10;&#10;Or paste a CSV column of emails…"
                            ></textarea>
                            <div class="label-text-alt mt-1 text-base-content/60">
                                One email per line, or a single CSV column. Optional header: <code class="text-xs">email</code>.
                            </div>
                        </label>
                        @error('emails_paste')
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        @enderror
                    @else
                        <label class="form-control w-full">
                            <span class="label-text mb-1 font-medium">CSV file</span>
                            <input
                                type="file"
                                wire:model="emails_csv_file"
                                accept=".csv,.txt,text/csv,text/plain"
                                class="file-input file-input-bordered w-full @error('emails_csv_file') file-input-error @enderror"
                            />
                            <span class="label-text-alt mt-1 text-base-content/60">
                                One column of addresses, or a header named <code class="text-xs">email</code>.
                            </span>
                        </label>
                        @error('emails_csv_file')
                            <span class="label-text-alt mt-1 text-error">{{ $message }}</span>
                        @enderror
                        <div wire:loading wire:target="emails_csv_file" class="text-sm text-base-content/60">
                            Uploading…
                        </div>
                    @endif

                    <x-role-in-module-select id="add-members-role-paste" />

                    
                    <div class="flex justify-end gap-2">
                        <button
                            type="button"
                            class="btn btn-ghost btn-sm"
                            onclick="add_members.close()"
                            wire:click="cancel"
                        >
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary btn-sm">
                            @if ($listSource === "paste")
                                Add from pasted list
                            @elseif ($listSource === "file")
                                Add from file
                            @endif
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