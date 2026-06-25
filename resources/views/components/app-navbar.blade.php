@props([
    'actingUser' => null,
])

@php
    use App\Enums\GlobalRole;
    $actingUser = $actingUser ?? auth()->user();
    $isAdmin = $actingUser?->global_role === GlobalRole::Admin;
@endphp

<header class="border-b border-base-300 bg-base-100">
    <div class="navbar mx-auto max-w-6xl px-2 md:px-4">
        <div class="navbar-start">
            <div class="dropdown">
                <div tabindex="0"
                     role="button"
                     0class="btn btn-ghost lg:hidden"
                     1aria-label="Open navigation menu">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-5 w-5"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </div>
                <ul tabindex="0"
                    class="menu menu-sm dropdown-content bg-base-100 rounded-box
                              z-50 mt-3 w-52 border border-base-300 p-2 shadow"
                >
                    <li>
                        <a href="{{ route('dashboard.resumes.index') }}"
                           @class(['active' => request()->routeIs('dashboard.resumes.*')])>
                            Resumes
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('dashboard.modules.index') }}"
                           @class(['active' => request()->routeIs('dashboard.modules.*')])>
                            Modules
                        </a>
                    </li>
                    @if ($isAdmin)
                        <li>
                            <a href="{{ route('dashboard.admin.users.index') }}"
                               @class(['active' => request()->routeIs('dashboard.admin.*')])>
                                Admin
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
            <a href="{{ route('dashboard.resumes.index') }}"
               class="btn btn-ghost text-lg font-bold text-primary">
                Resume Matcher
            </a>
        </div>

        <div class="navbar-center hidden lg:flex">
            <ul class="menu menu-horizontal px-1">
                <li>
                    <a
                        href="{{ route('dashboard.resumes.index') }}"
                        @class(['font-medium', 'active' => request()->routeIs('dashboard.resumes.*')])
                        aria-current="{{ request()->routeIs('dashboard.resumes.*') ? 'page' : 'false' }}"
                    >
                        Resumes
                    </a>
                </li>
                <li>
                    <a
                        href="{{ route('dashboard.modules.index') }}"
                        @class(['font-medium', 'active' => request()->routeIs('dashboard.modules.*')])
                        aria-current="{{ request()->routeIs('dashboard.modules.*') ? 'page' : 'false' }}"
                    >
                        Modules
                    </a>
                </li>
                @if ($isAdmin)
                    <li>
                        <a
                            href="{{ route('dashboard.admin.users.index') }}"
                            @class(['font-medium', 'active' => request()->routeIs('dashboard.admin.*')])
                            aria-current="{{ request()->routeIs('dashboard.admin.*') ? 'page' : 'false' }}"
                        >
                            Admin
                        </a>
                    </li>
                @endif
            </ul>
        </div>

        <div class="navbar-end">
            @if ($actingUser)
                <div class="dropdown dropdown-end">
                    <div tabindex="0"
                         role="button"
                         class="btn btn-ghost btn-sm gap-2"
                         aria-label="User menu">
                        <span class="hidden sm:inline">
                            {{ $actingUser->first_name }} {{ $actingUser->last_name }}
                        </span>
                        <span class="badge badge-ghost badge-sm">
                            {{ ucfirst($actingUser->global_role->value) }}
                        </span>
                    </div>
                    <ul tabindex="0"
                        class="menu menu-sm dropdown-content bg-base-100 rounded-box z-50
                                  mt-3 w-52 border border-base-300 p-2 shadow">
                        <li>
                            <button type="submit" form="navbar-logout-form">
                                out
                            </button>
                        </li>
                        <li>
                            <button type="submit" form="navbar-logout-form">
                                Log out
                            </button>
                        </li>
                    </ul>
                    <form id="navbar-logout-form" method="POST" action="{{ route('logout.destroy') }}" class="hidden">
                        @csrf
                    </form>
                </div>
            @else
                <a href="{{ route('login') }}" class="btn btn-primary btn-sm">Sign in</a>
            @endif
        </div>
    </div>
</header>
