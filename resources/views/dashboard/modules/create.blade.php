<x-dashboard-layout>
    <x-slot:title>Create Module</x-slot:title>

    <section class="space-y-4">
        <header class="space-y-1">
            <a href="{{ route('dashboard.modules.index') }}" class="link link-primary text-sm">&larr; Back to Modules</a>
            <h2 class="text-2xl font-semibold">Create Module</h2>
            <p class="text-sm text-base-content/70">Add a new module and, optionally, its first members. You can add more members later.</p>
        </header>

        <article class="rounded-box border border-base-300 bg-base-100 p-6">
            <form class="flex max-w-2xl flex-col gap-5" method="POST" action="{{ route('dashboard.modules.store') }}">
                @csrf

                <label class="form-control w-full">
                    <span class="label-text mb-1 font-medium">Module name</span>
                    <input
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        class="input input-bordered w-full @error('name') input-error @enderror"
                        placeholder="e.g. Resume Workshop"
                        autocomplete="off"
                        required
                    />
                    @error('name')
                        <span class="label-text-alt mt-1 text-error">{{ $message }}</span>
                    @enderror
                </label>

                @foreach (['instructor_ids' => 'Instructors', 'student_ids' => 'Students'] as $fieldName => $fieldLabel)
                    <div class="space-y-1">
                        <span class="label-text font-medium">{{ $fieldLabel }}</span>
                        <livewire:user-picker-field :name="$fieldName" :key="$fieldName" />
                        @foreach ([$fieldName, $fieldName.'.*'] as $errorKey)
                            @error($errorKey)
                                <span class="block text-sm text-error">{{ $message }}</span>
                            @enderror
                        @endforeach
                    </div>
                @endforeach

                <div class="flex flex-wrap justify-end gap-2 pt-2">
                    <a href="{{ route('dashboard.modules.index') }}" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-primary">Create Module</button>
                </div>
            </form>
        </article>
    </section>
</x-dashboard-layout>
