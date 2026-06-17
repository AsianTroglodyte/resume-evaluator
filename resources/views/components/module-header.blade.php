@props([
    'module',
])

<header class="space-y-1">
    <a href="{{ route('dashboard.modules.index') }}" class="link link-primary text-sm">&larr; Back to Modules</a>
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
</header>
