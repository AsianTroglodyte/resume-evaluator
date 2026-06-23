@props([
    'label',
    'name',
    'type' => 'text',
    'id' => null,
    'placeholder' => null,
    'required' => false,
    'autocomplete' => null,
    'value' => null,
])

@php
    $id ??= $name;
    $hasError = $errors->has($name);
    $isPassword = $type === 'password';
@endphp

<label class="form-control w-full" for="{{ $id }}">
    <span class="label-text mb-1">{{ $label }}</span>
    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $id }}"
        @unless($isPassword)
            value="{{ $value ?? old($name) }}"
        @endunless
        @if($placeholder) placeholder="{{ $placeholder }}" @endif
        @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif
        @required($required)
        {{ $attributes->class([
            'input input-bordered w-full',
            'input-error' => $hasError,
        ])}}
    />
    @error($name)
        <span class="label-text-alt mt-1 text-error">{{ $message }}</span>
    @enderror
</label>
