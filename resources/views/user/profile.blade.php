<x-dashboard-layout>
    <x-slot:title>My profile</x-slot:title>

    <section class="space-y-6">
        <header class="space-y-1">
            <h2 class="text-2xl font-semibold">My profile</h2>
            <p class="text-sm text-base-content/70">Your account information.</p>
        </header>

        <article class="rounded-box border border-base-300 bg-base-100 p-6">
            <header class="mb-4 space-y-1 border-b border-base-300 pb-4">
                <h3 class="text-lg font-semibold">{{ $user->first_name }} {{ $user->last_name }}</h3>
                <p class="text-sm text-base-content/70">{{ $user->email }}</p>
            </header>

            <x-user-details :user="$user" />
        </article>
    </section>
</x-dashboard-layout>
