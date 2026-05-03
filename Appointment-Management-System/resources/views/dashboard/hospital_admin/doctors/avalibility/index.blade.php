@extends('layouts.dashboard')

@section('title', 'Doctor Availability')

@section('content')
    <h2 class="dash-title">Availability - {{ $doctor->user?->first_name }} {{ $doctor->user?->last_name }}</h2>

    <div class="ui-actions" style="margin-bottom: 14px;">
        <a class="ui-action-btn ui-action-btn--primary" href="{{ route('hospital-admin.doctors.availability.create', $doctor) }}">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 11h-6V5h-2v6H5v2h6v6h2v-6h6z"/></svg>
            <span>Add Slot</span>
        </a>
        <a class="ui-action-btn ui-action-btn--ghost" href="{{ route('hospital-admin.doctors.info', $doctor) }}">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
            <span>Back to Doctor</span>
        </a>
    </div>

    <x-ui.card title="Weekly Calendar Grid" class="dash-filter-card">
        <x-ui.calendar-grid :days="$calendarDays" :entries-by-day="$entriesByDay" empty="No slots" />
    </x-ui.card>

    <x-ui.table
        :headers="['Day', 'Time', 'Type', 'Availability', 'Actions']"
        :has-rows="$availabilities->isNotEmpty()"
        empty="No availability slots found."
        :colspan="5"
    >
        @foreach ($availabilities as $slot)
            <tr>
                <td>{{ $dayLabels[(int) $slot->day_of_week] ?? '-' }}</td>
                <td>{{ substr((string) $slot->start_time, 0, 5) }} - {{ substr((string) $slot->end_time, 0, 5) }}</td>
                <td>{{ ucfirst($slot->appointment_type->value) }}</td>
                <td>
                    <span class="status-pill {{ $slot->is_available ? 'is-active' : 'is-inactive' }}">
                        {{ $slot->is_available ? 'Available' : 'Unavailable' }}
                    </span>
                </td>
                <td>
                    <div class="ui-actions">
                        <a class="ui-action-btn ui-action-btn--primary ui-action-btn--sm" href="{{ route('hospital-admin.doctors.availability.update', [$doctor, $slot]) }}">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 17.25V21h3.75l11-11.03-3.75-3.75L3 17.25zm17.71-10.04a1.003 1.003 0 0 0 0-1.42L18.21 3.29a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 2-1.66z"/></svg>
                            <span>Edit</span>
                        </a>
                        <form method="POST" action="{{ route('hospital-admin.doctors.availability.delete', [$doctor, $slot]) }}" onsubmit="return confirm('Delete this availability slot?')">
                            @csrf
                            <button type="submit" class="ui-action-btn ui-action-btn--danger ui-action-btn--sm">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 7h12l-1 14H7L6 7zm3-3h6l1 2H8l1-2z"/></svg>
                                <span>Delete</span>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        @endforeach
    </x-ui.table>
@endsection
