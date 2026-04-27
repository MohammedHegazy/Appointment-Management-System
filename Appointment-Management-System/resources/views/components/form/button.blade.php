@props([
    'type' => 'submit',
])

@php
    $classString = (string) ($attributes->get('class') ?? '');
    $isCustomActionButton = str_contains($classString, 'ui-action-btn');
@endphp

<button type="{{ $type }}" {{ $attributes->class([$isCustomActionButton ? '' : 'btn']) }}>
    {{ $slot }}
</button>
