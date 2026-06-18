<header class="space-y-4 border-b border-base-300 pb-4">
    <h2 class="text-2xl font-semibold">Admin</h2>

    <nav aria-label="Admin sections">
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
    </nav>
</header>
