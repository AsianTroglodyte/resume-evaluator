<x-dashboard-layout>
    <x-slot:title>{{ $module->name }} — Participants</x-slot:title>

    <section class="space-y-6">
        <x-module-header :module="$module" />

        <div>
            <p class="mb-4 text-sm text-base-content/70">Members enrolled in this module.</p>

            <div class="overflow-x-auto rounded-box border border-base-300">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Module role</th>
                            <th>Status</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($participants as $participant)
                            <tr>
                                <td>{{ $participant->first_name }} {{ $participant->last_name }}</td>
                                <td>{{ $participant->email }}</td>
                                <td>{{ ucfirst($participant->pivot->role_in_module) }}</td>
                                <td>{{ ucfirst($participant->pivot->status) }}</td>
                                <td>{{ $participant->pivot->joined_at ? \Illuminate\Support\Carbon::parse($participant->pivot->joined_at)->format('M j, Y') : '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-base-content/70">No participants in this module yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</x-dashboard-layout>
