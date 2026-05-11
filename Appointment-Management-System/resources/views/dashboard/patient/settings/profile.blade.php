@extends('layouts.dashboard')

@section('title', 'My Profile')

@section('content')
    <x-shared.myProfile
        :user="$user"
        :profile-action="route('patient.settings.profile.update')"
        :password-action="route('patient.settings.profile.password.update')"
    />

    <x-ui.card title="Medical Profile">
        <form method="POST" action="{{ route('patient.settings.profile.medical.update') }}">
            @csrf
            <div class="row">
                <x-form.input name="blood_type" label="Blood Type" :value="old('blood_type', $user->medicalProfile?->blood_type)" />
                <x-form.input name="emergency_contact" label="Emergency Contact" :value="old('emergency_contact', $user->medicalProfile?->emergency_contact)" />
            </div>

            <x-form.textarea name="allergies" label="Allergies" rows="3" :value="old('allergies', $user->medicalProfile?->allergies)" />
            <x-form.textarea name="chronic_conditions" label="Chronic Conditions" rows="3" :value="old('chronic_conditions', $user->medicalProfile?->chronic_conditions)" />
            <x-form.textarea name="medications" label="Current Medications" rows="3" :value="old('medications', $user->medicalProfile?->medications)" />

            @if ($errors->profile->any())
                <p class="field-error">{{ $errors->profile->first() }}</p>
            @endif

            <div class="ui-actions">
                <x-form.button type="submit" class="ui-action-btn ui-action-btn--primary">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M17 3H5a2 2 0 0 0-2 2v14l4-4h10a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2z"/></svg>
                    <span>Update Medical Profile</span>
                </x-form.button>
            </div>
        </form>
    </x-ui.card>
@endsection
