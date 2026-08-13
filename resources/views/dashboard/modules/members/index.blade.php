<x-dashboard-layout>
    <x-slot:title>{{ $module->name }} — Participants</x-slot:title>

    <section class="space-y-6">
        <x-module-header :module="$module" />

        <div class="space-y-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div class="min-w-0 flex-1 space-y-1">
                    <p class="text-sm text-base-content/70">Members enrolled in this module.</p>
                    {{-- Wire: filter $members (Livewire) or client-side search --}}
                    <label class="form-control w-full max-w-md">
                        <span class="label-text mb-1 text-sm font-medium">Search members</span>
                        <input
                            type="search"
                            name="member_query"
                            value="{{ request('q', old('member_query')) }}"
                            placeholder="Filter by name or email…"
                            class="input input-bordered input-sm w-full"
                            autocomplete="off"
                        />
                    </label>
                </div>

                <livewire:add-members-modal :module="$module"/>
            </div>

            @if (session('members_add_summary'))
                <div class="rounded-box border border-base-300 bg-base-200/40 px-4 py-3 text-sm">
                    <p class="font-medium">Add results</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5 text-base-content/80">
                        @foreach ((array) session('members_add_summary') as $line)
                            <li>{{ $line }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($errors->any())
                <ul class="list-disc space-y-1 pl-5 text-sm text-error">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif
            

            <div class="overflow-x-auto rounded-box border border-base-300">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Module role</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th class="w-12"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($members as $member)
                            <tr data-member-search="{{ strtolower($member->first_name.' '.$member->last_name.' '.$member->email) }}">
                                <td>
                                    <a href="{{ route('user.show', $member) }}" class="link">
                                        {{ $member->first_name }} {{ $member->last_name }}
                                    </a>
                                </td>
                                <td>{{ $member->email }}</td>
                                <td>{{ ucfirst($member->pivot->role_in_module) }}</td>
                                <td>{{ ucfirst($member->pivot->status) }}</td>
                                <td>
                                    {{ $member->pivot->joined_at ? \Illuminate\Support\Carbon::parse($member->pivot->joined_at)->format('M j, Y') : '—' }}
                                </td>
                                <td>
                                    <button
                                        type="button"
                                        class="btn btn-outline btn-xs btn-error"
                                        onclick="delete_member_{{ $member->id }}.showModal()"
                                        aria-label="Remove {{ $member->first_name }} {{ $member->last_name }}"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </button>

                                    <dialog id="delete_member_{{ $member->id }}" class="modal">
                                        <div class="modal-box w-[92vw] max-w-lg">
                                            <form method="POST" action="{{ route('dashboard.modules.members.destroy', $module) }}">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="user_id" value="{{ $member->id }}">
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-circle btn-outline absolute right-2 top-2"
                                                    onclick="delete_member_{{ $member->id }}.close()"
                                                    aria-label="Close"
                                                >
                                                    ×
                                                </button>

                                                <header class="space-y-1 pr-10">
                                                    <h3 class="text-xl font-bold text-primary">Remove member</h3>
                                                </header>
                                                <p class="mt-3 text-sm">
                                                    Are you sure you want to remove
                                                    {{ ucfirst($member->first_name) }} {{ ucfirst($member->last_name) }}
                                                    from the module?
                                                </p>

                                                <fieldset class="mt-4 flex flex-row justify-end gap-2">
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline"
                                                        onclick="delete_member_{{ $member->id }}.close()"
                                                    >
                                                        Cancel
                                                    </button>
                                                    <button type="submit" class="btn btn-sm btn-error">
                                                        Remove member
                                                    </button>
                                                </fieldset>
                                            </form>
                                        </div>
                                        <form method="dialog" class="modal-backdrop">
                                            <button type="submit">close</button>
                                        </form>
                                    </dialog>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-base-content/70">No members in this module yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</x-dashboard-layout>
