@extends('layouts.auth')

@section('title', 'Login | MedMeets')

@section('content')
    <header class="auth-header">
        <h1>Welcome back</h1>
        <p>Login to continue to MedMeets.</p>
    </header>

    <div class="auth-body">
        <form method="POST" action="{{ route('login.submit') }}">
            @csrf

            <x-form.input name="email" label="Email" type="email" required />
            <x-form.input name="password" label="Password" type="password" required />

            <x-form.button>Login</x-form.button>
            <a href="{{ route('register') }}" class="btn-link">Need an account? Register</a>
        </form>
    </div>
@endsection
