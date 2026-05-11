<?php

namespace App\Http\Controllers\Web\Patient;

use App\Models\MedicalProfile;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SettingsController extends BasePatientController
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function profile(): View
    {
        $user = $this->ensurePatient()->load('medicalProfile');

        return view('dashboard.patient.settings.profile', [
            'user' => $user,
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $this->ensurePatient();

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
            action: 'patient.profile_updated.web',
            entity: $user,
            oldValues: ['user' => $oldUser],
            newValues: ['user' => $user->fresh()->only(['first_name', 'last_name', 'email', 'avatar'])],
            request: $request,
            userId: $user->id
        );

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updateMedicalProfile(Request $request): RedirectResponse
    {
        $user = $this->ensurePatient();

        $validated = $request->validateWithBag('profile', [
            'blood_type' => ['nullable', 'string', 'max:10'],
            'allergies' => ['nullable', 'string'],
            'chronic_conditions' => ['nullable', 'string'],
            'medications' => ['nullable', 'string'],
            'emergency_contact' => ['nullable', 'string', 'max:255'],
        ]);

        $profile = MedicalProfile::query()->firstOrCreate(['user_id' => $user->id]);
        $oldValues = $profile->only(['blood_type', 'allergies', 'chronic_conditions', 'medications', 'emergency_contact']);

        $profile->update([
            'blood_type' => $validated['blood_type'] ?? null,
            'allergies' => $validated['allergies'] ?? null,
            'chronic_conditions' => $validated['chronic_conditions'] ?? null,
            'medications' => $validated['medications'] ?? null,
            'emergency_contact' => $validated['emergency_contact'] ?? null,
        ]);

        $this->auditLogService->log(
            action: 'patient.medical_profile_updated.web',
            entity: $profile,
            oldValues: $oldValues,
            newValues: $profile->fresh()->only(['blood_type', 'allergies', 'chronic_conditions', 'medications', 'emergency_contact']),
            request: $request,
            userId: $user->id
        );

        return back()->with('success', 'Medical profile updated successfully.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $this->ensurePatient();

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
            action: 'patient.password_updated.web',
            entity: $user,
            request: $request,
            userId: $user->id
        );

        return back()->with('success', 'Password updated successfully.');
    }
}

