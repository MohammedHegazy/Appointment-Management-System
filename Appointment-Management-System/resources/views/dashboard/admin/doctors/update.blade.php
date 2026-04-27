@extends('layouts.dashboard')

@section('title', 'Update Doctor')

@section('content')
    <h2 class="dash-title">Update Independent Doctor</h2>

    <x-ui.card title="Doctor Information" class="dash-filter-card">
        <form method="POST" action="{{ route('admin.doctors.update.submit', $doctor) }}" enctype="multipart/form-data">
            @csrf

            <div class="row">
                <x-form.input name="first_name" label="First Name" :value="$doctor->user?->first_name" required />
                <x-form.input name="last_name" label="Last Name" :value="$doctor->user?->last_name" required />
            </div>

            <x-form.input name="email" label="Email" type="email" :value="$doctor->user?->email" required />
            <x-form.input name="password" label="New Password (optional)" type="password" />
            <x-form.input name="password_confirmation" label="Confirm Password" type="password" />

            <div class="row">
                <x-form.select name="status" label="Status" required>
                    <option value="pending" @selected(old('status', $doctor->user?->status?->value) === 'pending')>Pending</option>
                    <option value="active" @selected(old('status', $doctor->user?->status?->value) === 'active')>Active</option>
                    <option value="inactive" @selected(old('status', $doctor->user?->status?->value) === 'inactive')>Inactive</option>
                </x-form.select>

                <x-form.select name="specialization_id" label="Specialization">
                    <option value="">Select specialization</option>
                    @foreach ($specializations as $specialization)
                        <option value="{{ $specialization->id }}" @selected((string) old('specialization_id', $doctor->specialization_id) === (string) $specialization->id)>
                            {{ $specialization->name }}
                        </option>
                    @endforeach
                </x-form.select>
            </div>

            <x-form.input name="license_number" label="License Number" :value="$doctor->license_number" />
            <x-form.fileUpload
                name="certificate_file"
                label="Medical Certificate"
                accept=".pdf,.jpg,.jpeg,.png"
                hint="Upload to replace existing certificate (max 5MB)."
            />
            <x-form.textarea name="bio" label="Bio" rows="3" :value="$doctor->bio" />

            <div class="ui-actions">
                <x-form.button type="submit" class="ui-action-btn ui-action-btn--primary">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M17 3H5a2 2 0 0 0-2 2v14l4-4h10a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2z"/></svg>
                    <span>Save Changes</span>
                </x-form.button>
                <a class="ui-action-btn ui-action-btn--ghost" href="{{ route('admin.doctors.info', $doctor) }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
                    <span>Cancel</span>
                </a>
            </div>
        </form>
    </x-ui.card>
@endsection
