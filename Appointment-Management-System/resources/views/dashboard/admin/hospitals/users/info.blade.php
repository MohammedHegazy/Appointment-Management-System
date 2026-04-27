@extends('layouts.dashboard')

@section('title', 'Hospital User Details')

@section('content')
    @php
        $statusClass = match ($user->status->value) {
            'active' => 'is-active',
            'inactive' => 'is-inactive',
            default => 'is-pending',
        };
        $initials = strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1));
    @endphp

    <x-shared.profile
        :title="$user->first_name . ' ' . $user->last_name"
        :subtitle="$user->email"
        :avatar="$initials"
        :badges="[
            ['label' => ucfirst(str_replace('_', ' ', $user->role->value)), 'class' => 'is-pending'],
            ['label' => ucfirst($user->status->value), 'class' => $statusClass],
        ]"
    >
        <a class="ui-action-btn ui-action-btn--primary" href="{{ route('admin.hospitals.users.update', [$hospital, $user]) }}">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 17.25V21h3.75l11-11.03-3.75-3.75L3 17.25zm17.71-10.04a1.003 1.003 0 0 0 0-1.42L18.21 3.29a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 2-1.66z"/></svg>
            <span>Edit User</span>
        </a>
        <a class="ui-action-btn ui-action-btn--ghost" href="{{ route('admin.hospitals.users.index', $hospital) }}">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
            <span>Back to users</span>
        </a>
    </x-shared.profile>

    <div class="patient-sections-grid">
        <x-ui.card title="Identity">
            <dl class="data-list">
                <div><dt>First Name</dt><dd>{{ $user->first_name }}</dd></div>
                <div><dt>Last Name</dt><dd>{{ $user->last_name }}</dd></div>
                <div><dt>Email</dt><dd>{{ $user->email }}</dd></div>
                <div><dt>Email Verified</dt><dd>{{ $user->email_verified_at ? 'Yes' : 'No' }}</dd></div>
                <div><dt>Role</dt><dd>{{ ucfirst(str_replace('_', ' ', $user->role->value)) }}</dd></div>
            </dl>
        </x-ui.card>

        @if ($user->role->value === 'doctor')
            <x-ui.card title="Doctor Profile">
                <dl class="data-list">
                    <div><dt>Specialization</dt><dd>{{ $user->doctor?->specialization?->name ?? '-' }}</dd></div>
                    <div><dt>License Number</dt><dd>{{ $user->doctor?->license_number ?: '-' }}</dd></div>
                    <div>
                        <dt>Certificate</dt>
                        <dd>
                            @if ($user->doctor?->certificate_file)
                                <a href="{{ asset('storage/' . $user->doctor->certificate_file) }}" target="_blank" rel="noopener">Open File</a>
                            @else
                                Not uploaded
                            @endif
                        </dd>
                    </div>
                    <div><dt>Bio</dt><dd>{{ $user->doctor?->bio ?: '-' }}</dd></div>
                </dl>
            </x-ui.card>
        @elseif ($user->role->value === 'patient')
            <x-ui.card title="Medical Profile">
                <dl class="data-list">
                    <div><dt>Blood Type</dt><dd>{{ $user->medicalProfile?->blood_type ?? '-' }}</dd></div>
                    <div><dt>Allergies</dt><dd>{{ $user->medicalProfile?->allergies ?? '-' }}</dd></div>
                    <div><dt>Chronic Conditions</dt><dd>{{ $user->medicalProfile?->chronic_conditions ?? '-' }}</dd></div>
                    <div><dt>Medications</dt><dd>{{ $user->medicalProfile?->medications ?? '-' }}</dd></div>
                    <div><dt>Emergency Contact</dt><dd>{{ $user->medicalProfile?->emergency_contact ?? '-' }}</dd></div>
                </dl>
            </x-ui.card>
        @else
            <x-ui.card title="Role Notes">
                <p class="hint">This user is a hospital admin account. No medical/doctor profile fields are attached.</p>
            </x-ui.card>
        @endif
    </div>
@endsection
