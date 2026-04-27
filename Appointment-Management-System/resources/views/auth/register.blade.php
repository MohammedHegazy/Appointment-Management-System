@extends('layouts.auth')

@section('title', 'Register | MedMeets')

@section('content')
    <header class="auth-header">
        <h1>Create your account</h1>
        <p>Register as patient, doctor, or hospital admin.</p>
    </header>

    <div class="auth-body">
        <form method="POST" action="{{ route('register.submit') }}" enctype="multipart/form-data">
            @csrf

            <x-form.select name="register_as" label="Register as" required>
                    <option value="patient" @selected(old('register_as') === 'patient')>Patient</option>
                    <option value="doctor" @selected(old('register_as') === 'doctor')>Doctor</option>
                    <option value="hospital_admin" @selected(old('register_as') === 'hospital_admin')>Hospital Admin</option>
            </x-form.select>

            <div class="row">
                <x-form.input name="first_name" label="First name" required />
                <x-form.input name="last_name" label="Last name" required />
            </div>

            <x-form.input name="email" label="Email" type="email" required />

            <div class="row">
                <x-form.input name="password" label="Password" type="password" required />
                <x-form.input name="password_confirmation" label="Confirm password" type="password" required />
            </div>

            <div id="hospital-fields" class="hidden">
                <div class="panel">
                    <p class="hint">Hospital details</p>
                </div>
                <x-form.input name="hospital_name" label="Hospital name" />
                <div class="row">
                    <x-form.input name="hospital_phone" label="Hospital phone" />
                    <x-form.input name="hospital_address" label="Hospital address" />
                </div>
            </div>

            <div id="doctor-fields" class="hidden">
                <div class="panel">
                    <p class="hint">Doctor details</p>
                </div>
                <div class="row">
                    <x-form.select name="specialization_id" label="Specialization">
                            <option value="">Select specialization</option>
                            @foreach ($specializations as $specialization)
                                <option value="{{ $specialization->id }}" @selected((string) old('specialization_id') === (string) $specialization->id)>
                                    {{ $specialization->name }}
                                </option>
                            @endforeach
                    </x-form.select>
                    <x-form.input name="license_number" label="License number" />
                </div>
                <x-form.fileUpload
                    name="certificate_file"
                    label="Medical Certificate"
                    accept=".pdf,.jpg,.jpeg,.png"
                    hint="Upload PDF or image (max 5MB)."
                />
                <x-form.textarea name="bio" label="Bio" rows="3" />
            </div>

            <x-form.button>Register</x-form.button>
            <a href="{{ route('login') }}" class="btn-link">Already have an account? Login</a>
        </form>
    </div>

    <script>
        const roleSelect = document.getElementById('register_as');
        const hospitalFields = document.getElementById('hospital-fields');
        const doctorFields = document.getElementById('doctor-fields');
        const hospitalName = document.getElementById('hospital_name');

        function toggleRoleFields() {
            const role = roleSelect.value;
            const isHospital = role === 'hospital_admin';
            const isDoctor = role === 'doctor';

            hospitalFields.classList.toggle('hidden', !isHospital);
            doctorFields.classList.toggle('hidden', !isDoctor);
            hospitalName.required = isHospital;
        }

        roleSelect.addEventListener('change', toggleRoleFields);
        toggleRoleFields();
    </script>
@endsection
