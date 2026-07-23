@props([
    'assignments' => [],
    'module'
])

<div class="space-y-3">

    @forelse ($assignments as $assignment)
        @can('view', $assignment)
        <div
            class="flex flex-row relative justify-between
            w-full rounded-box border border-base-300 p-3 text-left transition hover:bg-base-200"
        >
            <a class="absolute inset-0 z-0"
            href={{ route('dashboard.modules.assignments.show', [$module, $assignment]) }}>
            </a>
            <div class="flex flex-col justify-between">
                <h4 class="font-medium">{{ $assignment->title }}</h4>
                <p class="mt-2 text-sm text-base-content/70">
                    Due: {{ $assignment->due_date?->format('M j, Y g:i A') ?? 'No due date' }}
                </p>
            </div>

            @can('update', $assignment)
            <button
                type="button"
                class="btn btn-ghost btn-sm btn-square relative z-10"
                popovertarget="popover-{{ $assignment->id }}"
                style="anchor-name:--anchor-{{ $assignment->id }}"
                aria-label="Assignment actions"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10 3a1.5 1.5 0 110 3 1.5 1.5 0 010-3zM10 8.5a1.5 1.5 0 110 3 1.5 1.5 0 010-3zM11.5 15.5a1.5 1.5 0 10-3 0 1.5 1.5 0 003 0z" />
                </svg>
            </button>

                <ul
                class="dropdown menu z-20 w-52 rounded-box bg-base-100 shadow-sm"
                popover
                id="popover-{{ $assignment->id }}"
                style="position-anchor:--anchor-{{ $assignment->id }}"
                >
                <li>
                    <a href="{{ route('dashboard.modules.assignments.edit', [$module, $assignment]) }}">
                        Edit
                    </a>
                </li>
                    <li>
                        <button
                        type="button"
                        class="text-error"
                        onclick="assignment_delete_modal_{{ $assignment->id }}.showModal()"
                        >
                        Delete
                    </button>
                @endcan
            </li>
    </ul>
        </div>
        <dialog id="assignment_delete_modal_{{$assignment->id}}" class="modal">
            <div class="modal-box">
                <h3 class="text-lg font-bold">Delete Assignment?</h3>
                <p class="py-4">
                    This will delete {{$assignment->title}} and any data it contains
                </p>
                <button
                    type="button"
                    class="btn btn-sm btn-circle btn-outline absolute right-2 top-2"
                    onclick="assignment_delete_modal_{{ $assignment->id }}.close()"
                    aria-label="Close"
                >
                    x
                </button>
                <form 
                    method="POST" 
                    action="{{ route('dashboard.modules.assignments.destroy', [$module, $assignment]) }}"> 
                @csrf
                @method("DELETE")
                    <div class="flex flex-row justify-between">
                        <button type="button" class="btn btn-outline"
                        onclick="assignment_delete_modal_{{ $assignment->id }}.close()">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-error">
                            Delete
                        </button>
                    </div>
                </form>
            </div>
            <form method="dialog" class="modal-backdrop">
                <button>close</button>
            </form>
        </dialog>
        @endcan
    @empty
        <p class="text-sm text-base-content/70">No assignments for this module yet.</p>
    @endforelse
</div>