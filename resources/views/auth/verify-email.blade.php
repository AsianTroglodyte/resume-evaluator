<x-layout>
    <x-slot:title>
        Verify email
    </x-slot:title>

    <main class="flex min-h-screen items-center justify-center p-6">
        <section class="card w-full max-w-md border border-base-300 bg-base-100 shadow-xl">
            <div class="card-body gap-5">
                <header class="space-y-1">
                    <p class="text-xs uppercase tracking-wider text-base-content/60">Resume Evaluator</p>
                    <h1 class="text-2xl font-bold text-primary">Verify your email</h1>
                    <p class="text-sm text-base-content/70">
                        Thanks for signing up. Before you can use your account, please confirm your email address.
                    </p>
                </header>

                <div class="rounded-box border border-base-300 bg-base-200/40 px-4 py-3 text-sm text-base-content/80">
                    <p>
                        We sent a verification link to
                        @auth
                            <span class="font-medium text-base-content">{{ auth()->user()->email }}</span>.
                        @else
                            <span class="font-medium text-base-content">your email address</span>.
                        @endauth
                    </p>
                    <p class="mt-2 text-base-content/60">
                        Open the link in that message to activate your account. The link expires after a short time.
                    </p>
                </div>

                @if (session('status') === 'verification-link-sent')
                    <div role="alert" class="alert alert-success alert-soft text-sm">
                        <span>A new verification link has been sent to your email address.</span>
                    </div>
                @endif

                <div class="flex flex-col gap-3">
                    {{-- Wire action when verification routes are registered --}}
                    <form method="POST" action="{{route('verification.send')}}" class="w-full">
                        @csrf
                        <button type="submit" class="btn btn-primary w-full">
                            Resend verification email
                        </button>
                    </form>

                    {{-- Wire action when logout route is connected --}}
                    <form method="POST" action="{{ route('logout.destroy') }}" class="w-full">
                        @csrf
                        <button type="submit" class="btn btn-outline w-full">
                            Log out
                        </button>
                    </form>
                </div>

                <p class="text-center text-sm text-base-content/60">
                    Wrong address?
                    Log out and register again, or contact support if you need help.
                </p>
            </div>
        </section>
    </main>
</x-layout>
