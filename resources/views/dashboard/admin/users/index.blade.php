<x-dashboard-layout>
    <x-slot:title>Users</x-slot:title>

    <section class="space-y-4">
        <header class="space-y-1">
            <h2 class="text-2xl font-semibold">Users</h2>
            <p class="text-sm text-base-content/70">View and manage platform accounts.</p>
        </header>

        <div class="overflow-x-auto rounded-box border border-base-300">
            <table class="table">
                <thead>
                    <tr>
                        <th>First name</th>
                        <th>Last name</th>
                        <th>Email</th>
                        <th>Global role</th>
                        <th>Email verified</th>
                        <th>Created at</th>
                        <th>Updated at</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->first_name }}</td>
                            <td>{{ $user->last_name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->global_role }}</td>
                            <td>{{ $user->email_verified_at?->format('M j, Y g:i A') ?? 'No' }}</td>
                            <td>{{ $user->created_at?->format('M j, Y g:i A') }}</td>
                            <td>{{ $user->updated_at?->format('M j, Y g:i A') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-base-content/70">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-dashboard-layout>
