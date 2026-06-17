<nav class="border-b border-base-300 bg-base-100" aria-label="Admin sections">
    <div class="mx-auto max-w-6xl px-4 md:px-6">
        <div role="tablist" class="tabs tabs-border">
            <a
                role="tab"
                href="{{ route('dashboard.admin.users.index') }}"
                class="tab {{ request()->routeIs('dashboard.admin.users.*') ? 'tab-active' : '' }}"
                aria-current="{{ request()->routeIs('dashboard.admin.users.*') ? 'page' : 'false' }}"
            >
                Users
            </a>
        </div>
    </div>
</nav>
