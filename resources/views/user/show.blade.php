<x-dashboard-layout>
    <x-slot:title>{{ $user->first_name }} {{ $user->last_name }}</x-slot:title>

    <section class="space-y-6">
        <header class="space-y-1">
            <h2 class="text-2xl font-semibold">{{ $user->first_name }} {{ $user->last_name }}</h2>
            <p class="text-sm text-base-content/70">User profile</p>
        </header>

        <article class="rounded-box border border-base-300 bg-base-100 p-6">
            <header class="mb-4 space-y-1 border-b border-base-300 pb-4">
                <h3 class="text-lg font-semibold">Account details</h3>
                <p class="text-sm text-base-content/70">{{ $user->email }}</p>
            </header>

            <x-user-details :user="$user" />

            @if (auth()->id() === $user->id)
                <p class="mt-6 text-sm">
                    <a href="{{ route('user.profile') }}" class="link link-primary">Go to my profile</a>
                </p>
            @endif
        </article>
    </section>
</x-dashboard-layout>
