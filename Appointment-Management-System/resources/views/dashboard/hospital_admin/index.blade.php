@extends('layouts.dashboard')

@section('title', 'Hospital Admin Overview')

@section('content')
    <h2 class="dash-title">Overview</h2>

    <div class="stats-grid">
        <x-ui.card title="Hospital Admins">
            <p class="dash-kpi-value">{{ number_format($stats['hospital_admins']) }}</p>
        </x-ui.card>
        <x-ui.card title="Doctors">
            <p class="dash-kpi-value">{{ number_format($stats['doctors']) }}</p>
        </x-ui.card>
        <x-ui.card title="Treated Patients">
            <p class="dash-kpi-value">{{ number_format($stats['patients']) }}</p>
        </x-ui.card>
        <x-ui.card title="Total Appointments">
            <p class="dash-kpi-value">{{ number_format($stats['appointments']) }}</p>
        </x-ui.card>
        <x-ui.card title="Appointments Today">
            <p class="dash-kpi-value">{{ number_format($stats['appointments_today']) }}</p>
        </x-ui.card>
    </div>

    <div class="dash-charts-grid">
        <x-ui.chart
            title="Appointments Trend"
            subtitle="Last 6 months for your hospital"
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

        <x-ui.chart
            title="Users by Role"
            subtitle="Hospital admins, doctors, and patients"
            type="bar"
            :labels="$charts['userRoles']['labels']"
            :values="$charts['userRoles']['values']"
            dataset-label="Users"
            :height="180"
        />
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('js/dashboard-charts.js') }}" defer></script>
@endpush
