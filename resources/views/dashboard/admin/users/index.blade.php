<x-dashboard-layout>
    <x-slot:title>Admin</x-slot:title>

    <section class="space-y-6">
        <x-admin-header />

        <div>
            <p class="mb-4 text-sm text-base-content/70">View and manage platform accounts.</p>
            <div class="pb-5">
                {{ $users->links() }}
            </div>
            <div class="overflow-x-auto rounded-box border border-base-300">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Global role</th>
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
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-base-content/70">No users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="pt-5">
                {{ $users->links() }}
            </div>
        </div>
    </section>
</x-dashboard-layout>
