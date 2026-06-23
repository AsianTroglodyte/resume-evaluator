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
                    <label class="form-control w-full">
                        <span class="label-text mb-1">First name</span>
                        <input
                            type="text"
                            name="first_name"
                            class="input input-bordered w-full"
                            placeholder="Joe"
                            autocomplete="given-name"
                            required
                        />
                    </label>

                    <label class="form-control w-full">
                        <span class="label-text mb-1">Last name</span>
                        <input
                            type="text"
                            name="last_name"
                            class="input input-bordered w-full"
                            placeholder="Mama"
                            autocomplete="family-name"
                            required
                        />
                    </label>

                    <label class="form-control w-full">
                        <span class="label-text mb-1">Email</span>
                        <input type="email" 
                               class="input input-bordered w-full"
                               placeholder="you@example.com"
                               name="email"
                               required/>
                    </label>

                    <label class="form-control w-full" for="password">
                        <span class="label-text mb-1">Password</span>
                        <input type="password"
                               class="input input-bordered w-full"
                               name="password"
                               id="password"
                               placeholder="Create password"
                               required/>
                    </label>

                    <label class="form-control w-full" for="password_confirmation">
                        <span class="label-text mb-1">Confirm Password</span>
                        <input type="password"
                               class="input input-bordered w-full"
                               name="password_confirmation"
                               id="password_confirmation"
                               placeholder="Confirm password"
                               required/>
                    </label>

                    <x-form-input />

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
