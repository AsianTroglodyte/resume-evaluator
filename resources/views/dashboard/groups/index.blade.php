<x-dashboard-layout>
    <x-slot:title>Groups</x-slot:title>

    <section class="space-y-4">
        <header class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-2xl font-semibold">Groups</h2>
            </div>
            <a href="{{ route('dashboard.groups.create') }}" class="btn btn-primary btn-sm">Create Group</a>
        </header>

        <div class="grid gap-4 md:grid-cols-2">
            @foreach ($groups as $group)
            <a class="rounded-box border border-base-300 bg-base-100 p-4 "
            href="/dashboard/groups/{{ $group["id"] }}">
                <h3 class="text-lg font-semibold">{{ $group['name'] }}</h3>
                <p class="mt-1 text-sm text-base-content/70">
                    Status: {{ ucfirst($group['status']) }} ·
                    {{-- {{ $group['pending_assignments'] }} assignment pending --}}
                    bruh
                </p>
            </a>
            @endforeach
        </div>
    </section>
</x-dashboard-layout>