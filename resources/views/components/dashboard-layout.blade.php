@php
    $pageTitle = trim((string) ($title ?? 'Dashboard'));
@endphp

<x-layout>
    <x-slot:title>{{ $pageTitle }}</x-slot:title>

    <section class="container mx-auto max-w-6xl p-4 md:p-6">
        <div class="rounded-box border border-base-300 bg-base-100 shadow-sm">
            <header class="border-b border-base-300 p-4 md:p-6">
                <h1 class="text-3xl font-bold text-primary">Dashboard</h1>
            </header>

            <nav class="border-b border-base-300 px-4 md:px-6" aria-label="Dashboard sections">
                <div role="tablist" class="tabs tabs-border">
                    <a
                        role="tab"
                        href="/dashboard/resumes"
                        class="tab {{ request()->is('dashboard/resumes*') ? 'tab-active' : '' }}"
                        aria-current="{{ request()->is('dashboard/resumes*') ? 'page' : 'false' }}"
                    >
                        Resumes
                    </a>
                    <a
                        role="tab"
                        href="/dashboard/groups"
                        class="tab {{ request()->is('dashboard/groups*') ? 'tab-active' : '' }}"
                        aria-current="{{ request()->is('dashboard/groups*') ? 'page' : 'false' }}"
                    >
                        Groups
                    </a>
                </div>
            </nav>

            <div class="p-4 md:p-6">
                {{ $slot }}
            </div>
        </div>
    </section>
</x-layout>