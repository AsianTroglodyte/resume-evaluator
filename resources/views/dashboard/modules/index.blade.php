<x-dashboard-layout>
    <x-slot:title>Modules</x-slot:title>

    <section class="space-y-4">
        <header class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-2xl font-semibold">Modules</h2>
            </div>
            <a href="{{ route('dashboard.modules.create') }}" class="btn btn-primary btn-sm">Create Module</a>
        </header>

        <div class="grid gap-4 md:grid-cols-2">
            @foreach ($modules as $module)
            <div class="rounded-box border border-base-300 p-4 transition hover:bg-base-200 relative">
                <a class="absolute inset-0 z-10"
                    href="{{ route('dashboard.modules.show', $module) }}">
                </a>

                <h3 class="text-lg font-semibold">{{ $module->name }}</h3>
                <p class="mt-1 text-sm text-base-content/70">
                    Status: {{ ucfirst($module->status) }}
                </p>
                <button type="submit" class="btn btn-outline btn-xs btn-error absolute z-20 bottom-0 right-0" 
                onclick="delete_module_{{$module->id}}.showModal()">
                    {{-- <span class="sr-only">Delete</span> --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </button>

                <dialog id="delete_module_{{ $module->id }}" class="modal">
                    <div class="modal-box w-[92vw] max-w-3xl">
                        <form method="POST" action="{{ route('dashboard.modules.destroy', $module) }}">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="user_id"value={{ $module->id }} >
                            <button
                                type="button"
                                class="btn btn-sm btn-circle btn-outline absolute right-2 top-2"
                                onclick="delete_module_{{ $module->id }}.close()"
                                aria-label="close">
                                x
                            </button>

                            <header class="space-y-1">
                                <h3 class="text-2xl font-bold text-primary">archive</h3>
                            </header>
                            <p>
                                Are you sure you want to delete?
                                {{ $module->name }}
                                This will 
                            </p>

                            <fieldset class="mt-4 flex flex-row gap-5">
                                <button 
                                    type="button"
                                    class="btn btn-sm btn-outline"
                                    onclick="delete_module_{{ $module->id }}.close()"
                                    aria-label="cancel">
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    class="btn btn-sm btn-error"
                                    onclick="delete_module_{{ $module->id }}.close()"
                                    aria-label="remove">
                                    remove
                                </button>
                            </fieldset>
                        </form>
                    </div>
                    <form method="dialog" class="modal-backdrop">
                        <button type="submit">close</button>
                    </form>
                </dialog>
            </div>
            @endforeach
        </div>
    </section>
</x-dashboard-layout>
