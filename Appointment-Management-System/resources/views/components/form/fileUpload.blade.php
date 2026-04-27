@props([
    'name',
    'label',
    'accept' => null,
    'required' => false,
    'hint' => null,
])

@php
    $hasError = $errors->has($name);
    $inputId = $attributes->get('id', $name);
@endphp

<div class="field">
    <label for="{{ $inputId }}">{{ $label }}</label>

    <div
        class="file-upload js-file-upload @if($hasError) is-invalid @endif"
        data-target="#{{ $inputId }}"
    >
        <input
        id="{{ $inputId }}"
        name="{{ $name }}"
        type="file"
        class="file-upload__input"
        @required($required)
        @if($accept) accept="{{ $accept }}" @endif
        {{ $attributes->except('id') }}
        >

        <button type="button" class="file-upload__dropzone js-file-upload-trigger">
            <span class="file-upload__title">Drag & drop file here</span>
            <span class="file-upload__subtitle">or click to choose</span>
            <span class="file-upload__meta js-file-upload-name">No file selected</span>
        </button>
    </div>

    @if ($hint)
        <p class="hint file-upload__hint">{{ $hint }}</p>
    @endif
    @error($name)
        <p class="field-error">{{ $message }}</p>
    @enderror
</div>
