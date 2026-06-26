@php use App\Models\Module; @endphp

<x-dashboard-layout>
    <x-slot:title>Modules</x-slot:title>

    <section class="space-y-4">
        <header class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-2xl font-semibold">Modules</h2>
            </div>
            @can('create', Module::class)
            <a href="{{ route('dashboard.modules.create') }}" class="btn btn-primary btn-sm">
                Create Module
            </a>
            @endcan
        </header>

        <div class="grid gap-4 md:grid-cols-2">
            @foreach ($modules as $module)
                @can("view", $module)
                <div class="rounded-box border border-base-300 p-4 transition hover:bg-base-200 relative">
                    <a class="absolute inset-0 z-10"
                        href="{{ route('dashboard.modules.show', $module) }}">
                    </a>

                    <h3 class="text-lg font-semibold">{{ $module->name }}</h3>
                    <p class="mt-1 text-sm text-base-content/70">
                        Status: {{ ucfirst($module->status->value) }}
                    </p>
                </div>
                @endcan
            @endforeach
        </div>
    </section>
</x-dashboard-layout>
