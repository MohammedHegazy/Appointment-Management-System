@extends('layouts.dashboard')

@section('title', 'Appointment Workspace')

@section('content')
    <h2 class="dash-title">Appointment Workspace</h2>

    <x-ui.card title="Appointment Summary" class="dash-filter-card">
        <p><strong>Date:</strong> {{ $appointment->scheduled_at?->format('Y-m-d H:i') }}</p>
        <p><strong>Duration:</strong> {{ $appointment->duration_minutes ? $appointment->duration_minutes . ' min' : '-' }}</p>
        <p><strong>Type:</strong> {{ ucfirst($appointment->appointment_type->value) }}</p>
        <p><strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $appointment->status->value)) }}</p>
    </x-ui.card>

    <div class="patient-sections-grid">
        <x-ui.card title="Patient Information">
            <p><strong>Name:</strong> {{ $appointment->patient?->first_name }} {{ $appointment->patient?->last_name }}</p>
            <p><strong>Email:</strong> {{ $appointment->patient?->email }}</p>
        </x-ui.card>

        <x-ui.card title="Medical Profile">
            <p><strong>Blood Type:</strong> {{ $appointment->patient?->medicalProfile?->blood_type ?: '-' }}</p>
            <p><strong>Allergies:</strong> {{ $appointment->patient?->medicalProfile?->allergies ?: '-' }}</p>
            <p><strong>Chronic Conditions:</strong> {{ $appointment->patient?->medicalProfile?->chronic_conditions ?: '-' }}</p>
            <p><strong>Medications:</strong> {{ $appointment->patient?->medicalProfile?->medications ?: '-' }}</p>
            <p><strong>Emergency Contact:</strong> {{ $appointment->patient?->medicalProfile?->emergency_contact ?: '-' }}</p>
        </x-ui.card>
    </div>

    <x-ui.card title="Update Appointment + Medical Notes" class="dash-filter-card">
        @php
            $currentRecord = $appointment->medicalRecords->first();
        @endphp
        <form method="POST" action="{{ route('hospital-doctor.appointments.update', $appointment) }}">
            @csrf

            <x-form.select name="status" label="Status" required>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(old('status', $appointment->status->value) === $status->value)>
                        {{ ucfirst(str_replace('_', ' ', $status->value)) }}
                    </option>
                @endforeach
            </x-form.select>

            <x-form.textarea name="notes" label="Appointment Notes" rows="4" :value="old('notes', $appointment->notes)" />

            <div class="row">
                <x-form.textarea name="diagnosis" label="Diagnosis" rows="4" :value="old('diagnosis', $currentRecord?->diagnosis)" />
                <x-form.textarea name="treatment" label="Treatment Plan" rows="4" :value="old('treatment', $currentRecord?->treatment)" />
            </div>

            <div class="row">
                <x-form.textarea name="prescription" label="Prescription" rows="4" :value="old('prescription', $currentRecord?->prescription)" />
                <x-form.textarea name="medical_notes" label="Medical Record Notes" rows="4" :value="old('medical_notes', $currentRecord?->notes)" />
            </div>

            <div class="ui-actions">
                <x-form.button type="submit" class="ui-action-btn ui-action-btn--primary">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M17 3H5a2 2 0 0 0-2 2v14l4-4h10a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2z"/></svg>
                    <span>Save Notes</span>
                </x-form.button>
                <a class="ui-action-btn ui-action-btn--ghost" href="{{ route('hospital-doctor.appointments.index') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
                    <span>Back</span>
                </a>
            </div>
        </form>
    </x-ui.card>

    <x-ui.card title="Patient Medical History (Recent 10)">
        @if ($patientHistory->isEmpty())
            <p class="hint">No medical records found for this patient yet.</p>
        @else
            <div class="data-list">
                @foreach ($patientHistory as $record)
                    <div>
                        <dt>Appointment</dt>
                        <dd>
                            {{ optional($record->appointment?->scheduled_at)->format('Y-m-d H:i') ?: '-' }}
                            - Dr. {{ $record->appointment?->doctor?->user?->first_name }} {{ $record->appointment?->doctor?->user?->last_name }}
                        </dd>
                        <dt>Diagnosis</dt>
                        <dd>{{ $record->diagnosis ?: '-' }}</dd>
                        <dt>Treatment</dt>
                        <dd>{{ $record->treatment ?: '-' }}</dd>
                        <dt>Prescription</dt>
                        <dd>{{ $record->prescription ?: '-' }}</dd>
                        <dt>Notes</dt>
                        <dd>{{ $record->notes ?: '-' }}</dd>
                    </div>
                @endforeach
            </div>
        @endif
    </x-ui.card>
@endsection
