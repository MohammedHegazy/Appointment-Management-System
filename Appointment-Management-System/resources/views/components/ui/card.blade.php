@props([
    'title' => null,
    'subtitle' => null,
])

<article {{ $attributes->class(['stat-card']) }}>
    @if ($title || $subtitle)
        <header class="ui-card__head">
            @if ($title)
                <h3>{{ $title }}</h3>
            @endif

            @if ($subtitle)
                <p class="hint">{{ $subtitle }}</p>
            @endif
        </header>
    @endif

    {{ $slot }}
</article>
