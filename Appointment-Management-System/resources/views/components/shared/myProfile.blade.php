@props([
    'user',
    'profileAction',
    'passwordAction',
])

@php
    $initials = strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1));
@endphp

<x-shared.profile
    :title="$user->first_name . ' ' . $user->last_name"
    :subtitle="$user->email"
    :avatar="$initials"
    :avatar-url="$user->avatar ? asset('storage/' . $user->avatar) : null"
    :badges="[
        ['label' => ucfirst(str_replace('_', ' ', $user->role->value)), 'class' => 'is-pending'],
        ['label' => ucfirst($user->status->value), 'class' => $user->status->value === 'active' ? 'is-active' : ($user->status->value === 'inactive' ? 'is-inactive' : 'is-pending')],
    ]"
>
    <a class="ui-action-btn ui-action-btn--soft" href="{{ $user->avatar ? asset('storage/' . $user->avatar) : '#' }}" @if(!$user->avatar) style="pointer-events:none;opacity:.6;" @endif>
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16l4-4h10a2 2 0 0 0 2-2V8z"/></svg>
        <span>Current Avatar</span>
    </a>
</x-shared.profile>

<div class="patient-sections-grid">
    <x-ui.card title="Profile Information">
        <form method="POST" action="{{ $profileAction }}" enctype="multipart/form-data">
            @csrf
            <div class="profile-avatar-preview">
                @if ($user->avatar)
                    <img id="my-profile-avatar-preview" src="{{ asset('storage/' . $user->avatar) }}" alt="Current avatar">
                @else
                    <div id="my-profile-avatar-preview" class="profile-avatar-preview__fallback">{{ $initials }}</div>
                @endif
            </div>
            <x-form.input name="first_name" label="First Name" :value="$user->first_name" required />
            <x-form.input name="last_name" label="Last Name" :value="$user->last_name" required />
            <x-form.input name="email" label="Email" type="email" :value="$user->email" required />
            <x-form.fileUpload
                name="avatar"
                label="Avatar"
                accept=".jpg,.jpeg,.png,.webp"
                hint="Upload image (max 4MB)."
            />
            @if ($errors->profile->any())
                <p class="field-error">{{ $errors->profile->first() }}</p>
            @endif
            <div class="ui-actions">
                <x-form.button type="submit" class="ui-action-btn ui-action-btn--primary">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M17 3H5a2 2 0 0 0-2 2v14l4-4h10a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2z"/></svg>
                    <span>Update Profile</span>
                </x-form.button>
            </div>
        </form>
    </x-ui.card>

    <x-ui.card title="Change Password">
        <form method="POST" action="{{ $passwordAction }}">
            @csrf
            <x-form.input name="current_password" label="Current Password" type="password" required />
            <x-form.input name="password" label="New Password" type="password" required />
            <x-form.input name="password_confirmation" label="Confirm Password" type="password" required />
            @if ($errors->password->any())
                <p class="field-error">{{ $errors->password->first() }}</p>
            @endif
            <div class="ui-actions">
                <x-form.button type="submit" class="ui-action-btn ui-action-btn--primary">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 1a5 5 0 0 0-5 5v3H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V11a2 2 0 0 0-2-2h-2V6a5 5 0 0 0-5-5zm-3 8V6a3 3 0 1 1 6 0v3H9z"/></svg>
                    <span>Update Password</span>
                </x-form.button>
            </div>
        </form>
    </x-ui.card>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('avatar');
            const preview = document.getElementById('my-profile-avatar-preview');

            if (!input || !preview) return;

            input.addEventListener('change', function () {
                const file = input.files?.[0];
                if (!file || !file.type.startsWith('image/')) return;

                const url = URL.createObjectURL(file);
                if (preview.tagName === 'IMG') {
                    preview.src = url;
                    return;
                }

                const img = document.createElement('img');
                img.id = 'my-profile-avatar-preview';
                img.src = url;
                img.alt = 'Avatar preview';
                preview.replaceWith(img);
            });
        });
    </script>
@endpush
