<?php

namespace App\Http\Controllers\Web\Doctor;

use App\Models\Specialization;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SettingsController extends BaseDoctorController
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function profile(): View
    {
        $user = $this->ensureDoctor()->load('doctor.specialization');

        return view('dashboard.doctor.settings.profile', [
            'user' => $user,
            'specializations' => Specialization::query()->orderBy('name')->get(),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $this->ensureDoctor()->load('doctor');
        abort_unless($user->doctor, 404, 'Doctor profile not found.');

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
            action: 'doctor.profile_updated.web',
            entity: $user,
            oldValues: ['user' => $oldUser],
            newValues: ['user' => $user->fresh()->only(['first_name', 'last_name', 'email', 'avatar'])],
            request: $request,
            userId: $user->id
        );

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updateProfessional(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $this->ensureDoctor()->load('doctor');
        abort_unless($user->doctor, 404, 'Doctor profile not found.');

        $validated = $request->validateWithBag('profile', [
            'specialization_id' => ['nullable', 'integer', 'exists:specializations,id'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'certificate_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'bio' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('certificate_file')) {
            $validated['certificate_file'] = $request->file('certificate_file')->store('doctor-certificates', 'public');
        }

        $oldValues = $user->doctor->only(['specialization_id', 'license_number', 'certificate_file', 'bio']);

        $user->doctor->update([
            'specialization_id' => $validated['specialization_id'] ?? null,
            'license_number' => $validated['license_number'] ?? null,
            'certificate_file' => $validated['certificate_file'] ?? $user->doctor->certificate_file,
            'bio' => $validated['bio'] ?? null,
        ]);

        $this->auditLogService->log(
            action: 'doctor.professional_profile_updated.web',
            entity: $user->doctor,
            oldValues: $oldValues,
            newValues: $user->doctor->fresh()->only(['specialization_id', 'license_number', 'certificate_file', 'bio']),
            request: $request,
            userId: $user->id
        );

        return back()->with('success', 'Professional information updated successfully.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $this->ensureDoctor();

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
            action: 'doctor.password_updated.web',
            entity: $user,
            request: $request,
            userId: $user->id
        );

        return back()->with('success', 'Password updated successfully.');
    }
}
