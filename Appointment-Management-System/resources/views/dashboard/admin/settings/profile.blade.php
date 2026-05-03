@extends('layouts.dashboard')

@section('title', 'My Profile')

@section('content')
    <x-shared.myProfile
        :user="$user"
        :profile-action="route('admin.settings.profile.update')"
        :password-action="route('admin.settings.profile.password.update')"
    />
@endsection
