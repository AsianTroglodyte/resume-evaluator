@php
    $evaluations = [
        [
            'id' => 101,
            'name' => 'Resume 1',
            'ats_friendliness' => 90,
            'keyword_match' => null,
            'groups' => [
                ['id' => 1, 'name' => 'Group 1'],
            ],
        ],
        [
            'id' => 102,
            'name' => 'Senior Backend Engineer (Distributed Systems)',
            'ats_friendliness' => 67,
            'keyword_match' => 69,
            'groups' => [
                ['id' => 2, 'name' => 'Senior Seminar W25'],
                ['id' => 4, 'name' => 'Distributed Systems Cohort'],
            ],
        ],
        [
            'id' => 103,
            'name' => 'Frontend Developer - BlueWave Analytics',
            'ats_friendliness' => 50,
            'keyword_match' => 50,
            'groups' => [
                ['id' => 3, 'name' => 'Senior Seminar W24'],
            ],
        ],
    ];
@endphp

<x-dashboard-layout>
    <x-slot:title>Resumes</x-slot:title>

    <section>
        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-2xl font-semibold">Your Resumes</h2>
            </div>
            <button type="button" class="btn btn-primary btn-sm" onclick="new_evaluation_modal.showModal()">New Evaluation</button>

            <dialog id="new_evaluation_modal" class="modal">
                <div class="modal-box max-w-2xl border border-base-300 bg-base-100 p-0">

                    <div class="border-b border-base-300 px-6 py-4">
                        <h3 class="text-lg font-bold">New Evaluation</h3>
                        <p class="mt-1 text-sm text-base-content/70">Upload a resume and optionally include job context.</p>
                    </div>

                    <form class="flex flex-col gap-4 px-6 py-5" method="POST" action="#" enctype="multipart/form-data">
                        @csrf
                        <label class="form-control w-full">
                            <span class="label-text mb-1 font-medium">Evaluation Name</span>
                            <input type="text" class="input input-bordered w-full" placeholder="e.g. Summer Internship Resume Review" />
                        </label>

                        <label class="form-control w-full">
                            <span class="label-text mb-1 font-medium">Job Description</span>
                            <textarea class="textarea textarea-bordered min-h-28 max-h-60 w-full" placeholder="Paste role description (optional)"></textarea>
                            <span class="label-text-alt text-sm text-base-content/60">Required for keyword matching.</span>
                        </label>

                        <label class="form-control w-full">
                            <span class="label-text mb-1 font-medium">Resume File</span>
                            <input type="file" class="file-input file-input-bordered w-full" accept=".pdf,.doc,.docx" />
                            <span class="label-text-alt text-sm text-base-content/60">Accepted: PDF, DOC, DOCX. Max file size depends on server limits.</span>
                        </label>

                        <div class="modal-action mt-2">
                            <button type="button" class="btn btn-ghost" onclick="new_evaluation_modal.close()">Cancel</button>
                            <button type="submit" class="btn btn-primary">Create Evaluation</button>
                        </div>
                    </form>
                </div>
                <form method="dialog" class="modal-backdrop">
                    <button type="submit">close</button>
                </form>
            </dialog>
        </div>

        <div class="overflow-x-auto rounded-box border border-base-300 bg-base-100">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>ATS </br> Friendliness</th>
                        <th>Keyword </br> Match</th>
                        <th>Group/s</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($evaluations as $evaluation)
                    <tr
                        class="cursor-pointer hover:bg-base-200"
                        onclick="window.location.href='{{ route('dashboard.resumes.show', $evaluation['id']) }}'"
                    >
                        <td>
                            <div class="font-semibold">{{ $evaluation['name'] }}</div>
                        </td>

                        <td>
                            <div class="text-xs bg-primary/10 p-1 rounded-md inline-block">
                                {{ isset($evaluation['ats_friendliness']) ? $evaluation['ats_friendliness'] . '%' : 'N/A' }}
                            </div>
                        </td>

                        <td>
                            <div class="text-xs bg-primary/10 p-1 rounded-md inline-block">
                                {{ isset($evaluation['keyword_match']) ? $evaluation['keyword_match'] . '%' : 'N/A' }}
                            </div>
                        </td>

                        <td>
                            <div class="text-xs">
                                @if (count($evaluation['groups']) > 0)
                                    {{ collect($evaluation['groups'])->pluck('name')->join(', ') }}
                                @else
                                    Ungrouped
                                @endif
                            </div>
                        </td>

                        <td class="text-right">
                            <button type="button" class="btn btn-ghost btn-xs">
                                <span class="sr-only">Details</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <button type="button" class="btn btn-ghost btn-xs">
                                <span class="sr-only">Share/Submit</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z" />
                                        </svg>
                            </button>
                            <button type="button" class="btn btn-ghost btn-xs btn-error">
                                <span class="sr-only">Delete</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">No evaluations found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-dashboard-layout>