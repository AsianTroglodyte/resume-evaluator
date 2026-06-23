<x-layout>
    <x-slot:title>
        Login
    </x-slot:title>
    <main class="min-h-screen flex items-center justify-center p-6">
        <section class="card w-full max-w-md bg-base-100 shadow-xl border border-base-300">
            <div class="card-body gap-4">
                <header class="space-y-1">
                    <p class="text-xs uppercase tracking-wider text-base-content/60">Resume Evaluator</p>
                    <h1 class="text-2xl font-bold text-primary">Welcome back</h1>
                    <p class="text-sm text-base-content/70">Sign in to continue evaluating resumes.</p>
                </header>
                <!-- action="{ route() }}" -->
                <form method="POST" action="{{route('login.store')}}"  class="flex flex-col gap-5">
                    @csrf
                    <x-form-input
                        type="email"
                        label="Email"
                        name="email"
                        autocomplete="email"
                        placeholder="you@example.come"
                        required
                    />      
                    <x-form-input
                        type="password"
                        label="Password"
                        name="password"
                        autocomplete="current-password"
                        placeholder="Enter password"
                        required
                    />      

                    <button type="submit"
                            class="btn btn-primary w-full">
                        Login
                    </button>
                </form>

                <p class="text-sm text-base-content/70 text-center">
                    Don't have an account?
                    <a href="/register" class="link link-primary">Sign up</a>
                </p>
            </div>
        </section>
    </main>
</x-layout>
