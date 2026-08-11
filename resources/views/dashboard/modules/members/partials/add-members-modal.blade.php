@php
    use App\Enums\RoleInModule;
@endphp

<dialog id="add_members" class="modal">
    <div class="modal-box w-[92vw] max-w-2xl">
        <button
            type="button"
            class="btn btn-sm btn-circle btn-outline absolute right-2 top-2"
            onclick="add_members.close()"
            aria-label="Close"
        >
            ×
        </button>

        <header class="space-y-1 pr-10">
            <h3 class="text-2xl font-bold text-primary">Add members</h3>
            <p class="text-sm text-base-content/60">
                Add people who already have accounts. Unknown emails can be invited later.
            </p>
        </header>

        {{-- DaisyUI radio tabs (no JS). Wire each panel’s form separately. --}}
        <div class="mt-4 tabs tabs-lift">
            <input
                type="radio"
                name="add_members_tab"
                role="tab"
                class="tab"
                aria-label="Find people"
                @checked(old('add_mode', session('add_members_tab', 'find')) !== 'paste')
            />
            <div role="tabpanel" class="tab-content space-y-0 border-base-300 bg-base-100 p-4">
                <form
                    method="POST"
                    action="{{ route('dashboard.modules.members.store', $module) }}"
                    class="flex flex-col gap-4"
                >
                    @csrf
                    <input type="hidden" name="add_mode" value="find" />

                    <label class="form-control w-full">
                        <span class="label-text mb-1 font-medium">Search users</span>
                        <input
                            type="search"
                            name="user_query"
                            value="{{ old('user_query') }}"
                            placeholder="Name or email…"
                            class="input input-bordered w-full @error('user_query') input-error @enderror"
                            autocomplete="off"
                        />
                        <span class="label-text-alt mt-1 text-base-content/60">
                            Wire this to a user typeahead (exclude active members).
                        </span>
                        @error('user_query')
                            <span class="label-text-alt mt-1 text-error">{{ $message }}</span>
                        @enderror
                    </label>

                    {{-- Wire: render search hits here (checkboxes or click-to-select). --}}
                    <fieldset class="rounded-box border border-base-300 bg-base-200/30 p-3">
                        <legend class="px-1 text-sm font-medium">Matches</legend>
                        <ul class="max-h-48 space-y-1 overflow-y-auto" data-user-search-results>
                            <li class="px-2 py-3 text-center text-sm text-base-content/50">
                                Search results will appear here.
                            </li>
                            {{--
                            <li>
                                <label class="flex cursor-pointer items-center gap-3 rounded px-2 py-2 hover:bg-base-200">
                                    <input type="checkbox" name="user_ids[]" value="USER_ID" class="checkbox checkbox-sm checkbox-primary" />
                                    <span class="min-w-0">
                                        <span class="block truncate font-medium">First Last</span>
                                        <span class="block truncate text-xs text-base-content/60">email@example.edu</span>
                                    </span>
                                </label>
                            </li>
                            --}}
                        </ul>
                    </fieldset>

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
                        <button type="button" class="btn btn-ghost btn-sm" onclick="add_members.close()">
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
                            One email per line, or comma/semicolon-separated. Existing accounts are added; unknowns are reported for invites later.
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
