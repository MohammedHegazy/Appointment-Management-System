@extends('layouts.dashboard')

@section('title', 'Admin Patients')

@section('content')
    <h2 class="dash-title">Patients</h2>

    <x-ui.card class="dash-filter-card" title="Filters">
        <form method="GET" action="{{ route('admin.patients.index') }}" class="filter-grid">
            <x-form.input
                name="search"
                label="Search by name/email"
                :value="request('search')"
                placeholder="patient name or email"
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
        :headers="['Patient', 'Status', 'Email Verified', 'Appointments', 'Blood Type', 'Actions']"
        :has-rows="$patients->isNotEmpty()"
        empty="No patients found."
        :colspan="6"
    >
        @foreach ($patients as $patient)
            <tr>
                <td>
                    <strong>{{ $patient->first_name }} {{ $patient->last_name }}</strong><br>
                    <span class="mono">{{ $patient->email }}</span>
                </td>
                <td>{{ ucfirst($patient->status->value) }}</td>
                <td>{{ $patient->email_verified_at ? 'Yes' : 'No' }}</td>
                <td>{{ $patient->appointments_count }}</td>
                <td>{{ $patient->medicalProfile?->blood_type ?? '-' }}</td>
                <td>
                    <div class="ui-actions">
                        <a class="ui-action-btn ui-action-btn--soft ui-action-btn--sm" href="{{ route('admin.patients.info', $patient) }}">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5c-7 0-11 7-11 7s4 7 11 7 11-7 11-7-4-7-11-7zm0 11.5A4.5 4.5 0 1 1 12 7a4.5 4.5 0 0 1 0 9.5zm0-7A2.5 2.5 0 1 0 12 15a2.5 2.5 0 0 0 0-5z"/></svg>
                            <span>View</span>
                        </a>
                        <a class="ui-action-btn ui-action-btn--primary ui-action-btn--sm" href="{{ route('admin.patients.update', $patient) }}">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 17.25V21h3.75l11-11.03-3.75-3.75L3 17.25zm17.71-10.04a1.003 1.003 0 0 0 0-1.42L18.21 3.29a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 2-1.66z"/></svg>
                            <span>Edit</span>
                        </a>
                    </div>
                </td>
            </tr>
        @endforeach
    </x-ui.table>

    <div class="page-links">
        {{ $patients->links() }}
    </div>
@endsection
