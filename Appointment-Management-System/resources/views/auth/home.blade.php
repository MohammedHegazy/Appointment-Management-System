@extends('layouts.auth')

@section('title', 'Home | MedMeets')

@section('content')
    <header class="auth-header">
        <h1>Hello, {{ auth()->user()->first_name }}</h1>
        <p>You are logged in as {{ auth()->user()->role->value }}.</p>
    </header>

    <div class="auth-body">
        <div class="panel">
            <p class="hint">Account status: <strong>{{ auth()->user()->status->value }}</strong></p>
            <p class="hint">Email: <strong>{{ auth()->user()->email }}</strong></p>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-form.button>Logout</x-form.button>
        </form>
    </div>
@endsection
