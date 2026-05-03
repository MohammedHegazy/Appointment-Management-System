@extends('layouts.dashboard')

@section('title', 'Patient Details')

@section('content')
    @php
        $statusClass = match ($patient->status->value) {
            'active' => 'is-active',
            'inactive' => 'is-inactive',
            default => 'is-pending',
        };
        $initials = strtoupper(substr($patient->first_name, 0, 1) . substr($patient->last_name, 0, 1));
    @endphp

    <x-shared.profile
        :title="$patient->first_name . ' ' . $patient->last_name"
        :subtitle="$patient->email"
        :avatar="$initials"
        :badges="[
            ['label' => ucfirst($patient->status->value), 'class' => $statusClass],
            ['label' => $patient->email_verified_at ? 'Email Verified' : 'Email Not Verified', 'class' => $patient->email_verified_at ? 'is-active' : 'is-pending'],
        ]"
    >
        <a class="ui-action-btn ui-action-btn--primary" href="{{ route('admin.patients.update', $patient) }}">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 17.25V21h3.75l11-11.03-3.75-3.75L3 17.25zm17.71-10.04a1.003 1.003 0 0 0 0-1.42L18.21 3.29a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 2-1.66z"/></svg>
            <span>Edit Patient</span>
        </a>
        <a class="ui-action-btn ui-action-btn--ghost" href="{{ route('admin.patients.index') }}">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
            <span>Back to list</span>
        </a>
    </x-shared.profile>

    <div class="patient-kpi-grid">
        <x-ui.card title="Total Recent Visits">
            <p class="dash-kpi-value">{{ $recentAppointments->count() }}</p>
        </x-ui.card>
        <x-ui.card title="Joined On">
            <p class="dash-kpi-value">{{ $patient->created_at?->format('Y-m-d') }}</p>
        </x-ui.card>
        <x-ui.card title="Emergency Contact">
            <p class="dash-kpi-value">{{ $patient->medicalProfile?->emergency_contact ?: '-' }}</p>
        </x-ui.card>
    </div>

    <div class="patient-sections-grid">
        <x-ui.card title="Identity">
            <dl class="data-list">
                <div><dt>First Name</dt><dd>{{ $patient->first_name }}</dd></div>
                <div><dt>Last Name</dt><dd>{{ $patient->last_name }}</dd></div>
                <div><dt>Email</dt><dd>{{ $patient->email }}</dd></div>
                <div><dt>Created At</dt><dd>{{ $patient->created_at?->format('Y-m-d H:i') }}</dd></div>
            </dl>
        </x-ui.card>

        <x-ui.card title="Medical Profile">
            <dl class="data-list">
                <div><dt>Blood Type</dt><dd>{{ $patient->medicalProfile?->blood_type ?? '-' }}</dd></div>
                <div><dt>Allergies</dt><dd>{{ $patient->medicalProfile?->allergies ?? '-' }}</dd></div>
                <div><dt>Chronic Conditions</dt><dd>{{ $patient->medicalProfile?->chronic_conditions ?? '-' }}</dd></div>
                <div><dt>Medications</dt><dd>{{ $patient->medicalProfile?->medications ?? '-' }}</dd></div>
                <div><dt>Emergency Contact</dt><dd>{{ $patient->medicalProfile?->emergency_contact ?? '-' }}</dd></div>
            </dl>
        </x-ui.card>
    </div>

    <x-ui.card title="Recent Appointments">
        <x-ui.table
            :headers="['Date', 'Doctor', 'Type', 'Status']"
            :has-rows="$recentAppointments->isNotEmpty()"
            empty="No appointments yet."
            :colspan="4"
        >
            @foreach ($recentAppointments as $appointment)
                <tr>
                    <td>{{ $appointment->scheduled_at?->format('Y-m-d H:i') }}</td>
                    <td>{{ $appointment->doctor?->user?->first_name }} {{ $appointment->doctor?->user?->last_name }}</td>
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
