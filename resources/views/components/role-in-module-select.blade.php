@props([
    'id',
])

@php
    use App\Enums\RoleInModule;
@endphp

<div {{ $attributes->class(['form-control w-full']) }}>
    <label for="{{ $id }}" class="label-text mb-1 w-fit font-medium">Role in module</label>
    <select
        id="{{ $id }}"
        name="roleInModule"
        wire:model="roleInModule"
        class="select select-bordered w-full @error('roleInModule') select-error @enderror"
        required
    >
        @foreach (RoleInModule::cases() as $role)
            <option value="{{ $role->value }}">
                {{ ucfirst($role->value) }}
            </option>
        @endforeach
    </select>
    @error('roleInModule')
        <span class="label-text-alt mt-1 text-error">{{ $message }}</span>
    @enderror
</div>
