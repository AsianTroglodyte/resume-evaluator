<x-dashboard-layout>
    <x-slot:title>{{ $module->name }} — Settings</x-slot:title>

    <section class="space-y-6">
        <x-module-header :module="$module" />

        <article class="rounded-box border border-base-300 bg-base-100 p-4">
            <header class="mb-4 space-y-1">
                <h3 class="text-lg font-semibold">Module Settings</h3>
                <p class="text-sm text-base-content/70">Basic module details and ownership information.</p>
            </header>

            <dl class="divide-y divide-base-300 text-sm">
                <div class="grid gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="font-medium text-base-content/70">Module name</dt>
                    <dd class="sm:col-span-2">{{ $module->name }}</dd>
                </div>

                <div class="grid gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="font-medium text-base-content/70">Status</dt>
                    <dd class="sm:col-span-2">
                        <span @class([
                            'badge badge-sm',
                            'badge-neutral' => $module->status === 'Archived',
                            'badge-success' => $module->status === 'active',
                        ])>
                            {{ ucfirst($module->status) }}
                        </span>
                    </dd>
                </div>

                <div class="grid gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="font-medium text-base-content/70">Created by</dt>
                    <dd class="sm:col-span-2">
                        <div class="font-medium">
                            {{ $module->creator->first_name }} {{ $module->creator->last_name }}
                        </div>
                        <div class="text-base-content/70">{{ $module->creator->email }}</div>
                    </dd>
                </div>

                <div class="grid gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="font-medium text-base-content/70">Created</dt>
                    <dd class="sm:col-span-2">{{ $module->created_at->format('M j, Y g:i A') }}</dd>
                </div>

                <div class="grid gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="font-medium text-base-content/70">Last updated</dt>
                    <dd class="sm:col-span-2">{{ $module->updated_at->format('M j, Y g:i A') }}</dd>
                </div>
            </dl>
        </article>
    </section>
</x-dashboard-layout>
