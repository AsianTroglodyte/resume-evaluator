<x-dashboard-layout>
    <x-slot:title>Admin</x-slot:title>

    <section class="space-y-6">
        <x-admin-header />

        <div>
            <p class="mb-4 text-sm text-base-content/70">View and manage platform accounts.</p>

            <div class="overflow-x-auto rounded-box border border-base-300">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
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
                                <td>
                                    <a href="{{ route('user.show', $user) }}" class="link">
                                        {{ $user->first_name }} {{ $user->last_name }}
                                    </a>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->global_role }}</td>
                                <td>{{ $user->email_verified_at?->format('M j, Y g:i A') ?? 'No' }}</td>
                                <td>{{ $user->created_at?->format('M j, Y g:i A') }}</td>
                                <td>{{ $user->updated_at?->format('M j, Y g:i A') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-base-content/70">No users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</x-dashboard-layout>
