<x-layout>
    <x-slot:title>
        Register
    </x-slot:title>
    <main class="min-h-screen flex items-center justify-center p-6">
        <section class="card w-full max-w-md bg-base-100 shadow-xl border border-base-300">
            <div class="card-body gap-4">
                <header class="space-y-1">
                    <p class="text-xs uppercase tracking-wider text-base-content/60">Resume Evaluator</p>
                    <h1 class="text-2xl font-bold text-primary">Create your account</h1>
                    <p class="text-sm text-base-content/70">Start evaluating resumes.</p>
                </header>

                <form method="POST" class="flex flex-col gap-5">
                    @csrf
                    <x-form-input
                        label="First name"
                        name="first_name"
                        placeholder="Joe"
                        autocomplete="given-name"
                        required
                    />

                    <x-form-input
                        label="Last name"
                        name="last_name"
                        placeholder="Mama"
                        autocomplete="family-name"
                        required
                    />
                    <x-form-input
                        type="email"
                        label="Email"
                        name="email"
                        placeholder="you@example.com"
                        autocomplete="email"
                        required
                    />                    
                    <x-form-input
                        type="password"
                        label="Password"
                        name="password"
                        autocomplete="new-password"
                        placeholder="Create password"
                        required
                    />      
                    <x-form-input
                        type="password"
                        label="Confirm Password"
                        name="password_confirmation"
                        autocomplete="new-password"
                        placeholder="Confirm password"
                        required
                    />

                    <button type="submit"
                            class="btn btn-primary w-full">
                        Sign up
                    </button>
                </form>

                <p class="text-sm text-base-content/70 text-center">
                    Already have an account?
                    <a href="/login" class="link link-primary">Login</a>
                </p>
            </div>
        </section>
    </main>
</x-layout>
