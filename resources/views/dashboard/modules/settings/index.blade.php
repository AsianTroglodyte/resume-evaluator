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
        </article>
    </section>
</x-dashboard-layout>
