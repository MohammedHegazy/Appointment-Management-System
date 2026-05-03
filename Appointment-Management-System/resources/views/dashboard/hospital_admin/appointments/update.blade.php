@extends('layouts.dashboard')

@section('title', 'Update Appointment')

@section('content')
    <h2 class="dash-title">Update Appointment</h2>

    <x-ui.card title="Appointment Information" class="dash-filter-card">
        <form method="POST" action="{{ route('hospital-admin.appointments.update.submit', $appointment) }}">
            @csrf

            <div class="row">
                <x-form.select name="doctor_id" label="Doctor" required>
                    @foreach ($doctors as $doctor)
                        <option value="{{ $doctor->id }}" @selected((string) old('doctor_id', $appointment->doctor_id) === (string) $doctor->id)>
                            {{ $doctor->user?->first_name }} {{ $doctor->user?->last_name }}
                        </option>
                    @endforeach
                </x-form.select>

                <x-form.select name="appointment_type" label="Type" required>
                    @foreach ($types as $type)
                        <option value="{{ $type->value }}" @selected(old('appointment_type', $appointment->appointment_type->value) === $type->value)>
                            {{ ucfirst($type->value) }}
                        </option>
                    @endforeach
                </x-form.select>
            </div>

            <div class="row">
                <x-form.input name="scheduled_at" label="Scheduled At" type="datetime-local" :value="old('scheduled_at', optional($appointment->scheduled_at)->format('Y-m-d\TH:i'))" required />
                <x-form.input name="duration_minutes" label="Duration (minutes)" type="number" :value="old('duration_minutes', $appointment->duration_minutes)" min="1" max="480" />
            </div>

            <x-form.select name="status" label="Status" required>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(old('status', $appointment->status->value) === $status->value)>
                        {{ ucfirst(str_replace('_', ' ', $status->value)) }}
                    </option>
                @endforeach
            </x-form.select>

            <x-form.textarea name="notes" label="Notes" rows="4" :value="old('notes', $appointment->notes)" />

            <div class="ui-actions">
                <x-form.button type="submit" class="ui-action-btn ui-action-btn--primary">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M17 3H5a2 2 0 0 0-2 2v14l4-4h10a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2z"/></svg>
                    <span>Save Changes</span>
                </x-form.button>
                <a class="ui-action-btn ui-action-btn--ghost" href="{{ route('hospital-admin.appointments.info', $appointment) }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
                    <span>Cancel</span>
                </a>
            </div>
        </form>
    </x-ui.card>
@endsection
