@props([
    'name',
    'label',
    'rows' => 3,
    'required' => false,
    'value' => null,
])

@php
    $hasError = $errors->has($name);
@endphp

<div class="field">
    <label for="{{ $name }}">{{ $label }}</label>
    <textarea
        id="{{ $name }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        @required($required)
        {{ $attributes->class(['field-textarea', 'is-invalid' => $hasError]) }}
    >{{ old($name, $value) }}</textarea>
    @error($name)
        <p class="field-error">{{ $message }}</p>
    @enderror
</div>
