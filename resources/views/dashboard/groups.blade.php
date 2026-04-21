@php
    $groups = [
        [
            'id' => 1,
            'name' => 'Senior Seminar W25',
            'shared_count' => 2,
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
            'shared_count' => 2,
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
            'shared_count' => 0,
            'pending_assignments' => 0,
            'resumes' => [],
        ],
    ];
@endphp
<x-dashboard-layout>
    <x-slot:title>Groups</x-slot:title>

    <section class="space-y-4">
        <header>
            <h2 class="text-2xl font-semibold">Groups</h2>
            <p class="text-sm text-base-content/70">Manage shared resumes and track assignment status by group.</p>
        </header>

        <div class="grid gap-4 md:grid-cols-2">
            @foreach ($groups as $group)
                @php $modalId = 'group_modal_' . $group['id']; @endphp
                <article class="rounded-box border border-base-300 bg-base-100 p-4 "
                onclick="{{ $modalId }}.showModal()">
                    <h3 class="text-lg font-semibold">{{ $group['name'] }}</h3>
                    <p class="mt-1 text-sm text-base-content/70">
                        Shared {{ $group['shared_count'] }} resumes ·
                        {{ $group['pending_assignments'] }} assignment pending
                    </p>

                    <dialog id="{{ $modalId }}" class="modal">
                        <div class="modal-box max-w-2xl">
                            <h3 class="text-lg font-bold">{{ $group['name'] }}</h3>
                            <p class="py-2 text-sm text-base-content/70">
                                Resume performance in this group.
                            </p>

                            <div class="overflow-x-auto rounded-box border border-base-300">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Resume</th>
                                            <th>ATS Friendliness</th>
                                            <th>Keyword Match</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($group['resumes'] as $resume)
                                            <tr>
                                                <td>{{ $resume['name'] }}</td>
                                                <td>{{ $resume['ats_friendliness'] }}%</td>
                                                <td>{{ $resume['keyword_match'] }}%</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-base-content/70">No resumes in this group yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="modal-action">
                                <form method="dialog">
                                    <button class="btn">Close</button>
                                </form>
                            </div>
                        </div>
                    </dialog>
                </article>
            @endforeach
        </div>
    </section>
</x-dashboard-layout>