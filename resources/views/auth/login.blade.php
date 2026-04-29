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

                <form class="flex flex-col gap-5">
                    <label class="form-control w-full flex flex-col">
                        <span class="label-text mb-1">Email</span>
                        <input type="email" class="input input-bordered w-full" placeholder="you@example.com" />
                    </label>

                    <label class="form-control w-full">
                        <span class="label-text mb-1">Password</span>
                        <input type="password" class="input input-bordered w-full" placeholder="Enter password" />
                    </label>

                    <a href="/dashboard/resumes" class="btn btn-primary w-full">Login</a>
                </form>

                <p class="text-sm text-base-content/70 text-center">
                    Don't have an account?
                    <a href="/register" class="link link-primary">Sign up</a>
                </p>
            </div>
        </section>
    </main>
</x-layout>