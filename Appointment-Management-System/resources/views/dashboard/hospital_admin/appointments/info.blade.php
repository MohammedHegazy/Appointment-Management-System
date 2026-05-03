@extends('layouts.dashboard')

@section('title', 'Appointment Details')

@section('content')
    <h2 class="dash-title">Appointment Details</h2>

    <x-ui.card title="Appointment Summary" class="dash-filter-card">
        <p><strong>Date:</strong> {{ $appointment->scheduled_at?->format('Y-m-d H:i') }}</p>
        <p><strong>Duration:</strong> {{ $appointment->duration_minutes ? $appointment->duration_minutes . ' min' : '-' }}</p>
        <p><strong>Type:</strong> {{ ucfirst($appointment->appointment_type->value) }}</p>
        <p><strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $appointment->status->value)) }}</p>
    </x-ui.card>

    <div class="patient-sections-grid">
        <x-ui.card title="Patient">
            <p><strong>Name:</strong> {{ $appointment->patient?->first_name }} {{ $appointment->patient?->last_name }}</p>
            <p><strong>Email:</strong> {{ $appointment->patient?->email }}</p>
        </x-ui.card>

        <x-ui.card title="Doctor">
            <p><strong>Name:</strong> {{ $appointment->doctor?->user?->first_name }} {{ $appointment->doctor?->user?->last_name }}</p>
            <p><strong>Doctor ID:</strong> {{ $appointment->doctor_id }}</p>
        </x-ui.card>
    </div>

    <x-ui.card title="Notes">
        <p>{{ $appointment->notes ?: 'No notes provided.' }}</p>
    </x-ui.card>

    <div class="ui-actions" style="margin-top: 14px;">
        <a class="ui-action-btn ui-action-btn--primary" href="{{ route('hospital-admin.appointments.update', $appointment) }}">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 17.25V21h3.75l11-11.03-3.75-3.75L3 17.25zm17.71-10.04a1.003 1.003 0 0 0 0-1.42L18.21 3.29a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 2-1.66z"/></svg>
            <span>Edit Appointment</span>
        </a>
        <a class="ui-action-btn ui-action-btn--ghost" href="{{ route('hospital-admin.appointments.index') }}">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
            <span>Back to list</span>
        </a>
    </div>
@endsection
