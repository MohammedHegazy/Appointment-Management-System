@extends('layouts.dashboard')

@section('title', 'Admin Hospitals')

@section('content')
    <h2 class="dash-title">Hospitals</h2>

    <div class="ui-actions" style="margin-bottom: 14px;">
        <a class="ui-action-btn ui-action-btn--primary" href="{{ route('admin.hospitals.create') }}">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 11h-6V5h-2v6H5v2h6v6h2v-6h6z"/></svg>
            <span>Create Hospital</span>
        </a>
    </div>

    <x-ui.card class="dash-filter-card" title="Filters">
        <form method="GET" action="{{ route('admin.hospitals.index') }}" class="filter-grid">
            <x-form.input
                name="search"
                label="Search by name/address/phone"
                :value="request('search')"
                placeholder="hospital name or phone"
            />
            <div></div>
            <div></div>
            <x-form.button type="submit" class="ui-action-btn ui-action-btn--soft">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 18h4v-2h-4v2zm-7-8v2h18v-2H3zm3-6v2h12V4H6z"/></svg>
                <span>Filter</span>
            </x-form.button>
        </form>
    </x-ui.card>

    <x-ui.table
        :headers="['Hospital', 'Address', 'Phone', 'Admins', 'Doctors', 'Actions']"
        :has-rows="$hospitals->isNotEmpty()"
        empty="No hospitals found."
        :colspan="6"
    >
        @foreach ($hospitals as $hospital)
            <tr>
                <td>
                    <strong>{{ $hospital->name }}</strong><br>
                    <span class="mono">#{{ $hospital->id }}</span>
                </td>
                <td>{{ $hospital->address ?: '-' }}</td>
                <td>{{ $hospital->phone ?: '-' }}</td>
                <td>{{ $hospital->hospital_admins_count }}</td>
                <td>{{ $hospital->doctors_count }}</td>
                <td>
                    <div class="ui-actions">
                        <a class="ui-action-btn ui-action-btn--soft ui-action-btn--sm" href="{{ route('admin.hospitals.info', $hospital) }}">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5c-7 0-11 7-11 7s4 7 11 7 11-7 11-7-4-7-11-7zm0 11.5A4.5 4.5 0 1 1 12 7a4.5 4.5 0 0 1 0 9.5zm0-7A2.5 2.5 0 1 0 12 15a2.5 2.5 0 0 0 0-5z"/></svg>
                            <span>View</span>
                        </a>
                        <a class="ui-action-btn ui-action-btn--primary ui-action-btn--sm" href="{{ route('admin.hospitals.update', $hospital) }}">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 17.25V21h3.75l11-11.03-3.75-3.75L3 17.25zm17.71-10.04a1.003 1.003 0 0 0 0-1.42L18.21 3.29a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 2-1.66z"/></svg>
                            <span>Edit</span>
                        </a>
                    </div>
                </td>
            </tr>
        @endforeach
    </x-ui.table>

    <div class="page-links">
        {{ $hospitals->links() }}
    </div>
@endsection
