@extends('layouts.dashboard')

@section('title', 'Admin Overview')

@section('content')
    <h2 class="dash-title">Overview</h2>

    <div class="stats-grid">
        <x-ui.card title="Total Users">
            <p class="dash-kpi-value">{{ number_format($stats['users']) }}</p>
        </x-ui.card>
        <x-ui.card title="Total Hospitals">
            <p class="dash-kpi-value">{{ number_format($stats['hospitals']) }}</p>
        </x-ui.card>
        <x-ui.card title="Total Doctors">
            <p class="dash-kpi-value">{{ number_format($stats['doctors']) }}</p>
        </x-ui.card>
        <x-ui.card title="Total Appointments">
            <p class="dash-kpi-value">{{ number_format($stats['appointments']) }}</p>
        </x-ui.card>
        <x-ui.card title="Appointments Today">
            <p class="dash-kpi-value">{{ number_format($stats['appointments_today']) }}</p>
        </x-ui.card>
        <x-ui.card title="Total Audit Logs">
            <p class="dash-kpi-value">{{ number_format($stats['audit_logs']) }}</p>
        </x-ui.card>
    </div>

    <div class="dash-charts-grid">
        <x-ui.chart
            title="Appointments Trend"
            subtitle="Last 6 months by scheduled date"
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
            subtitle="Admins, doctors, hospitals, patients"
            type="bar"
            :labels="$charts['userRoles']['labels']"
            :values="$charts['userRoles']['values']"
            dataset-label="Users"
            :height="180"
        />
    </div>

    <div class="ui-actions">
        <a class="ui-action-btn ui-action-btn--soft" href="{{ route('admin.audit-logs.index') }}">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2zM7 7h10v2H7V7zm0 4h10v2H7v-2zm0 4h7v2H7v-2z"/></svg>
            <span>Open Audit Logs</span>
        </a>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('js/dashboard-charts.js') }}" defer></script>
@endpush
