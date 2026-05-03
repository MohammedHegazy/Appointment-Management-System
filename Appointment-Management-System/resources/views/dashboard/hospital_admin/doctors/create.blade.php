@extends('layouts.dashboard')

@section('title', 'Create Doctor')

@section('content')
    <h2 class="dash-title">Create Doctor</h2>

    <x-ui.card title="Doctor Information" class="dash-filter-card">
        <form method="POST" action="{{ route('hospital-admin.doctors.create.submit') }}" enctype="multipart/form-data">
            @csrf

            <div class="row">
                <x-form.input name="first_name" label="First Name" required />
                <x-form.input name="last_name" label="Last Name" required />
            </div>

            <x-form.input name="email" label="Email" type="email" required />

            <div class="row">
                <x-form.input name="password" label="Password" type="password" required />
                <x-form.input name="password_confirmation" label="Confirm Password" type="password" required />
            </div>

            <div class="row">
                <x-form.select name="status" label="Status" required>
                    <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                    <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                </x-form.select>

                <x-form.select name="specialization_id" label="Specialization">
                    <option value="">Select specialization</option>
                    @foreach ($specializations as $specialization)
                        <option value="{{ $specialization->id }}" @selected((string) old('specialization_id') === (string) $specialization->id)>
                            {{ $specialization->name }}
                        </option>
                    @endforeach
                </x-form.select>
            </div>

            <x-form.input name="license_number" label="License Number" />
            <x-form.fileUpload
                name="certificate_file"
                label="Medical Certificate"
                accept=".pdf,.jpg,.jpeg,.png"
                hint="Upload PDF or image (max 5MB)."
            />
            <x-form.textarea name="bio" label="Bio" rows="3" />

            <div class="ui-actions">
                <x-form.button type="submit" class="ui-action-btn ui-action-btn--primary">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 11h-6V5h-2v6H5v2h6v6h2v-6h6z"/></svg>
                    <span>Create Doctor</span>
                </x-form.button>
                <a class="ui-action-btn ui-action-btn--ghost" href="{{ route('hospital-admin.doctors.index') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
                    <span>Cancel</span>
                </a>
            </div>
        </form>
    </x-ui.card>
@endsection
