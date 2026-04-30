{{-- @php
    $groups = [
        [
            'id' => 1,
            'name' => 'Senior Seminar W25',
            'status' => 'active',
            'pending_assignments' => 1,
            'resumes' => [
                [
                    'id' => 101,
                    'name' => 'Resume 1',
                    'ats_friendliness' => 90,
                    'keyword_match' => 67,
                ],
                [
                    'id' => 102,
                    'name' => 'Resume 2',
                    'ats_friendliness' => 82,
                    'keyword_match' => 71,
                ],
            ],
        ],
        [
            'id' => 2,
            'name' => 'Senior Seminar W24',
            'status' => 'completed',
            'pending_assignments' => 0,
            'resumes' => [
                [
                    'id' => 103,
                    'name' => 'Resume 1',
                    'ats_friendliness' => 90,
                    'keyword_match' => 50,
                ],
                [
                    'id' => 104,
                    'name' => 'Resume 2',
                    'ats_friendliness' => 76,
                    'keyword_match' => 63,
                ],
            ],
        ],
        [
            'id' => 3,
            'name' => 'Yeeh',
            'status' => 'active',
            'pending_assignments' => 0,
            'resumes' => [],
        ],
    ];
@endphp --}}
<x-dashboard-layout>
    <x-slot:title>Groups</x-slot:title>

    <section class="space-y-4">
        <header>
            <h2 class="text-2xl font-semibold">Groups</h2>
        </header>

        <div class="grid gap-4 md:grid-cols-2">
            @foreach ($groups as $group)
            <a class="rounded-box border border-base-300 bg-base-100 p-4 "
            href="/dashboard/groups/{{ $group["id"] }}">
                <h3 class="text-lg font-semibold">{{ $group['name'] }}</h3>
                <p class="mt-1 text-sm text-base-content/70">
                    Status: {{ ucfirst($group['status']) }} ·
                    {{ $group['pending_assignments'] }} assignment pending
                </p>
            </a>
            @endforeach
        </div>
    </section>
</x-dashboard-layout>