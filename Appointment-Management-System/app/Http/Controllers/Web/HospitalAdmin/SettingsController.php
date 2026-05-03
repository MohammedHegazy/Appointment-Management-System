<?php

namespace App\Http\Controllers\Web\HospitalAdmin;

use App\Models\Hospital;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SettingsController extends BaseHospitalAdminController
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function profile(): View
    {
        $user = $this->ensureHospitalAdmin();

        return view('dashboard.hospital_admin.settings.profile', [
            'user' => $user,
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $this->ensureHospitalAdmin();

        $validated = $request->validateWithBag('profile', [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', "unique:users,email,{$user->id}"],
            'avatar' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $oldValues = $user->only(['first_name', 'last_name', 'email', 'avatar']);
        $user->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'avatar' => $validated['avatar'] ?? $user->avatar,
        ]);

        $this->auditLogService->log(
            action: 'hospital_admin.profile_updated.web',
            entity: $user,
            oldValues: $oldValues,
            newValues: $user->fresh()->only(['first_name', 'last_name', 'email', 'avatar']),
            request: $request,
            userId: $user->id
        );

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $this->ensureHospitalAdmin();

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
            action: 'hospital_admin.password_updated.web',
            entity: $user,
            request: $request,
            userId: $user->id
        );

        return back()->with('success', 'Password updated successfully.');
    }

    public function hospitalProfile(): View
    {
        $user = $this->ensureHospitalAdmin();
        $hospital = Hospital::query()->findOrFail((int) $user->hospital_id);

        return view('dashboard.hospital_admin.settings.hospitalProfile', [
            'hospital' => $hospital,
        ]);
    }

    public function updateHospitalProfile(Request $request): RedirectResponse
    {
        $user = $this->ensureHospitalAdmin();
        $hospital = Hospital::query()->findOrFail((int) $user->hospital_id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:50'],
        ]);

        $oldValues = $hospital->only(['name', 'address', 'phone']);
        $hospital->update($validated);

        $this->auditLogService->log(
            action: 'hospital_admin.hospital_profile_updated.web',
            entity: $hospital,
            oldValues: $oldValues,
            newValues: $hospital->fresh()->only(['name', 'address', 'phone']),
            request: $request,
            userId: auth()->id()
        );

        return back()->with('success', 'Hospital settings updated successfully.');
    }
}
