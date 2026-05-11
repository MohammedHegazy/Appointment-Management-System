<?php

namespace App\Http\Controllers\Web\HospitalDoctor;

use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SettingsController extends BaseHospitalDoctorController
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function profile(): View
    {
        $user = $this->ensureHospitalDoctor();

        return view('dashboard.hospital_doctor.settings.profile', [
            'user' => $user,
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $this->ensureHospitalDoctor();

        $validated = $request->validateWithBag('profile', [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', "unique:users,email,{$user->id}"],
            'avatar' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $oldUser = $user->only(['first_name', 'last_name', 'email', 'avatar']);
        $user->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'avatar' => $validated['avatar'] ?? $user->avatar,
        ]);

        $this->auditLogService->log(
            action: 'hospital_doctor.profile_updated.web',
            entity: $user,
            oldValues: ['user' => $oldUser],
            newValues: ['user' => $user->fresh()->only(['first_name', 'last_name', 'email', 'avatar'])],
            request: $request,
            userId: $user->id
        );

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $this->ensureHospitalDoctor();

        $validated = $request->validateWithBag('password', [
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.'], 'password');
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->auditLogService->log(
            action: 'hospital_doctor.password_updated.web',
            entity: $user,
            request: $request,
            userId: $user->id
        );

        return back()->with('success', 'Password updated successfully.');
    }
}

