@props([
    'label',
    'name',
    'type' => 'text',
    'id' => null,
    'placeholder' => null,
    'required' => false,
    'autocomplete' => nulle,
    'value' => null,
])

<label {{$attributes->merge(['class' => "form-control w-full", 'for' => $id])}} >
    <span class="label-text mb-1"></span>
    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $id }}"
        value="{{ $value ?? old($name) }}"
        @if($placeholder) placeholder="{{ $placeholder }}" @endif
        @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif
        @if($required) required @endif
        {{ $attribues->except('class')->class([
            'input input-bordered w-full',
            'input-error' => $hasError
        ])}}

    />
    @error($name)
        <span class="text-error"> {{ $message }}</span>
    @enderror
</label>
