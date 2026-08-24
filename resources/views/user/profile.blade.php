@php
    
@endphp
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

        <article class="rounded-box border border-base-300 bg-base-100 p-6">
            <header class="mb-4 space-y-1 border-b border-base-300 pb-4">
                <h3 class="text-lg font-semibold">Password</h3>
                <p class="text-sm text-base-content/70">Change the password you use to sign in.</p>
            </header>
                <form method="POST" action="{{ route('user.password.update') }}" class="flex flex-col gap-5">
                    @csrf
                    @method("PATCH")
                    <x-form-input
                        type="password"
                        label="Old Password"
                        name="current_password"
                        placeholder="Old Password"
                        autocomplete="given-name"
                        required
                    />
                    <x-form-input
                        type="password"
                        label="New Password"
                        name="new_password"
                        placeholder="Create Password"
                        autocomplete="given-name"
                        required
                    />
                    <x-form-input
                        type="password"
                        label="New Password Confirmation"
                        name="new_password_confirmation"
                        placeholder="Confirm Password"
                        autocomplete="given-name"
                        required
                    />
                    <button type="submit"
                            class="btn btn-primary w-fit">
                        Change password
                    </button>
                </form>
            </form>
        </article>
        {{-- @if ()
            
        @else
            
        @endif --}}
    </section>
</x-dashboard-layout>
