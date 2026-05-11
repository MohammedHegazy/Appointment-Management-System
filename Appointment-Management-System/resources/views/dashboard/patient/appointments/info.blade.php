@extends('layouts.dashboard')

@section('title', 'Appointment Details')

@section('content')
    <h2 class="dash-title">Appointment Details</h2>

    <x-ui.card title="Appointment Summary" class="dash-filter-card">
        <p><strong>Date:</strong> {{ $appointment->scheduled_at?->format('Y-m-d H:i') }}</p>
        <p><strong>Duration:</strong> {{ $appointment->duration_minutes ? $appointment->duration_minutes . ' min' : '-' }}</p>
        <p><strong>Type:</strong> {{ ucfirst($appointment->appointment_type->value) }}</p>
        <p><strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $appointment->status->value)) }}</p>
        <p><strong>Notes:</strong> {{ $appointment->notes ?: '-' }}</p>
    </x-ui.card>

    <div class="patient-sections-grid">
        <x-ui.card title="Doctor Information">
            <p><strong>Name:</strong> {{ $appointment->doctor?->user?->first_name }} {{ $appointment->doctor?->user?->last_name }}</p>
            <p><strong>Email:</strong> {{ $appointment->doctor?->user?->email ?: '-' }}</p>
            <p><strong>Specialization:</strong> {{ $appointment->doctor?->specialization?->name ?: '-' }}</p>
            <p><strong>Bio:</strong> {{ $appointment->doctor?->bio ?: '-' }}</p>
        </x-ui.card>

        <x-ui.card title="Payment">
            <p><strong>Status:</strong> {{ ucfirst($appointment->payment?->status?->value ?? 'pending') }}</p>
            <p><strong>Method:</strong> {{ ucfirst($appointment->payment?->method?->value ?? '-') }}</p>
            <p><strong>Amount:</strong> {{ isset($appointment->payment?->amount) ? number_format((float) $appointment->payment->amount, 2) : '-' }}</p>
            <p><strong>Paid At:</strong> {{ $appointment->payment?->paid_at?->format('Y-m-d H:i') ?: '-' }}</p>
        </x-ui.card>
    </div>

    <x-ui.card title="Medical Notes Shared From Appointment">
        @php
            $currentRecord = $appointment->medicalRecords->first();
        @endphp
        <p><strong>Diagnosis:</strong> {{ $currentRecord?->diagnosis ?: '-' }}</p>
        <p><strong>Treatment:</strong> {{ $currentRecord?->treatment ?: '-' }}</p>
        <p><strong>Prescription:</strong> {{ $currentRecord?->prescription ?: '-' }}</p>
        <p><strong>Notes:</strong> {{ $currentRecord?->notes ?: '-' }}</p>
    </x-ui.card>

    <div class="ui-actions" style="margin-top: 14px;">
        @if (in_array($appointment->status->value, ['scheduled', 'confirmed', 'in_progress'], true))
            <form method="POST" action="{{ route('patient.appointments.cancel', $appointment) }}" style="width:100%;">
                @csrf
                <x-form.textarea name="cancel_note" label="Cancellation Note (optional)" rows="3" :value="old('cancel_note')" />
                <button type="submit" class="ui-action-btn ui-action-btn--danger" onclick="return confirm('Cancel this appointment?')">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
                    <span>Cancel Appointment</span>
                </button>
            </form>
        @endif

        <a class="ui-action-btn ui-action-btn--ghost" href="{{ route('patient.appointments.index') }}">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
            <span>Back to list</span>
        </a>
    </div>
@endsection
