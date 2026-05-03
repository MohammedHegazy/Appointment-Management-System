@extends('layouts.dashboard')

@section('title', 'Hospital Settings')

@section('content')
    <h2 class="dash-title">Hospital Settings</h2>

    <x-ui.card title="Hospital Profile" class="dash-filter-card">
        <form method="POST" action="{{ route('hospital-admin.settings.hospital.update') }}">
            @csrf

            <x-form.input name="name" label="Hospital Name" :value="old('name', $hospital->name)" required />
            <x-form.input name="phone" label="Phone" :value="old('phone', $hospital->phone)" />
            <x-form.textarea name="address" label="Address" rows="3" :value="old('address', $hospital->address)" />

            <div class="ui-actions">
                <x-form.button type="submit" class="ui-action-btn ui-action-btn--primary">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M17 3H5a2 2 0 0 0-2 2v14l4-4h10a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2z"/></svg>
                    <span>Save Settings</span>
                </x-form.button>
            </div>
        </form>
    </x-ui.card>
@endsection
