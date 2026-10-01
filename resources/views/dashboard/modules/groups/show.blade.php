@php
    $groupStatus = session('groupStatus');
@endphp

<x-dashboard-layout>
    <x-slot:title>{{ $module->name }} — {{ $group->name }}</x-slot:title>

    <section class="space-y-6">
        <x-module-header :module="$module" />
        @if ($groupStatus)
        <div class="toast toast-top toast-center z-50 toast-auto-dismiss pointer-events-none">
            <div
                role="status"
                class="alert shadow-lg {{ $groupStatus['type'] === 'success' ? 'alert-success' : 'alert-info' }}">
                <span>{{ $groupStatus['message'] }}</span>
            </div>
        </div>
        @endif

        <div class="space-y-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div class="space-y-1">
                    <a href="{{ route('dashboard.modules.groups.index', $module) }}" class="link link-primary text-sm">
                        &larr; Back to groups
                    </a>
                    <h3 class="text-xl font-semibold">{{ $group->name }}</h3>
                    <p class="text-sm text-base-content/70">
                        {{ $members->count() }} {{ Str::plural('student', $members->count()) }} in this group.
                    </p>
                </div>

                @can('update', $group)
                    <livewire:add-group-members-modal :group="$group" />
                @endcan
            </div>

            <div class="overflow-x-auto rounded-box border border-base-300">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th class="w-12"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($members as $member)
                            <tr>
                                <td>
                                    <a href="{{ route('user.show', $member) }}" class="link">
                                        {{ $member->first_name }} {{ $member->last_name }}
                                    </a>
                                </td>
                                <td>{{ $member->email }}</td>
                                <td>
                                    @can('update', $group)
                                    <button
                                        type="button"
                                        class="btn btn-outline btn-xs btn-error"
                                        onclick="remove_group_member_{{ $member->id }}.showModal()"
                                        aria-label="Remove {{ $member->first_name }} {{ $member->last_name }} from {{ $group->name }}"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </button>

                                    <dialog id="remove_group_member_{{ $member->id }}" class="modal">
                                        <div class="modal-box w-[92vw] max-w-lg">
                                            <form method="POST" action="{{ route('dashboard.modules.groups.members.destroy', [$module, $group]) }}">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="user_id" value="{{ $member->id }}">
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-circle btn-outline absolute right-2 top-2"
                                                    onclick="remove_group_member_{{ $member->id }}.close()"
                                                    aria-label="Close"
                                                >
                                                    ×
                                                </button>

                                                <header class="space-y-1 pr-10">
                                                    <h3 class="text-xl font-bold text-primary">Remove from group</h3>
                                                </header>
                                                <p class="mt-3 text-sm">
                                                    Remove {{ $member->first_name }} {{ $member->last_name }} from {{ $group->name }}?
                                                    They stay in the module, ungrouped.
                                                </p>

                                                <fieldset class="mt-4 flex flex-row justify-end gap-2">
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline"
                                                        onclick="remove_group_member_{{ $member->id }}.close()"
                                                    >
                                                        Cancel
                                                    </button>
                                                    <button type="submit" class="btn btn-sm btn-error">
                                                        Remove from group
                                                    </button>
                                                </fieldset>
                                            </form>
                                        </div>
                                        <form method="dialog" class="modal-backdrop">
                                            <button type="submit">close</button>
                                        </form>
                                    </dialog>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-base-content/70">No students in this group yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</x-dashboard-layout>
