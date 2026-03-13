<x-layout>
    <x-slot:title>
        Resume Evaluator
    </x-slot:title>
    <main class="min-h-screen flex items-center justify-center p-6">
        <section class="card w-full max-w-3xl bg-base-100 shadow-xl border border-base-300">
            <div class="card-body gap-6">
                <header class="border-b border-base-300 pb-4">
                    <h1 class="text-4xl font-bold tracking-tight text-primary">Resume Evaluator</h1>
                    <p class="mt-2 text-base-content/70">Professional profile review and match readiness analysis.</p>
                </header>

                <div class="grid gap-4 sm:grid-cols-2">
                    <article class="rounded-box bg-base-200 p-4">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-base-content/70">Summary</h2>
                        <p class="mt-2 text-sm">
                            Upload your resume, compare against job descriptions, and identify opportunities to improve impact.
                        </p>
                    </article>

                    <article class="rounded-box bg-base-200 p-4">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-base-content/70">Core Skills</h2>
                        <ul class="mt-2 space-y-1 text-sm">
                            <li>ATS Compatibility</li>
                            <li>Keyword Match Insights</li>
                        </ul>
                    </article>
                </div>

                <footer class="border-t border-base-300 pt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
                    <a href="/login" class="btn btn-outline">Login</a>
                    <a href="/register" class="btn btn-primary">Sign up</a>
                </footer>
            </div>
        </section>
    </main>
</x-layout>