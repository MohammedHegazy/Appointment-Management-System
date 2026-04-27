@extends('layouts.dashboard')

@section('title', 'Update Hospital User')

@section('content')
    <h2 class="dash-title">Update User · {{ $hospital->name }}</h2>

    <x-ui.card title="User Information" class="dash-filter-card">
        <form method="POST" action="{{ route('admin.hospitals.users.update.submit', [$hospital, $user]) }}" enctype="multipart/form-data">
            @csrf

            <div class="row">
                <x-form.input name="first_name" label="First Name" :value="$user->first_name" required />
                <x-form.input name="last_name" label="Last Name" :value="$user->last_name" required />
            </div>

            <x-form.input name="email" label="Email" type="email" :value="$user->email" required />

            <div class="row">
                <x-form.select name="role" label="Role" required>
                    <option value="hospital_admin" @selected(old('role', $user->role->value) === 'hospital_admin')>Hospital Admin</option>
                    <option value="doctor" @selected(old('role', $user->role->value) === 'doctor')>Doctor</option>
                    <option value="patient" @selected(old('role', $user->role->value) === 'patient')>Patient</option>
                </x-form.select>

                <x-form.select name="status" label="Status" required>
                    <option value="pending" @selected(old('status', $user->status->value) === 'pending')>Pending</option>
                    <option value="active" @selected(old('status', $user->status->value) === 'active')>Active</option>
                    <option value="inactive" @selected(old('status', $user->status->value) === 'inactive')>Inactive</option>
                </x-form.select>
            </div>

            <div class="row">
                <x-form.select name="specialization_id" label="Doctor Specialization">
                    <option value="">Select specialization</option>
                    @foreach ($specializations as $specialization)
                        <option value="{{ $specialization->id }}" @selected((string) old('specialization_id', $user->doctor?->specialization_id) === (string) $specialization->id)>
                            {{ $specialization->name }}
                        </option>
                    @endforeach
                </x-form.select>
                <x-form.input name="license_number" label="License Number" :value="$user->doctor?->license_number" />
            </div>

            <x-form.fileUpload
                name="certificate_file"
                label="Doctor Certificate"
                accept=".pdf,.jpg,.jpeg,.png"
                hint="Upload to replace existing certificate (max 5MB)."
            />
            <x-form.textarea name="bio" label="Doctor Bio" rows="3" :value="$user->doctor?->bio" />

            <div class="row">
                <x-form.input name="blood_type" label="Patient Blood Type" :value="$user->medicalProfile?->blood_type" />
                <x-form.input name="emergency_contact" label="Patient Emergency Contact" :value="$user->medicalProfile?->emergency_contact" />
            </div>

            <x-form.textarea name="allergies" label="Patient Allergies" rows="2" :value="$user->medicalProfile?->allergies" />
            <x-form.textarea name="chronic_conditions" label="Patient Chronic Conditions" rows="2" :value="$user->medicalProfile?->chronic_conditions" />
            <x-form.textarea name="medications" label="Patient Medications" rows="2" :value="$user->medicalProfile?->medications" />

            <div class="ui-actions">
                <x-form.button type="submit" class="ui-action-btn ui-action-btn--primary">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M17 3H5a2 2 0 0 0-2 2v14l4-4h10a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2z"/></svg>
                    <span>Save Changes</span>
                </x-form.button>
                <a class="ui-action-btn ui-action-btn--ghost" href="{{ route('admin.hospitals.users.info', [$hospital, $user]) }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
                    <span>Cancel</span>
                </a>
            </div>
        </form>
    </x-ui.card>
@endsection
