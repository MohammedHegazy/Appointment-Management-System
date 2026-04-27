@extends('layouts.dashboard')

@section('title', 'Update Hospital')

@section('content')
    <h2 class="dash-title">Update Hospital</h2>

    <x-ui.card title="Hospital Information" class="dash-filter-card">
        <form method="POST" action="{{ route('admin.hospitals.update.submit', $hospital) }}">
            @csrf

            <x-form.input name="name" label="Hospital Name" :value="$hospital->name" required />
            <x-form.input name="address" label="Address" :value="$hospital->address" />
            <x-form.input name="phone" label="Phone" :value="$hospital->phone" />

            <div class="ui-actions">
                <x-form.button type="submit" class="ui-action-btn ui-action-btn--primary">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M17 3H5a2 2 0 0 0-2 2v14l4-4h10a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2z"/></svg>
                    <span>Save Changes</span>
                </x-form.button>
                <a class="ui-action-btn ui-action-btn--ghost" href="{{ route('admin.hospitals.info', $hospital) }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
                    <span>Cancel</span>
                </a>
            </div>
        </form>
    </x-ui.card>
@endsection
