@props([
    'name',
    'label',
    'required' => false,
])

@php
    $hasError = $errors->has($name);
@endphp

<div class="field">
    <label for="{{ $name }}">{{ $label }}</label>
    <select
        id="{{ $name }}"
        name="{{ $name }}"
        @required($required)
        {{ $attributes->class(['field-select', 'is-invalid' => $hasError]) }}
    >
        {{ $slot }}
    </select>
    @error($name)
        <p class="field-error">{{ $message }}</p>
    @enderror
</div>
