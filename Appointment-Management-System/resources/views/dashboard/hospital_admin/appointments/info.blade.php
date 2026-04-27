@extends('layouts.dashboard')

@section('title', 'Patient Details')

@section('content')
    <h2 class="dash-title">Patient Details</h2>

    <div class="dash-charts-grid">
        <x-ui.card title="Identity" class="dash-charts-grid__wide">
            <p><strong>Name:</strong> {{ $patient->first_name }} {{ $patient->last_name }}</p>
            <p><strong>Email:</strong> {{ $patient->email }}</p>
            <p><strong>Status:</strong> {{ ucfirst($patient->status->value) }}</p>
            <p><strong>Email Verified:</strong> {{ $patient->email_verified_at ? 'Yes' : 'No' }}</p>
            <p><strong>Joined:</strong> {{ $patient->created_at?->format('Y-m-d H:i') }}</p>
        </x-ui.card>

        <x-ui.card title="Medical Profile">
            <p><strong>Blood Type:</strong> {{ $patient->medicalProfile?->blood_type ?? '-' }}</p>
            <p><strong>Allergies:</strong> {{ $patient->medicalProfile?->allergies ?? '-' }}</p>
            <p><strong>Chronic Conditions:</strong> {{ $patient->medicalProfile?->chronic_conditions ?? '-' }}</p>
            <p><strong>Medications:</strong> {{ $patient->medicalProfile?->medications ?? '-' }}</p>
            <p><strong>Emergency Contact:</strong> {{ $patient->medicalProfile?->emergency_contact ?? '-' }}</p>
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
                    <td>{{ ucfirst(str_replace('_', ' ', $appointment->status->value)) }}</td>
                </tr>
            @endforeach
        </x-ui.table>
    </x-ui.card>

    <div class="ui-actions">
        <a class="ui-action-btn ui-action-btn--primary" href="{{ route('admin.patients.update', $patient) }}">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 17.25V21h3.75l11-11.03-3.75-3.75L3 17.25zm17.71-10.04a1.003 1.003 0 0 0 0-1.42L18.21 3.29a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 2-1.66z"/></svg>
            <span>Edit Patient</span>
        </a>
        <a class="ui-action-btn ui-action-btn--ghost" href="{{ route('admin.patients.index') }}">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
            <span>Back to list</span>
        </a>
    </div>
@endsection
