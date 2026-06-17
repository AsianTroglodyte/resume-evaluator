@props([
    'module',
])

<nav class="border-b border-base-300 bg-base-100" aria-label="Module sections">
    <div class="mx-auto max-w-6xl px-4 md:px-6">
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
    </div>
</nav>
