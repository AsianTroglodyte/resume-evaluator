@php
    $createErrors = $errors->getBag('createGroup');
@endphp

<x-dashboard-layout>
    <x-slot:title>{{ $module->name }} — Groups</x-slot:title>

    <section class="space-y-6">
        <x-module-header :module="$module" />

        <div class="space-y-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <p class="text-sm text-base-content/70">
                    Optional cohorts within this module (e.g. IT vs CS). With no groups, everyone is treated as one cohort.
                </p>

                <button type="button" class="btn btn-primary btn-sm shrink-0" onclick="create_group_modal.showModal()">
                    Create Group
                </button>
            </div>

            <dialog id="create_group_modal" class="modal">
                <div class="modal-box w-[92vw] max-w-lg">
                    <form method="POST" action="{{ route('dashboard.modules.groups.store', $module) }}">
                        @csrf
                        <button
                            type="button"
                            class="btn btn-sm btn-circle btn-outline absolute right-2 top-2"
                            onclick="create_group_modal.close()"
                            aria-label="Close"
                        >
                            ×
                        </button>

                        <header class="space-y-1 pr-10">
                            <h3 class="text-xl font-bold text-primary">Create group</h3>
                        </header>

                        <fieldset class="mt-4 flex flex-col gap-4">
                            <label class="form-control">
                                <span class="label-text mb-1">Name</span>
                                <input
                                    type="text"
                                    name="name"
                                    value="{{ $createErrors->any() ? old('name') : '' }}"
                                    placeholder="e.g. Computer Science"
                                    class="input input-bordered w-full @if ($createErrors->has('name')) input-error @endif"
                                    required
                                />
                                @if ($createErrors->has('name'))
                                    <span class="label-text-alt mt-1 text-error">{{ $createErrors->first('name') }}</span>
                                @endif
                            </label>

                            <button type="submit" class="btn btn-primary">Create group</button>
                        </fieldset>
                    </form>
                </div>
                <form method="dialog" class="modal-backdrop">
                    <button type="submit">close</button>
                </form>
            </dialog>
            @if ($createErrors->any())
                <script>
                    document.getElementById('create_group_modal')?.showModal();
                </script>
            @endif

            <div class="overflow-x-auto rounded-box border border-base-300">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Students</th>
                            <th>Created</th>
                            <th class="w-24"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($groups as $group)
                            @php
                                $updateErrors = $errors->getBag('updateGroup'.$group->id);
                            @endphp
                            <tr>
                                <td class="font-medium">
                                    <a href="{{ route('dashboard.modules.groups.show', [$module, $group]) }}" class="link link-hover">
                                        {{ $group->name }}
                                    </a>
                                </td>
                                <td>{{ $group->members_count }}</td>
                                <td>{{ $group->created_at->format('M j, Y') }}</td>
                                <td>
                                    <div class="flex justify-end gap-2">
                                        <button
                                            type="button"
                                            class="btn btn-outline btn-xs"
                                            onclick="edit_group_{{ $group->id }}.showModal()"
                                            aria-label="Rename {{ $group->name }}"
                                        >
                                            Rename
                                        </button>
                                        <button
                                            type="button"
                                            class="btn btn-outline btn-xs btn-error"
                                            onclick="delete_group_{{ $group->id }}.showModal()"
                                            aria-label="Delete {{ $group->name }}"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </div>

                                    <dialog id="edit_group_{{ $group->id }}" class="modal">
                                        <div class="modal-box w-[92vw] max-w-lg">
                                            <form method="POST" action="{{ route('dashboard.modules.groups.update', [$module, $group]) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-circle btn-outline absolute right-2 top-2"
                                                    onclick="edit_group_{{ $group->id }}.close()"
                                                    aria-label="Close"
                                                >
                                                    ×
                                                </button>

                                                <header class="space-y-1 pr-10">
                                                    <h3 class="text-xl font-bold text-primary">Rename group</h3>
                                                </header>

                                                <fieldset class="mt-4 flex flex-col gap-4">
                                                    <label class="form-control">
                                                        <span class="label-text mb-1">Name</span>
                                                        <input
                                                            type="text"
                                                            name="name"
                                                            value="{{ $updateErrors->any() ? old('name') : $group->name }}"
                                                            class="input input-bordered w-full @if ($updateErrors->has('name')) input-error @endif"
                                                            required
                                                        />
                                                        @if ($updateErrors->has('name'))
                                                            <span class="label-text-alt mt-1 text-error">{{ $updateErrors->first('name') }}</span>
                                                        @endif
                                                    </label>

                                                    <button type="submit" class="btn btn-primary">Save</button>
                                                </fieldset>
                                            </form>
                                        </div>
                                        <form method="dialog" class="modal-backdrop">
                                            <button type="submit">close</button>
                                        </form>
                                    </dialog>
                                    @if ($updateErrors->any())
                                        <script>
                                            document.getElementById('edit_group_{{ $group->id }}')?.showModal();
                                        </script>
                                    @endif

                                    <dialog id="delete_group_{{ $group->id }}" class="modal">
                                        <div class="modal-box w-[92vw] max-w-lg">
                                            <form method="POST" action="{{ route('dashboard.modules.groups.destroy', [$module, $group]) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-circle btn-outline absolute right-2 top-2"
                                                    onclick="delete_group_{{ $group->id }}.close()"
                                                    aria-label="Close"
                                                >
                                                    ×
                                                </button>

                                                <header class="space-y-1 pr-10">
                                                    <h3 class="text-xl font-bold text-primary">Delete group</h3>
                                                </header>
                                                <p class="mt-3 text-sm">
                                                    Are you sure you want to delete {{ $group->name }}?
                                                    @if ($group->members_count > 0)
                                                        Its {{ $group->members_count }} {{ Str::plural('student', $group->members_count) }}
                                                        will become ungrouped.
                                                    @endif
                                                </p>

                                                <fieldset class="mt-4 flex flex-row justify-end gap-2">
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline"
                                                        onclick="delete_group_{{ $group->id }}.close()"
                                                    >
                                                        Cancel
                                                    </button>
                                                    <button type="submit" class="btn btn-sm btn-error">
                                                        Delete group
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
                                <td colspan="4" class="text-center text-base-content/70">No groups yet. Everyone in this module is one cohort.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</x-dashboard-layout>
