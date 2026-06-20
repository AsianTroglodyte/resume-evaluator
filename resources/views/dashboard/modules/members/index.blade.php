<x-dashboard-layout>
    <x-slot:title>{{ $module->name }} — Participants</x-slot:title>

    <section class="space-y-6">
        <x-module-header :module="$module" />
        <div>
            <div class="flex flex-row justify-between">
                <p class="mb-4 text-sm text-base-content/70">Members enrolled in this module.</p>

                <button type="button" class="btn btn-primary btn-sm shrink-0" onclick="add_new_member.showModal()">
                    add new member
                </button>
                <dialog id="add_new_member" class="modal">
                    <div class="modal-box w-[92vw] max-w-3xl">
                        <form method="POST" action="{{ route('dashboard.modules.members.store', $module) }}">
                            @csrf

                            <button
                                type="button"
                                class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2"
                                onclick="add_new_participant.close()"
                                aria-label="Close"
                            >
                                x
                            </button>

                            <header class="space-y-1">
                                <h3 class="text-2xl font-bold text-primary">Create </h3>
                            </header>

                            <fieldset class="mt-4 flex flex-col gap-5">
                                <label class="form-control">
                                    <span class="label-text mb-1">Find email</span>
                                    <input
                                        type="email"
                                        name="new_member_email"
                                        value="{{ old('name') }}"
                                        placeholder="new_participant@southern.edu"
                                        class="input input-bordered w-full @error('name') input-error @enderror"
                                        required
                                        email
                                    />
                                    @error('name')
                                        <span class="label-text-alt mt-1 text-error">{{ $message }}</span>
                                    @enderror
                                </label>
                                <label>
                                    <span class="label-text mb-1">Assign role</span>
                                    <select class"select
                                        name="role_in_module"
                                        value="{{ old('name') }}"
                                        placeholder="new_participant@southern.edu"
                                        class="select select-bordered w-full @error('name') input-error @enderror"
                                        required
                                    >
                                        <option value="" disabled selected>Pick a role</option>
                                        <option value="Instructor"> Instructor </option>
                                        <option value="Student"> Student</option>
                                    </select>
                                </label>
                                <button type="submit" class="btn btn-primary">Add new user</button>
                            </fieldset>
                        </form>
                    </div>
                    <form method="dialog" class="modal-backdrop">
                        <button type="submit">close</button>
                    </form>
                </dialog>


                <dialog id="create_job_listing_modal" class="modal">
                    <div class="modal-box w-[92vw] max-w-3xl">
                        <form method="POST" action="{{ route('dashboard.modules.job-listings.store', $module) }}">
                            @csrf

                            <button
                                type="button"
                                class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2"
                                onclick="create_job_listing_modal.close()"
                                aria-label="Close"
                            >
                                x
                            </button>

                            <header class="space-y-1">
                                <h3 class="text-2xl font-bold text-primary">Create Job Listing</h3>
                            </header>

                            <fieldset class="mt-4 flex flex-col gap-5">
                                <label class="form-control">
                                    <span class="label-text mb-1">Title</span>
                                    <input
                                        type="text"
                                        name="name"
                                        value="{{ old('name') }}"
                                        placeholder="Job Title"
                                        class="input input-bordered w-full @error('name') input-error @enderror"
                                        required
                                    />
                                    @error('name')
                                        <span class="label-text-alt mt-1 text-error">{{ $message }}</span>
                                    @enderror
                                </label>

                                <label class="form-control">
                                    <span class="label-text mb-1">Description</span>
                                    <textarea
                                        name="description"
                                        placeholder="Job description and requirements..."
                                        class="textarea textarea-bordered h-64 w-full @error('description') textarea-error @enderror"
                                        required
                                    >{{ old('description') }}</textarea>
                                    @error('description')
                                        <span class="label-text-alt mt-1 text-error">{{ $message }}</span>
                                    @enderror
                                </label>

                                <button type="submit" class="btn btn-primary">Create Job Listing</button>
                            </fieldset>
                        </form>
                    </div>
                    <form method="dialog" class="modal-backdrop">
                        <button type="submit">close</button>
                    </form>
                </dialog>
            </div>

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
                        @forelse ($members as $member)
                            <tr>
                                <td>{{ $member->first_name }} {{ $member->last_name }}</td>
                                <td>{{ $member->email }}</td>
                                <td>{{ ucfirst($member->pivot->role_in_module) }}</td>
                                <td>{{ ucfirst($member->pivot->status) }}</td>
                                <td>{{ $member->pivot->joined_at ? \Illuminate\Support\Carbon::parse($member->pivot->joined_at)->format('M j, Y') : '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-base-content/70">No members in this module yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</x-dashboard-layout>
