@props([
    'user',
])

<dl class="grid gap-4 text-sm sm:grid-cols-2">
    <div>
        <dt class="font-medium text-base-content/70">First name</dt>
        <dd class="mt-1">{{ $user->first_name }}</dd>
    </div>
    <div>
        <dt class="font-medium text-base-content/70">Last name</dt>
        <dd class="mt-1">{{ $user->last_name }}</dd>
    </div>
    <div class="sm:col-span-2">
        <dt class="font-medium text-base-content/70">Email</dt>
        <dd class="mt-1">{{ $user->email }}</dd>
    </div>
    <div>
        <dt class="font-medium text-base-content/70">Global role</dt>
        <dd class="mt-1">{{ ucfirst($user->global_role->value) }}</dd>
    </div>
    <div>
        <dt class="font-medium text-base-content/70">Email verified</dt>
        <dd class="mt-1">{{ $user->email_verified_at?->format('M j, Y g:i A') ?? 'Not verified' }}</dd>
    </div>
    <div>
        <dt class="font-medium text-base-content/70">Member since</dt>
        <dd class="mt-1">{{ $user->created_at?->format('M j, Y g:i A') ?? '—' }}</dd>
    </div>
    <div>
        <dt class="font-medium text-base-content/70">Last updated</dt>
        <dd class="mt-1">{{ $user->updated_at?->format('M j, Y g:i A') ?? '—' }}</dd>
    </div>
</dl>
