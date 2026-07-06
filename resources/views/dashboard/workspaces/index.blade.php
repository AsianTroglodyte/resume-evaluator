<x-dashboard-layout>
    <x-slot:title>Workspaces</x-slot:title>

    <section class="space-y-4">
        <header class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-2xl font-semibold">Your Workspaces</h2>
                <p class="mt-1 text-sm text-base-content/70">
                    Run resume evaluations and review evaluation history. Each evaluation stores the resume and job context you used.
                </p>
            </div>
            <button type="button" class="btn btn-primary btn-sm shrink-0" onclick="new_workspace_modal.showModal()">
                New Workspace
            </button>

            <dialog id="new_workspace_modal" 
                class="modal">
                <div class="modal-box max-w-lg border border-base-300 bg-base-100 p-0">
                    <div class="border-b border-base-300 px-6 py-4">
                        <h3 class="text-lg font-bold">New Workspace</h3>
                        <p class="mt-1 text-sm text-base-content/70">
                            Create a place to run evaluations and keep a history of results.
                        </p>
                    </div>

                    <form class="flex flex-col gap-4 px-6 py-5" method="POST" action="{{route('dashboard.workspaces.store')}}">
                        @csrf
                        <label class="form-control w-full">
                            <span class="label-text mb-1 font-medium">Workspace Name</span>
                            <input
                                type="text"
                                class="input input-bordered w-full"
                                name="workspace_name"
                                placeholder="e.g. Summer Internship Prep"
                                required
                                minlength="3"
                            />
                        </label>
                        @error('workspace_name')
                            <span class="label-text-alt mt-1 text-error">{{ $message }}</span>
                        @enderror
                        <div class="modal-action mt-2">
                            <button type="button" class="btn btn-outline" onclick="new_workspace_modal.close()">Cancel</button>
                            <button type="submit" class="btn btn-primary">Create Workspace</button>
                        </div>
                    </form>
                </div>
                <form method="dialog" class="modal-backdrop">
                    <button type="submit">close</button>
                </form>
            </dialog>
            @if ($errors->has('workspace_name'))
                <script>
                    document.getElementById('new_workspace_modal')?.showModal();
                </script>
            @endif
        </header>

        <div class="overflow-x-auto rounded-box border border-base-300 bg-base-100">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Updated</th>
                        {{-- <th class="text-right">Actions</th> --}}
                    </tr>
                </thead>
                <tbody>
                    @forelse ($workspaces as $workspace)
                        <tr class="relative hover:bg-base-200">
                            <td class="font-medium">
                                <a
                                    href="{{ route('dashboard.workspaces.show', $workspace) }}"
                                    class="absolute inset-0"
                                >
                                </a>
                                {{ $workspace->name }}
                            </td>
                            <td class="text-sm text-base-content/70 whitespace-nowrap">
                                {{ $workspace->updated_at->format('M j, Y g:i A') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-10 text-center text-base-content/60">
                                No workspaces yet. Create one to start running evaluations.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{--
        Full table (requires evaluations relationship + latest eval eager load):

        <table class="table">
            <thead>
                <tr>
                    <th>Workspace</th>
                    <th>Latest evaluation</th>
                    <th>Keywords</th>
                    <th>Updated</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($workspaces as $workspace)
                    <tr class="relative hover:bg-base-200">
                        ...
                    </tr>
                @empty
                    ...
                @endforelse
            </tbody>
        </table>
        --}}
    </section>
</x-dashboard-layout>
