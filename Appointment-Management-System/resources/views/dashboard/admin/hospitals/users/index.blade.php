@extends('layouts.dashboard')

@section('title', 'Hospital Users')

@section('content')
    <h2 class="dash-title">Users · {{ $hospital->name }}</h2>

    <x-ui.card class="dash-filter-card" title="Filters">
        <form method="GET" action="{{ route('admin.hospitals.users.index', $hospital) }}" class="filter-grid">
            <x-form.input
                name="search"
                label="Search by name/email"
                :value="request('search')"
                placeholder="user name or email"
            />

            <x-form.select name="role" label="Role">
                <option value="">All roles</option>
                <option value="hospital_admin" @selected(request('role') === 'hospital_admin')>Hospital Admin</option>
                <option value="doctor" @selected(request('role') === 'doctor')>Doctor</option>
                <option value="patient" @selected(request('role') === 'patient')>Patient</option>
            </x-form.select>

            <x-form.select name="status" label="Status">
                <option value="">All statuses</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                <option value="pending" @selected(request('status') === 'pending')>Pending</option>
            </x-form.select>

            <x-form.button type="submit" class="ui-action-btn ui-action-btn--soft">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 18h4v-2h-4v2zm-7-8v2h18v-2H3zm3-6v2h12V4H6z"/></svg>
                <span>Filter</span>
            </x-form.button>
        </form>
    </x-ui.card>

    <x-ui.table
        :headers="['User', 'Role', 'Status', 'Email Verified', 'Actions']"
        :has-rows="$users->isNotEmpty()"
        empty="No users found for this hospital."
        :colspan="5"
    >
        @foreach ($users as $user)
            <tr>
                <td>
                    <strong>{{ $user->first_name }} {{ $user->last_name }}</strong><br>
                    <span class="mono">{{ $user->email }}</span>
                </td>
                <td>{{ ucfirst(str_replace('_', ' ', $user->role->value)) }}</td>
                <td>{{ ucfirst($user->status->value) }}</td>
                <td>{{ $user->email_verified_at ? 'Yes' : 'No' }}</td>
                <td>
                    <div class="ui-actions">
                        <a class="ui-action-btn ui-action-btn--soft ui-action-btn--sm" href="{{ route('admin.hospitals.users.info', [$hospital, $user]) }}">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5c-7 0-11 7-11 7s4 7 11 7 11-7 11-7-4-7-11-7zm0 11.5A4.5 4.5 0 1 1 12 7a4.5 4.5 0 0 1 0 9.5zm0-7A2.5 2.5 0 1 0 12 15a2.5 2.5 0 0 0 0-5z"/></svg>
                            <span>View</span>
                        </a>
                        <a class="ui-action-btn ui-action-btn--primary ui-action-btn--sm" href="{{ route('admin.hospitals.users.update', [$hospital, $user]) }}">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 17.25V21h3.75l11-11.03-3.75-3.75L3 17.25zm17.71-10.04a1.003 1.003 0 0 0 0-1.42L18.21 3.29a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 2-1.66z"/></svg>
                            <span>Edit</span>
                        </a>
                    </div>
                </td>
            </tr>
        @endforeach
    </x-ui.table>

    <div class="page-links">
        {{ $users->links() }}
    </div>
@endsection
