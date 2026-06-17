<x-dashboard-layout>
    <x-slot:title>Create Module</x-slot:title>

    <section class="space-y-4">
        <header class="space-y-1">
            <a href="{{ route('dashboard.modules.index') }}" class="link link-primary text-sm">&larr; Back to Modules</a>
            <h2 class="text-2xl font-semibold">Create Module</h2>
            <p class="text-sm text-base-content/70">Add a new module. You can invite members after it is created.</p>
        </header>

        <article class="rounded-box border border-base-300 bg-base-100 p-6">
            <form class="flex max-w-xl flex-col gap-5" method="POST" action="{{ route('dashboard.modules.store') }}">
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

                <div class="flex flex-wrap justify-end gap-2 pt-2">
                    <a href="{{ route('dashboard.modules.index') }}" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary">Create Module</button>
                </div>
            </form>
        </article>
    </section>
</x-dashboard-layout>
