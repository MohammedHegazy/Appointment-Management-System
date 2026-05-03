@extends('layouts.dashboard')

@section('title', 'Hospital Details')

@section('content')
    @php
        $initials = strtoupper(substr($hospital->name, 0, 2));
    @endphp

    <x-shared.profile
        :title="$hospital->name"
        :subtitle="$hospital->address ?: 'No address provided'"
        :avatar="$initials"
        :badges="[
            ['label' => $hospital->phone ?: 'No phone', 'class' => 'is-pending'],
            ['label' => $stats['all_members'] . ' Total Members', 'class' => 'is-active'],
        ]"
    >
        <a class="ui-action-btn ui-action-btn--primary" href="{{ route('admin.hospitals.update', $hospital) }}">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 17.25V21h3.75l11-11.03-3.75-3.75L3 17.25zm17.71-10.04a1.003 1.003 0 0 0 0-1.42L18.21 3.29a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 2-1.66z"/></svg>
            <span>Edit Hospital</span>
        </a>
        <a class="ui-action-btn ui-action-btn--ghost" href="{{ route('admin.hospitals.index') }}">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
            <span>Back to list</span>
        </a>
        <a class="ui-action-btn ui-action-btn--soft" href="{{ route('admin.hospitals.users.index', $hospital) }}">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 11c1.66 0 2.99-1.79 2.99-4S17.66 3 16 3s-3 1.79-3 4 1.34 4 3 4zm-8 0c1.66 0 2.99-1.79 2.99-4S9.66 3 8 3 5 4.79 5 7s1.34 4 3 4zm0 2c-2.33 0-7 1.17-7 3.5V21h14v-4.5C15 14.17 10.33 13 8 13zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.98 1.97 3.45V21h7v-4.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
            <span>Manage Users</span>
        </a>
        <a class="ui-action-btn ui-action-btn--soft" href="{{ route('admin.hospitals.doctors.index', $hospital) }}">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1s-2.4.84-2.82 2H5a2 2 0 0 0-2 2v15a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2zm-7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm0 6a3 3 0 1 1 0 6 3 3 0 0 1 0-6zm0 10c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08s5.97 1.09 6 3.08A7.18 7.18 0 0 1 12 19z"/></svg>
            <span>Manage Doctors</span>
        </a>
    </x-shared.profile>

    <div class="patient-kpi-grid">
        <x-ui.card title="Hospital Admins">
            <p class="dash-kpi-value">{{ $stats['admins'] }}</p>
        </x-ui.card>
        <x-ui.card title="Doctors">
            <p class="dash-kpi-value">{{ $stats['doctors'] }}</p>
        </x-ui.card>
        <x-ui.card title="Patients">
            <p class="dash-kpi-value">{{ $stats['patients'] }}</p>
        </x-ui.card>
    </div>

    <div class="patient-sections-grid">
        <x-ui.card title="Hospital Information">
            <dl class="data-list">
                <div><dt>Name</dt><dd>{{ $hospital->name }}</dd></div>
                <div><dt>Address</dt><dd>{{ $hospital->address ?: '-' }}</dd></div>
                <div><dt>Phone</dt><dd>{{ $hospital->phone ?: '-' }}</dd></div>
                <div><dt>Created At</dt><dd>{{ $hospital->created_at?->format('Y-m-d H:i') }}</dd></div>
            </dl>
        </x-ui.card>

        <x-ui.card title="Hospital Admins">
            <x-ui.table
                :headers="['Name', 'Email', 'Status']"
                :has-rows="$admins->isNotEmpty()"
                empty="No hospital admins."
                :colspan="3"
            >
                @foreach ($admins as $admin)
                    <tr>
                        <td>{{ $admin->first_name }} {{ $admin->last_name }}</td>
                        <td>{{ $admin->email }}</td>
                        <td>{{ ucfirst($admin->status->value) }}</td>
                    </tr>
                @endforeach
            </x-ui.table>
        </x-ui.card>
    </div>

    <x-ui.card title="Doctors">
        <x-ui.table
            :headers="['Doctor', 'Specialization', 'Status']"
            :has-rows="$doctors->isNotEmpty()"
            empty="No doctors in this hospital."
            :colspan="3"
        >
            @foreach ($doctors as $doctor)
                <tr>
                    <td>{{ $doctor->user?->first_name }} {{ $doctor->user?->last_name }}</td>
                    <td>{{ $doctor->specialization?->name ?: '-' }}</td>
                    <td>
                        {{ ucfirst($doctor->user?->status?->value ?? '-') }}
                        <a class="btn-link" href="{{ route('admin.hospitals.doctors.info', [$hospital, $doctor]) }}" style="margin-inline-start:8px;">View</a>
                    </td>
                </tr>
            @endforeach
        </x-ui.table>
    </x-ui.card>
@endsection
