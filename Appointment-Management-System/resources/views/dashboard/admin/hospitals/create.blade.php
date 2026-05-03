@extends('layouts.dashboard')

@section('title', 'Create Hospital')

@section('content')
    <h2 class="dash-title">Create Hospital</h2>

    <x-ui.card title="Hospital Information" class="dash-filter-card">
        <form method="POST" action="{{ route('admin.hospitals.create.submit') }}">
            @csrf

            <x-form.input name="name" label="Hospital Name" required />
            <x-form.input name="address" label="Address" />
            <x-form.input name="phone" label="Phone" />

            <div class="ui-actions">
                <x-form.button type="submit" class="ui-action-btn ui-action-btn--primary">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 11h-6V5h-2v6H5v2h6v6h2v-6h6z"/></svg>
                    <span>Create Hospital</span>
                </x-form.button>
                <a class="ui-action-btn ui-action-btn--ghost" href="{{ route('admin.hospitals.index') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
                    <span>Cancel</span>
                </a>
            </div>
        </form>
    </x-ui.card>
@endsection
