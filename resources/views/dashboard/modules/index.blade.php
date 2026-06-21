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

            </div>
            @endforeach
        </div>
    </section>
</x-dashboard-layout>
