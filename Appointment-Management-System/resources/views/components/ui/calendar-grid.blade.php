@props([
    'days' => [],
    'entriesByDay' => [],
    'empty' => 'No slots',
])

<div {{ $attributes->class(['calendar-grid']) }}>
    @foreach ($days as $day)
        @php
            $dayKey = (int) ($day['key'] ?? 0);
            $entries = $entriesByDay[$dayKey] ?? [];
            $slotCount = count($entries);
        @endphp
        <section class="calendar-grid__day">
            <header class="calendar-grid__day-head">
                <h4>{{ $day['label'] ?? 'Day' }}</h4>
                <span class="calendar-grid__count">{{ $slotCount }}</span>
            </header>
            <div class="calendar-grid__day-body">
                @forelse ($entries as $entry)
                    <article class="calendar-slot">
                        <p class="calendar-slot__title">{{ $entry['title'] ?? '-' }}</p>
                        <p class="calendar-slot__time">{{ $entry['time'] ?? '-' }}</p>
                        @if (!empty($entry['status']))
                            <span class="status-pill {{ ($entry['status'] === 'Available') ? 'is-active' : 'is-inactive' }}">
                                {{ $entry['status'] }}
                            </span>
                        @endif
                    </article>
                @empty
                    <p class="calendar-grid__empty">{{ $empty }}</p>
                @endforelse
            </div>
        </section>
    @endforeach
</div>
