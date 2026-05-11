@extends('layouts.dashboard')

@section('title', 'Hospital Doctor Overview')

@section('content')
    <h2 class="dash-title">Overview</h2>

    <div class="stats-grid">
        <x-ui.card title="Total Appointments">
            <p class="dash-kpi-value">{{ number_format($stats['appointments']) }}</p>
        </x-ui.card>
        <x-ui.card title="Appointments Today">
            <p class="dash-kpi-value">{{ number_format($stats['appointments_today']) }}</p>
        </x-ui.card>
        <x-ui.card title="My Patients">
            <p class="dash-kpi-value">{{ number_format($stats['patients']) }}</p>
        </x-ui.card>
        <x-ui.card title="Completed">
            <p class="dash-kpi-value">{{ number_format($stats['completed']) }}</p>
        </x-ui.card>
        <x-ui.card title="Upcoming">
            <p class="dash-kpi-value">{{ number_format($stats['upcoming']) }}</p>
        </x-ui.card>
    </div>

    <div class="dash-charts-grid">
        <x-ui.chart
            title="Appointments Trend"
            subtitle="Last 6 months"
            type="line"
            :labels="$charts['appointmentsMonthly']['labels']"
            :values="$charts['appointmentsMonthly']['values']"
            dataset-label="Appointments"
            class="dash-charts-grid__wide"
        />

        <x-ui.chart
            title="Appointments by Status"
            subtitle="Current status distribution"
            type="doughnut"
            :labels="$charts['appointmentStatus']['labels']"
            :values="$charts['appointmentStatus']['values']"
            dataset-label="Appointments"
            :height="180"
        />
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('js/dashboard-charts.js') }}" defer></script>
@endpush
