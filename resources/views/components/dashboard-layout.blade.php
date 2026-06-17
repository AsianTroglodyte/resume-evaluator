@php
    $pageTitle = trim((string) ($title ?? 'Dashboard'));
@endphp

<x-layout>
    <x-slot:title>{{ $pageTitle }}</x-slot:title>

    <div class="min-h-screen bg-base-300">
        <x-app-navbar />

        @if (request()->routeIs('dashboard.admin.*'))
            <x-admin-subnav />
        @endif

        @if (request()->routeIs('dashboard.modules.show', 'dashboard.modules.participants', 'dashboard.modules.assignments.create'))
            @isset($module)
                <x-module-subnav :module="$module" />
            @endisset
        @endif

        <main class="mx-auto max-w-6xl p-4 md:p-6">
            <div class="rounded-box border border-base-300 bg-base-100 p-4 shadow-sm md:p-6">
                {{ $slot }}
            </div>
        </main>
    </div>
</x-layout>
