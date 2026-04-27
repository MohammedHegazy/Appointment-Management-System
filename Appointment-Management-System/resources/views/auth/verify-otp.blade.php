@extends('layouts.auth')

@section('title', 'Verify Email | MedMeets')

@section('content')
    <header class="auth-header">
        <h1>Verify your email</h1>
        <p>Enter the 6-digit OTP sent to your inbox.</p>
    </header>

    <div class="auth-body">
        <form method="POST" action="{{ route('verify.submit') }}">
            @csrf
            <x-form.input name="email" label="Email" type="email" :value="$email" required />
            <x-form.input name="otp" label="OTP" inputmode="numeric" maxlength="6" required />
            <x-form.button>Verify OTP</x-form.button>
        </form>

        <form method="POST" action="{{ route('verify.resend') }}" style="margin-top: 12px;">
            @csrf
            <input type="hidden" name="email" value="{{ old('email', $email) }}">
            <x-form.button>Resend OTP</x-form.button>
        </form>

        <a href="{{ route('login') }}" class="btn-link">Back to login</a>
    </div>
@endsection
