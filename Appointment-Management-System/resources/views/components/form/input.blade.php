@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'required' => false,
])

@php
    $hasError = $errors->has($name);
@endphp

<div class="field">
    <label for="{{ $name }}">{{ $label }}</label>
    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ old($name, $value) }}"
        @required($required)
        {{ $attributes->class(['field-input', 'is-invalid' => $hasError]) }}
    >
    @error($name)
        <p class="field-error">{{ $message }}</p>
    @enderror
</div>
