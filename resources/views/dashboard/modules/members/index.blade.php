@php
    use App\Enums\RoleInModule;
@endphp

<x-dashboard-layout>
<x-slot:title>{{ $module->name }} — Participants</x-slot:title>

<section class="space-y-6">
    <x-module-header :module="$module" />
    <div>
        <div class="flex flex-row justify-between">
            <p class="mb-4 text-sm text-base-content/70">Members enrolled in this module.</p>

            <button type="button" class="btn btn-primary btn-sm shrink-0" onclick="add_new_member.showModal()">
                Add member
            </button>
            <dialog id="add_new_member" class="modal">
                <div class="modal-box w-[92vw] max-w-3xl">
                    <form method="POST" action="{{ route('dashboard.modules.members.store', $module) }}">
                        @csrf

                        <button
                            type="button"
                            class="btn btn-sm btn-circle btn-outline absolute right-2 top-2"
                            onclick="add_new_member.close()"
                            aria-label="Close"
                        >
                            x
                        </button>

                        <header class="space-y-1">
                            <h3 class="text-2xl font-bold text-primary">Add member</h3>
                        </header>

                        <fieldset class="mt-4 flex flex-col gap-5">
                            <label class="form-control">
                                <span class="label-text mb-1">Find email</span>
                                <input
                                    type="email"
                                    name="new_member_email"
                                    value="{{ old('new_member_email') }}"
                                    placeholder="new_participant@southern.edu"
                                    class="input input-bordered w-full @error('new_member_email') input-error @enderror"
                                    required
                                />
                                @error('new_member_email')
                                    <span class="label-text-alt mt-1 text-error">{{ $message }}</span>
                                @enderror
                            </label>
                            <label class="form-control w-full">
                                <span class="label-text mb-1 font-medium">Role in Module</span>
                                <select
                                    name="role_in_module"
                                    class="select select-bordered w-full"
                                    required
                                >
                                    <option value="{{ RoleInModule::Student->value }}" selected>Student</option>
                                    <option value="{{ RoleInModule::Instructor->value }}">Instructor</option>
                                </select>
                                @error('role_in_module')
                                    <span class="label-text-alt mt-1 text-error">{{ $message }}</span>
                                @enderror

                            </label>
                            <button type="submit" class="btn btn-primary">
                                Add member
                            </button>
                        </fieldset>

                    </form>
                </div>
                <form method="dialog" class="modal-backdrop">
                    <button type="submit">close</button>
                </form>
            </dialog>
        </div>
        @if ($errors->any())
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif


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
                        <td>
                        {{-- <form method="DELETE" action="{{route('dashboard.modules.members.destroy', $module)}}">
                            @csrf
                            @method('DELETE') --}}
                            <button type="button" class="btn btn-outline btn-xs btn-error" 
                                onclick="delete_member_{{$member->id}}.showModal()">
                                {{-- <span class="sr-only">Delete</span> --}}
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </button>

                            <dialog id="delete_member_{{ $member->id }}" class="modal">
                                <div class="modal-box w-[92vw] max-w-3xl">
                                    <form method="POST" action="{{ route('dashboard.modules.members.destroy', $module) }}">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="user_id" value="{{ $member->id }}">
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-circle btn-outline absolute right-2 top-2"
                                            onclick="delete_member_{{ $member->id }}.close()"
                                            aria-label="Close">
                                            x
                                        </button>
                
                                        <header class="space-y-1">
                                            <h3 class="text-2xl font-bold text-primary">Remove member</h3>
                                        </header>
                                        <p>
                                            Are you sure you want to remove 
                                            {{ ucfirst($member->first_name) }} {{ ucfirst($member->last_name) }}
                                            from the module?
                                        </p>
                
                                        <fieldset class="mt-4 flex flex-row gap-5">
                                            <button 
                                                type="button"
                                                class="btn btn-sm btn-outline"
                                                onclick="delete_member_{{ $member->id }}.close()"
                                                aria-label="cancel">
                                                Cancel
                                            </button>
                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-error"
                                                aria-label="remove">
                                                Remove member
                                            </button>
                                        </fieldset>
                                    </form>
                                </div>
                                <form method="dialog" class="modal-backdrop">
                                    <button type="submit">close</button>
                                </form>
                            </dialog>
                        </td>
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
