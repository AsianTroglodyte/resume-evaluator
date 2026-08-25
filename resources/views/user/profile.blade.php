
@php
    $profileNotice = session('profile_notice');
@endphp

<x-dashboard-layout>
    <x-slot:title>My profile</x-slot:title>
    @if ($profileNotice)
        <div class="toast toast-top toast-center z-50 toast-auto-dismiss pointer-events-none">
            <div
                role="status"
                class="alert shadow-lg {{ $profileNotice['type'] === 'success' ? 'alert-success' : 'alert-info' }}">
                <span>{{ $profileNotice['message'] }}</span>
            </div>
        </div>
    @endif
    <section class="space-y-6">
        <header class="space-y-1">
            <h2 class="text-2xl font-semibold">My profile</h2>
            <p class="text-sm text-base-content/70">Your account information.</p>
        </header>

        <article class="rounded-box border border-base-300 bg-base-100 p-6">
            <header class="mb-4 space-y-1 border-b border-base-300 pb-4">
                <h3 class="text-lg font-semibold">Name</h3>
                <p class="text-sm text-base-content/70">Update the name shown on your account.</p>
            </header>

            <form method="POST" action="{{ route('user.name.update') }}" class="flex max-w-md flex-col gap-5">
                @csrf
                @method('PATCH')

                <x-form-input
                    label="First name"
                    name="first_name"
                    autocomplete="given-name"
                    :value="old('first_name', $user->first_name)"
                    required
                />

                <x-form-input
                    label="Last name"
                    name="last_name"
                    autocomplete="family-name"
                    :value="old('last_name', $user->last_name)"
                    required
                />

                <div>
                    <button type="submit" class="btn btn-primary">
                        Update name
                    </button>
                </div>
            </form>
        </article>

        <article class="rounded-box border border-base-300 bg-base-100 p-6">
            <header class="mb-4 space-y-1 border-b border-base-300 pb-4">
                <h3 class="text-lg font-semibold">{{ $user->first_name }} {{ $user->last_name }}</h3>
                <p class="text-sm text-base-content/70">{{ $user->email }}</p>
            </header>

            <x-user-details :user="$user" />
        </article>

        <article class="rounded-box border border-base-300 bg-base-100 p-6">
            <header class="mb-4 space-y-1 border-b border-base-300 pb-4">
                <h3 class="text-lg font-semibold">Password</h3>
                <p class="text-sm text-base-content/70">Change the password you use to sign in.</p>
            </header>

            <form method="POST" action="{{ route('user.password.update') }}" class="flex max-w-md flex-col gap-5">
                @csrf
                @method('PATCH')

                <x-form-input
                    type="password"
                    label="Current password"
                    name="current_password"
                    autocomplete="current-password"
                    required
                />

                <x-form-input
                    type="password"
                    label="New password"
                    name="new_password"
                    autocomplete="new-password"
                    required
                />

                <x-form-input
                    type="password"
                    label="Confirm new password"
                    name="new_password_confirmation"
                    autocomplete="new-password"
                    required
                />

                <div>
                    <button type="submit" class="btn btn-primary">
                        Update password
                    </button>
                </div>
            </form>
        </article>
    </section>
</x-dashboard-layout>
