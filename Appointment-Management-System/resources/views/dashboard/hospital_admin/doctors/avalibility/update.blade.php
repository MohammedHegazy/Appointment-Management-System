@extends('layouts.dashboard')

@section('title', 'Update Availability Slot')

@section('content')
    <h2 class="dash-title">Update Availability Slot</h2>

    <x-ui.card title="Slot Details" class="dash-filter-card">
        <form method="POST" action="{{ route('hospital-admin.doctors.availability.update.submit', [$doctor, $availability]) }}">
            @csrf

            <div class="row">
                <x-form.select name="day_of_week" label="Day" required>
                    @foreach ($dayLabels as $value => $label)
                        <option value="{{ $value }}" @selected((string) old('day_of_week', $availability->day_of_week) === (string) $value)>{{ $label }}</option>
                    @endforeach
                </x-form.select>

                <x-form.select name="appointment_type" label="Appointment Type" required>
                    @foreach ($appointmentTypes as $type)
                        <option value="{{ $type->value }}" @selected(old('appointment_type', $availability->appointment_type->value) === $type->value)>{{ ucfirst($type->value) }}</option>
                    @endforeach
                </x-form.select>
            </div>

            <div class="row">
                <x-form.input name="start_time" label="Start Time" type="time" :value="old('start_time', substr((string) $availability->start_time, 0, 5))" required />
                <x-form.input name="end_time" label="End Time" type="time" :value="old('end_time', substr((string) $availability->end_time, 0, 5))" required />
            </div>

            <x-form.select name="is_available" label="Availability" required>
                <option value="1" @selected(old('is_available', $availability->is_available ? '1' : '0') === '1')>Available</option>
                <option value="0" @selected(old('is_available', $availability->is_available ? '1' : '0') === '0')>Unavailable</option>
            </x-form.select>

            <div class="ui-actions">
                <x-form.button type="submit" class="ui-action-btn ui-action-btn--primary">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M17 3H5a2 2 0 0 0-2 2v14l4-4h10a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2z"/></svg>
                    <span>Save Changes</span>
                </x-form.button>
                <a class="ui-action-btn ui-action-btn--ghost" href="{{ route('hospital-admin.doctors.availability.index', $doctor) }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
                    <span>Cancel</span>
                </a>
            </div>
        </form>
    </x-ui.card>
@endsection
