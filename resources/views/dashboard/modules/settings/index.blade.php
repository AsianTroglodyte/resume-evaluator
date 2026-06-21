<x-dashboard-layout>
    <x-slot:title>{{ $module->name }} — Settings</x-slot:title>

    <section class="space-y-6">
        <x-module-header :module="$module" />

        <article class="rounded-box border border-base-300 bg-base-100 p-4">
            <header class="mb-4 space-y-1">
                <h3 class="text-lg font-semibold">Module Settings</h3>
                <p class="text-sm text-base-content/70">Basic module details and ownership information.</p>
            </header>

            <form method="POST" action={{ route('dashboard.modules.settings.index', $module)}} class="flex flex-col space-y-5">
                @csrf
                @method("PATCH")

                <label class="form-control w-full">
                    <span class="label-text mb-1 font-medium">Module name</span>
                    <input
                        type="text"
                        name="name"
                        value="{{ old('name', $module->name) }}"
                        class="input input-bordered w-full"
                        required
                    />
                </label>

                <label class="form-control w-full">
                    <span class="label-text mb-1 font-medium">Status</span>
                    <select
                        name="status"
                        class="select select-bordered w-full"
                        required
                    >
                        <option value="active" @selected(old('status', $module->status) === 'active')>Active</option>
                        <option value="archived" @selected(old('status', $module->status) === 'archived')>Archived</option>
                    </select>
                </label>

                <section class="rounded-box border border-base-300 p-4 text-sm">
                    <h4 class="font-medium">Module details</h4>

                    <div class="mt-4 grid gap-4 sm:grid-cols-3">
                        <div>
                            <p class="text-base-content/70">Created by</p>
                            <div class="font-medium">
                                {{ $module->creator->first_name }} {{ $module->creator->last_name }}
                            </div>
                            <div class="text-base-content/70">{{ $module->creator->email }}</div>
                        </div>

                        <div>
                            <p class="text-base-content/70">Created</p>
                            <p class="font-medium">{{ $module->created_at->format('M j, Y g:i A') }}</p>
                        </div>

                        <div>
                            <p class="text-base-content/70">Last updated</p>
                            <p class="font-medium">{{ $module->updated_at->format('M j, Y g:i A') }}</p>
                        </div>
                    </div>
                </section>

                
                <div class="flex flex-wrap items-center justify-end gap-3 border-t border-base-300 pt-4">
                    <button type="submit" class="btn btn-primary" >Save changes</button>
                </div>
            </form>
            
            <section class="mt-6 rounded-box border border-error/40 bg-error/5 p-4">
                <div class="flex flex-col items-start gap-4">
                    <div class="space-y-1">
                        <h4 class="font-medium text-error">Danger zone</h4>
                        <p class="text-sm text-base-content/70">
                            Deleting this module removes it from the dashboard and cannot be undone.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="btn btn-error btn-outline btn-sm shrink-0"
                        onclick="delete_module_{{ $module->id }}.showModal()"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        Delete module
                    </button>
                </div>
                <dialog id="delete_module_{{ $module->id }}" class="modal">
                    <div class="modal-box w-[92vw] max-w-3xl">
                        <form method="POST" action="{{ route('dashboard.modules.destroy', $module) }}">
                            @csrf
                            @method('DELETE')
                            <button
                                type="button"
                                class="btn btn-sm btn-circle btn-outline absolute right-2 top-2"
                                onclick="delete_module_{{ $module->id }}.close()"
                                aria-label="close">
                                x
                            </button>

                            <header class="space-y-1">
                                <h3 class="text-2xl font-bold text-primary">Delete</h3>
                            </header>
                            <p>
                                his action will irreversibly delete all data in the module
                                <b>{{ $module->name }}</b>?
                                Have you considered changing the <i>Status</i> to <i>Archived</i> yet?
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
                                    aria-label="remove">
                                    Delete module
                                </button>
                            </fieldset>
                        </form>
                    </div>
                    <form method="dialog" class="modal-backdrop">
                        <button type="submit">close</button>
                    </form>
                </dialog>

            </section>
        </article>
    </section>
</x-dashboard-layout>
