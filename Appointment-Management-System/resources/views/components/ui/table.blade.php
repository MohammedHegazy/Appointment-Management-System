@props([
    'headers' => [],
    'empty' => 'No data found.',
    'hasRows' => true,
    'colspan' => null,
])

@php
    $resolvedColspan = $colspan ?? max(count($headers), 1);
@endphp

<div class="table-wrap">
    <table {{ $attributes->class(['table']) }}>
        <thead>
            <tr>
                @foreach ($headers as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @if ($hasRows)
                {{ $slot }}
            @else
                <tr>
                    <td colspan="{{ $resolvedColspan }}">{{ $empty }}</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
