@php
    $pageTitle = trim((string) ($title ?? 'Dashboard'));
@endphp

<x-layout>
    <x-slot:title>{{ $pageTitle }}</x-slot:title>

    <div class="min-h-screen bg-base-300">
        <x-app-navbar />

        <main class="mx-auto max-w-6xl p-4 md:p-6">
            <div class="rounded-box border border-base-300 bg-base-100 p-4 shadow-sm md:p-6">
                {{ $slot }}
            </div>
        </main>
    </div>
</x-layout>
