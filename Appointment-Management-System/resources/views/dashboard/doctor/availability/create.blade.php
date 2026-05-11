@extends('layouts.dashboard')

@section('title', 'Create Availability Slot')

@section('content')
    <h2 class="dash-title">Create Availability Slot</h2>

    <x-ui.card title="Slot Details" class="dash-filter-card">
        <form method="POST" action="{{ route('doctor.availability.create.submit') }}">
            @csrf

            <div class="row">
                <x-form.select name="day_of_week" label="Day" required>
                    @foreach ($dayLabels as $value => $label)
                        <option value="{{ $value }}" @selected((string) old('day_of_week') === (string) $value)>{{ $label }}</option>
                    @endforeach
                </x-form.select>

                <x-form.select name="appointment_type" label="Appointment Type" required>
                    @foreach ($appointmentTypes as $type)
                        <option value="{{ $type->value }}" @selected(old('appointment_type') === $type->value)>{{ ucfirst($type->value) }}</option>
                    @endforeach
                </x-form.select>
            </div>

            <div class="row">
                <x-form.input name="start_time" label="Start Time" type="time" :value="old('start_time')" required />
                <x-form.input name="end_time" label="End Time" type="time" :value="old('end_time')" required />
            </div>

            <x-form.select name="is_available" label="Availability" required>
                <option value="1" @selected(old('is_available', '1') === '1')>Available</option>
                <option value="0" @selected(old('is_available') === '0')>Unavailable</option>
            </x-form.select>

            <div class="ui-actions">
                <x-form.button type="submit" class="ui-action-btn ui-action-btn--primary">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 11h-6V5h-2v6H5v2h6v6h2v-6h6z"/></svg>
                    <span>Create Slot</span>
                </x-form.button>
                <a class="ui-action-btn ui-action-btn--ghost" href="{{ route('doctor.availability.index') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
                    <span>Cancel</span>
                </a>
            </div>
        </form>
    </x-ui.card>
@endsection

