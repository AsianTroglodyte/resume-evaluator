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

                <form class="flex flex-col gap-5">
                    <label class="form-control w-full">
                        <span class="label-text mb-1">Full name</span>
                        <input type="text" class="input input-bordered w-full" placeholder="Jane Doe" />
                    </label>

                    <label class="form-control w-full">
                        <span class="label-text mb-1">Email</span>
                        <input type="email" class="input input-bordered w-full" placeholder="you@example.com" />
                    </label>

                    <label class="form-control w-full">
                        <span class="label-text mb-1">Password</span>
                        <input type="password" class="input input-bordered w-full" placeholder="Create password" />
                    </label>

                    <a href="/dashboard/evaluations" class="btn btn-primary w-full">Sign up</a>
                </form>

                <p class="text-sm text-base-content/70 text-center">
                    Already have an account?
                    <a href="/login" class="link link-primary">Login</a>
                </p>
            </div>
        </section>
    </main>
</x-layout>