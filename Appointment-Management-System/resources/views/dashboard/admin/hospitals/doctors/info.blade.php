@extends('layouts.dashboard')

@section('title', 'Hospital Doctor Details')

@section('content')
    @php
        $doctorUser = $doctor->user;
        $statusClass = match ($doctorUser?->status?->value) {
            'active' => 'is-active',
            'inactive' => 'is-inactive',
            default => 'is-pending',
        };
        $initials = strtoupper(substr((string) $doctorUser?->first_name, 0, 1) . substr((string) $doctorUser?->last_name, 0, 1));
    @endphp

    <x-shared.profile
        :title="trim(($doctorUser?->first_name ?? '') . ' ' . ($doctorUser?->last_name ?? ''))"
        :subtitle="$doctorUser?->email"
        :avatar="$initials"
        :badges="[
            ['label' => ucfirst($doctorUser?->status?->value ?? 'pending'), 'class' => $statusClass],
            ['label' => ($doctor->certificate_file ? 'Certificate Uploaded' : 'No Certificate'), 'class' => ($doctor->certificate_file ? 'is-active' : 'is-pending')],
        ]"
    >
        <a class="ui-action-btn ui-action-btn--primary" href="{{ route('admin.hospitals.doctors.update', [$hospital, $doctor]) }}">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 17.25V21h3.75l11-11.03-3.75-3.75L3 17.25zm17.71-10.04a1.003 1.003 0 0 0 0-1.42L18.21 3.29a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 2-1.66z"/></svg>
            <span>Edit Doctor</span>
        </a>
        <a class="ui-action-btn ui-action-btn--ghost" href="{{ route('admin.hospitals.doctors.index', $hospital) }}">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
            <span>Back to doctors</span>
        </a>
    </x-shared.profile>

    <div class="patient-kpi-grid">
        <x-ui.card title="Recent Appointments"><p class="dash-kpi-value">{{ $recentAppointments->count() }}</p></x-ui.card>
        <x-ui.card title="Specialization"><p class="dash-kpi-value">{{ $doctor->specialization?->name ?: '-' }}</p></x-ui.card>
        <x-ui.card title="License Number"><p class="dash-kpi-value">{{ $doctor->license_number ?: '-' }}</p></x-ui.card>
    </div>

    <x-ui.card title="Recent Appointments">
        <x-ui.table
            :headers="['Date', 'Patient', 'Type', 'Status']"
            :has-rows="$recentAppointments->isNotEmpty()"
            empty="No appointments yet."
            :colspan="4"
        >
            @foreach ($recentAppointments as $appointment)
                <tr>
                    <td>{{ $appointment->scheduled_at?->format('Y-m-d H:i') }}</td>
                    <td>{{ $appointment->patient?->first_name }} {{ $appointment->patient?->last_name }}</td>
                    <td>{{ ucfirst($appointment->appointment_type->value) }}</td>
                    <td>
                        <span class="status-pill is-{{ str_replace('_', '-', $appointment->status->value) }}">
                            {{ ucfirst(str_replace('_', ' ', $appointment->status->value)) }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </x-ui.table>
    </x-ui.card>
@endsection
