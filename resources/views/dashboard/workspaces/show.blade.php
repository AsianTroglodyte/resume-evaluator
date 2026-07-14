
<x-dashboard-layout>
    <x-slot:title>{{ $workspace->name }}</x-slot:title>

    <section class="space-y-6">
        <header data-workspace-rename data-original-name="{{ $workspace->name }}">
            <a
                href="{{ route('dashboard.workspaces.index') }}"
                class="text-sm text-base-content/60 hover:text-base-content">
                ← Back to workspaces
            </a>

            <div data-rename-view class="mt-2 flex max-w-xl items-center gap-2">
                <h1 data-rename-display class="text-2xl font-semibold">{{ $workspace->name }}</h1>
                <button
                    type="button"
                    class="btn btn-ghost btn-sm btn-square shrink-0"
                    data-rename-start
                    aria-label="Rename workspace">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                    </svg>
                </button>
            </div>

            <form data-rename-edit class="mt-2 hidden max-w-xl space-y-2"
                method="POST"
                action="{{ route('dashboard.workspaces.update', $workspace) }}">
                @csrf
                @method('PATCH')
                <label class="form-control w-full">
                    <span class="label-text mb-1 font-medium">Workspace name</span>
                    <input
                        type="text"
                        class="input input-bordered w-full"
                        name="workspace_name"
                        data-rename-input
                        value="{{ old('name', $workspace->name) }}"
                        placeholder="Workspace name"
                        autocomplete="off"
                        required
                        minlength="3" />
                </label>
                <div class="flex flex-wrap gap-2 mt-2">
                    {{-- Wire to PATCH route when rename is implemented --}}
                    <button type="submit" class="btn btn-primary btn-sm" data-rename-save>
                        Save
                    </button>
                    <button type="cancel" class="btn btn-outline btn-sm" data-rename-cancel>
                        Cancel
                    </button>
                </div>
            </form>

            @error('workspace_name')
                <span class="label-text-alt mt-1 text-error">{{ $message }}</span>
            @enderror

            <p class="mt-1 text-sm text-base-content/70">
                Upload a resume and optionally add a job description to run a practice evaluation.
            </p>
        </header>

        <section class="rounded-box border border-base-300 bg-base-100">
            <div class="border-b border-base-300 px-4 py-3 sm:px-6">
                <h2 class="font-semibold">New evaluation</h2>
                <p class="text-sm text-base-content/60">Resume file is required. Job description is optional.</p>
            </div>
            {{-- <form
                class="flex flex-col gap-4 px-4 py-5 sm:px-6"
                method="POST"
                enctype="multipart/form-data"
                action="{{ route('dashboard.workspaces.evaluations.store', $workspace) }}"
            >
            @csrf
            <label class="form-control w-full">
                <div class="label-text mb-1 font-medium">Resume file</div>
                <input
                    type="file"
                    name="resume_file"
                    class="file-input" />
                @error('resume_file')
                <span class="label-text-alt mt-1 text-error">{{ $message }}</span>
                @enderror
            </label>
            <label class="form-control w-full">
                <span class="label-text mb-1 font-medium">Job description <span class="font-normal text-base-content/50">(optional)</span></span>
                <textarea
                    name="job_description"
                    class="textarea textarea-bordered min-h-28 max-h-60 w-full text-sm"
                    placeholder="Paste a role description for targeted feedback and keyword analysis.">{{ session('job_description') }}</textarea>
                <span class="label-text-alt text-sm text-base-content/60">
                    Leave blank for a general quality evaluation without keyword analysis.
                </span>
            </label>
            <div class="flex justify-end">
                <button type="submit" class="btn btn-primary btn-sm">Run evaluation</button>
            </div>
            </form> --}}

            <form
                class="flex flex-col gap-4 px-4 py-5 sm:px-6"
                method="POST"
                enctype="multipart/form-data"
                action="{{ route('dashboard.workspaces.evaluations.store', $workspace) }}">
                @csrf
                <label class="form-control w-full">
                    <div class="label-text mb-1 font-medium">Resume file</div>
                    <textarea
                        name="resume_text"
                        class="textarea textarea-bordered min-h-28 max-h-60 w-full text-sm"
                        placeholder="Paste the text for you resume here."></textarea>
                    @error('resume_text')
                    <span class="label-text-alt mt-1 text-error">{{ $message }}</span>
                    @enderror
                </label>
                <label class="form-control w-full">
                    <span class="label-text mb-1 font-medium">Job description <span class="font-normal text-base-content/50">(optional)</span></span>
                    <textarea
                        name="job_description"
                        class="textarea textarea-bordered min-h-28 max-h-60 w-full text-sm"
                        placeholder="Paste a role description for targeted feedback and keyword analysis.">{{ session('job_description') }}</textarea>
                    <span class="label-text-alt text-sm text-base-content/60">
                        Leave blank for a general quality evaluation without keyword analysis.
                    </span>
                </label>
                <div class="flex justify-end">
                    <button
                        type="submit"
                        class="btn btn-primary btn-sm"
                        id="run-evaluation">Run evaluation</button>
                </div>
            </form>

        </section>

        <section class="space-y-4">
            <div class="px-1">
                <h2 class="font-semibold">Recent evaluations</h2>
                <p class="text-sm text-base-content/60">Showing the five most recent results for this workspace.</p>
            </div>

            @if (session('evaluation_error'))
            <p class="text-sm text-error">{{ session('evaluation_error') }}</p>
            @endif

            <livewire:workspace-evaluations :workspace="$workspace" />
        </section>

        <section class="rounded-box border border-error/40 bg-error/5 p-4">
            <div class="flex flex-col items-start gap-4">
                <div class="space-y-1">
                    <h2 class="font-medium text-error">Danger zone</h2>
                    <p class="text-sm text-base-content/70">
                        Deleting this workspace removes it and any practice evaluations stored in it. This cannot be undone.
                    </p>
                </div>

                <button
                    type="button"
                    class="btn btn-error btn-outline btn-sm shrink-0"
                    onclick="delete_workspace_{{ $workspace->id }}.showModal()">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    Delete workspace
                </button>
            </div>

            <dialog id="delete_workspace_{{ $workspace->id }}" class="modal">
                <div class="modal-box w-[92vw] max-w-lg">
                    <button
                        type="button"
                        class="btn btn-sm btn-circle btn-outline absolute right-2 top-2"
                        onclick="delete_workspace_{{ $workspace->id }}.close()"
                        aria-label="Close">
                        ×
                    </button>

                    <header class="space-y-1">
                        <h3 class="text-2xl font-bold text-primary">Delete workspace</h3>
                    </header>
                    <p class="mt-4 text-sm text-base-content/80">
                        Are you sure you want to delete <strong>{{ $workspace->name }}</strong>?
                        All practice evaluations in this workspace will be removed permanently.
                    </p>

                    <form
                        class="modal-action mt-6"
                        method="POST"
                        action="{{ route('dashboard.workspaces.destroy', $workspace) }}">
                        @csrf
                        @method('DELETE')
                        <input name='workspace' />
                        <button
                            type="button"
                            class="btn btn-outline"
                            onclick="delete_workspace_{{ $workspace->id }}.close()">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-error">
                            Delete workspace
                        </button>
                    </form>
                </div>
                <form method="dialog" class="modal-backdrop">
                    <button type="submit">close</button>
                </form>
            </dialog>
        </section>
    </section>

    <script>
        document.querySelectorAll('[data-workspace-rename]').forEach((root) => {
            const viewBlock = root.querySelector('[data-rename-view]');
            const editBlock = root.querySelector('[data-rename-edit]');
            const display = root.querySelector('[data-rename-display]');
            const input = root.querySelector('[data-rename-input]');
            const originalName = root.dataset.originalName;

            root.querySelector('[data-rename-start]').addEventListener('click', () => {
                viewBlock.classList.add('hidden');
                editBlock.classList.remove('hidden');
                input.value = display.textContent.trim();
                input.focus();
                input.select();
            });

            root.querySelector('[data-rename-cancel]').addEventListener('click', () => {
                input.value = originalName;
                editBlock.classList.add('hidden');
                viewBlock.classList.remove('hidden');
            });

            input.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    root.querySelector('[data-rename-cancel]').click();
                }
            });
        });
    </script>
</x-dashboard-layout>
