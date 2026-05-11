@extends('layouts.dashboard')

@section('title', 'Doctor Profile')

@section('content')
    <x-shared.myProfile
        :user="$user"
        :profile-action="route('doctor.settings.profile.update')"
        :password-action="route('doctor.settings.profile.password.update')"
    />

    <x-ui.card title="Professional Information">
        <form method="POST" action="{{ route('doctor.settings.profile.professional.update') }}" enctype="multipart/form-data">
            @csrf

            <div class="row">
                <x-form.select name="specialization_id" label="Specialization">
                    <option value="">Select specialization</option>
                    @foreach ($specializations as $specialization)
                        <option value="{{ $specialization->id }}" @selected((string) old('specialization_id', $user->doctor?->specialization_id) === (string) $specialization->id)>
                            {{ $specialization->name }}
                        </option>
                    @endforeach
                </x-form.select>

                <x-form.input name="license_number" label="License Number" :value="old('license_number', $user->doctor?->license_number)" />
            </div>

            <x-form.fileUpload
                name="certificate_file"
                label="Certificate File"
                accept=".pdf,.jpg,.jpeg,.png"
                hint="Upload certificate (max 5MB)."
            />
            @if ($user->doctor?->certificate_file)
                <p class="hint" style="margin-top: 8px;">
                    Current file:
                    <a href="{{ asset('storage/' . $user->doctor->certificate_file) }}" target="_blank" rel="noopener">Open Certificate</a>
                </p>
            @endif

            <x-form.textarea name="bio" label="Bio" rows="4" :value="old('bio', $user->doctor?->bio)" />

            @if ($errors->profile->any())
                <p class="field-error">{{ $errors->profile->first() }}</p>
            @endif

            <div class="ui-actions">
                <x-form.button type="submit" class="ui-action-btn ui-action-btn--primary">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M17 3H5a2 2 0 0 0-2 2v14l4-4h10a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2z"/></svg>
                    <span>Update Professional Info</span>
                </x-form.button>
            </div>
        </form>
    </x-ui.card>
@endsection
