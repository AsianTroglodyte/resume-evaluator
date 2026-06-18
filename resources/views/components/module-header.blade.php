@props([
    'module',
])

<header class="space-y-4 border-b border-base-300 pb-4">
    {{-- <a href="{{ route('dashboard.modules.index') }}" class="link link-primary text-sm">&larr; Back to Modules</a> --}}

    <div class="flex flex-wrap items-center gap-3">
        <h2 class="text-2xl font-semibold">{{ $module->name }}</h2>
        <span @class([
            'badge badge-sm',
            'badge-neutral' => $module->status === 'Archived',
            'badge-success' => $module->status === 'active',
        ])>
            {{ ucfirst($module->status) }}
        </span>
    </div>

    <nav aria-label="Module sections">
        <div role="tablist" class="tabs tabs-border">
            <a
                role="tab"
                href="{{ route('dashboard.modules.show', $module) }}"
                class="tab {{ request()->routeIs('dashboard.modules.show') ? 'tab-active' : '' }}"
                aria-current="{{ request()->routeIs('dashboard.modules.show') ? 'page' : 'false' }}"
            >
                Overview
            </a>
            <a
                role="tab"
                href="{{ route('dashboard.modules.participants', $module) }}"
                class="tab {{ request()->routeIs('dashboard.modules.participants') ? 'tab-active' : '' }}"
                aria-current="{{ request()->routeIs('dashboard.modules.participants') ? 'page' : 'false' }}"
            >
                Participants
            </a>
        </div>
    </nav>
</header>
