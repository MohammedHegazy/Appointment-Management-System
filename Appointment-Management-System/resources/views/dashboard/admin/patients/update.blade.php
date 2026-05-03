@extends('layouts.dashboard')

@section('title', 'Update Patient')

@section('content')
    <h2 class="dash-title">Update Patient</h2>

    <x-ui.card title="Patient Information" class="dash-filter-card">
        <form method="POST" action="{{ route('admin.patients.update.submit', $patient) }}">
            @csrf

            <div class="row">
                <x-form.input name="first_name" label="First Name" :value="$patient->first_name" required />
                <x-form.input name="last_name" label="Last Name" :value="$patient->last_name" required />
            </div>

            <x-form.input name="email" label="Email" type="email" :value="$patient->email" required />

            <x-form.select name="status" label="Status" required>
                <option value="active" @selected(old('status', $patient->status->value) === 'active')>Active</option>
                <option value="inactive" @selected(old('status', $patient->status->value) === 'inactive')>Inactive</option>
            </x-form.select>

            <div class="row">
                <x-form.input name="blood_type" label="Blood Type" :value="$patient->medicalProfile?->blood_type" />
                <x-form.input name="emergency_contact" label="Emergency Contact" :value="$patient->medicalProfile?->emergency_contact" />
            </div>

            <x-form.textarea name="allergies" label="Allergies" rows="2" :value="$patient->medicalProfile?->allergies" />
            <x-form.textarea name="chronic_conditions" label="Chronic Conditions" rows="2" :value="$patient->medicalProfile?->chronic_conditions" />
            <x-form.textarea name="medications" label="Medications" rows="2" :value="$patient->medicalProfile?->medications" />

            <div class="ui-actions">
                <x-form.button type="submit" class="ui-action-btn ui-action-btn--primary">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M17 3H5a2 2 0 0 0-2 2v14l4-4h10a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2z"/></svg>
                    <span>Save Changes</span>
                </x-form.button>
                <a class="ui-action-btn ui-action-btn--ghost" href="{{ route('admin.patients.info', $patient) }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
                    <span>Cancel</span>
                </a>
            </div>
        </form>
    </x-ui.card>
@endsection
