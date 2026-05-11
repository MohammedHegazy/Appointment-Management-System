@extends('layouts.dashboard')

@section('title', 'My Profile')

@section('content')
    <x-shared.myProfile
        :user="$user"
        :profile-action="route('hospital-doctor.settings.profile.update')"
        :password-action="route('hospital-doctor.settings.profile.password.update')"
    />
@endsection
