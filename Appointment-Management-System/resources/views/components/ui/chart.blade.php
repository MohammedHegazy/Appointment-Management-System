@props([
    'title',
    'subtitle' => null,
    'type' => 'line',
    'labels' => [],
    'values' => [],
    'height' => 140,
    'datasetLabel' => null,
])

@php
    $chartConfig = [
        'type' => $type,
        'data' => [
            'labels' => array_values($labels),
            'datasets' => [[
                'label' => $datasetLabel ?? $title,
                'data' => array_values($values),
                'borderColor' => '#0072ff',
                'backgroundColor' => [
                    'rgba(0, 114, 255, 0.7)',
                    'rgba(64, 92, 128, 0.7)',
                    'rgba(10, 143, 122, 0.7)',
                    'rgba(0, 92, 204, 0.7)',
                    'rgba(111, 66, 193, 0.7)',
                    'rgba(220, 53, 69, 0.7)',
                ],
                'tension' => 0.35,
                'fill' => $type === 'line',
                'pointRadius' => 3,
            ]],
        ],
    ];
@endphp

<x-ui.card :title="$title" :subtitle="$subtitle" {{ $attributes->class(['dash-chart-card']) }}>
    <div class="dash-chart">
        <canvas
            class="js-chart"
            data-chart='@json($chartConfig)'
            height="{{ $height }}"
        ></canvas>
    </div>
</x-ui.card>
