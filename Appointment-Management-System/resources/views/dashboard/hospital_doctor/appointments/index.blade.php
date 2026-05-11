@extends('layouts.dashboard')

@section('title', 'My Appointments')

@section('content')
    <h2 class="dash-title">Appointments</h2>

    <x-ui.card class="dash-filter-card" title="Filters">
        <form method="GET" action="{{ route('hospital-doctor.appointments.index') }}" class="filter-grid">
            <x-form.input
                name="search"
                label="Search by patient"
                :value="request('search')"
                placeholder="patient name or email"
            />

            <x-form.select name="status" label="Status">
                <option value="">All statuses</option>
                <option value="scheduled" @selected(request('status') === 'scheduled')>Scheduled</option>
                <option value="confirmed" @selected(request('status') === 'confirmed')>Confirmed</option>
                <option value="in_progress" @selected(request('status') === 'in_progress')>In Progress</option>
                <option value="completed" @selected(request('status') === 'completed')>Completed</option>
                <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
                <option value="no_show" @selected(request('status') === 'no_show')>No Show</option>
            </x-form.select>

            <x-form.select name="appointment_type" label="Type">
                <option value="">All types</option>
                <option value="online" @selected(request('appointment_type') === 'online')>Online</option>
                <option value="onsite" @selected(request('appointment_type') === 'onsite')>Onsite</option>
            </x-form.select>

            <x-form.button type="submit" class="ui-action-btn ui-action-btn--soft">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 18h4v-2h-4v2zm-7-8v2h18v-2H3zm3-6v2h12V4H6z"/></svg>
                <span>Filter</span>
            </x-form.button>
        </form>
    </x-ui.card>

    <x-ui.table
        :headers="['Date', 'Patient', 'Type', 'Status', 'Notes', 'Actions']"
        :has-rows="$appointments->isNotEmpty()"
        empty="No appointments found."
        :colspan="6"
    >
        @foreach ($appointments as $appointment)
            <tr>
                <td>{{ $appointment->scheduled_at?->format('Y-m-d H:i') }}</td>
                <td>
                    <strong>{{ $appointment->patient?->first_name }} {{ $appointment->patient?->last_name }}</strong><br>
                    <span class="mono">{{ $appointment->patient?->email }}</span>
                </td>
                <td>{{ ucfirst($appointment->appointment_type->value) }}</td>
                <td>
                    <span class="status-pill is-{{ str_replace('_', '-', $appointment->status->value) }}">
                        {{ ucfirst(str_replace('_', ' ', $appointment->status->value)) }}
                    </span>
                </td>
                <td>{{ $appointment->notes ? \Illuminate\Support\Str::limit($appointment->notes, 60) : '-' }}</td>
                <td>
                    <div class="ui-actions">
                        <a class="ui-action-btn ui-action-btn--soft ui-action-btn--sm" href="{{ route('hospital-doctor.appointments.info', $appointment) }}">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5c-7 0-11 7-11 7s4 7 11 7 11-7 11-7-4-7-11-7zm0 11.5A4.5 4.5 0 1 1 12 7a4.5 4.5 0 0 1 0 9.5zm0-7A2.5 2.5 0 1 0 12 15a2.5 2.5 0 0 0 0-5z"/></svg>
                            <span>Open</span>
                        </a>
                    </div>
                </td>
            </tr>
        @endforeach
    </x-ui.table>

    <div class="page-links">
        {{ $appointments->links() }}
    </div>
@endsection
