@extends('layouts.dashboard')

@section('title', 'Hospital Doctors')

@section('content')
    <h2 class="dash-title">Doctors · {{ $hospital->name }}</h2>

    <div class="ui-actions" style="margin-bottom: 14px;">
        <a class="ui-action-btn ui-action-btn--ghost" href="{{ route('admin.hospitals.users.index', $hospital) }}">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 11c1.66 0 2.99-1.79 2.99-4S17.66 3 16 3s-3 1.79-3 4 1.34 4 3 4zm-8 0c1.66 0 2.99-1.79 2.99-4S9.66 3 8 3 5 4.79 5 7s1.34 4 3 4zm0 2c-2.33 0-7 1.17-7 3.5V21h14v-4.5C15 14.17 10.33 13 8 13zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.98 1.97 3.45V21h7v-4.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
            <span>Manage All Users</span>
        </a>
    </div>

    <x-ui.card class="dash-filter-card" title="Filters">
        <form method="GET" action="{{ route('admin.hospitals.doctors.index', $hospital) }}" class="filter-grid">
            <x-form.input
                name="search"
                label="Search by name/email/license"
                :value="request('search')"
                placeholder="doctor name or email"
            />
            <x-form.select name="status" label="Status">
                <option value="">All statuses</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                <option value="pending" @selected(request('status') === 'pending')>Pending</option>
            </x-form.select>
            <div></div>
            <x-form.button type="submit" class="ui-action-btn ui-action-btn--soft">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 18h4v-2h-4v2zm-7-8v2h18v-2H3zm3-6v2h12V4H6z"/></svg>
                <span>Filter</span>
            </x-form.button>
        </form>
    </x-ui.card>

    <x-ui.table
        :headers="['Doctor', 'Specialization', 'Status', 'Appointments', 'Actions']"
        :has-rows="$doctors->isNotEmpty()"
        empty="No doctors found in this hospital."
        :colspan="5"
    >
        @foreach ($doctors as $doctor)
            <tr>
                <td>
                    <strong>{{ $doctor->user?->first_name }} {{ $doctor->user?->last_name }}</strong><br>
                    <span class="mono">{{ $doctor->user?->email }}</span>
                </td>
                <td>{{ $doctor->specialization?->name ?: '-' }}</td>
                <td>{{ ucfirst($doctor->user?->status?->value ?? '-') }}</td>
                <td>{{ $doctor->appointments_count }}</td>
                <td>
                    <div class="ui-actions">
                        <a class="ui-action-btn ui-action-btn--soft ui-action-btn--sm" href="{{ route('admin.hospitals.doctors.info', [$hospital, $doctor]) }}">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5c-7 0-11 7-11 7s4 7 11 7 11-7 11-7-4-7-11-7zm0 11.5A4.5 4.5 0 1 1 12 7a4.5 4.5 0 0 1 0 9.5zm0-7A2.5 2.5 0 1 0 12 15a2.5 2.5 0 0 0 0-5z"/></svg>
                            <span>View</span>
                        </a>
                        <a class="ui-action-btn ui-action-btn--primary ui-action-btn--sm" href="{{ route('admin.hospitals.doctors.update', [$hospital, $doctor]) }}">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 17.25V21h3.75l11-11.03-3.75-3.75L3 17.25zm17.71-10.04a1.003 1.003 0 0 0 0-1.42L18.21 3.29a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 2-1.66z"/></svg>
                            <span>Edit</span>
                        </a>
                    </div>
                </td>
            </tr>
        @endforeach
    </x-ui.table>

    <div class="page-links">
        {{ $doctors->links() }}
    </div>
@endsection
