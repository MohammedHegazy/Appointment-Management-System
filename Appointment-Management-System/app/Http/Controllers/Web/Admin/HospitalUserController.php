<?php

namespace App\Http\Controllers\Web\Admin;

use App\Enums\AccountStatus;
use App\Enums\RoleType;
use App\Models\Doctor;
use App\Models\Hospital;
use App\Models\MedicalProfile;
use App\Models\Specialization;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HospitalUserController extends BaseAdminController
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function index(Request $request, Hospital $hospital): View
    {
        $this->ensureAdmin();

        $query = User::query()
            ->where('hospital_id', $hospital->id)
            ->with(['doctor.specialization', 'medicalProfile'])
            ->latest('id');

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $role = trim((string) $request->query('role', ''));
        if ($role !== '') {
            $query->where('role', $role);
        }

        $status = trim((string) $request->query('status', ''));
        if ($status !== '') {
            $query->where('status', $status);
        }

        $users = $query->paginate(20);
        $users->appends($request->query());

        return view('dashboard.admin.hospitals.users.index', [
            'hospital' => $hospital,
            'users' => $users,
        ]);
    }

    public function show(Hospital $hospital, User $user): View
    {
        $this->ensureAdmin();
        abort_unless((int) $user->hospital_id === (int) $hospital->id, 404);

        $user->load(['doctor.specialization', 'medicalProfile']);

        return view('dashboard.admin.hospitals.users.info', [
            'hospital' => $hospital,
            'user' => $user,
        ]);
    }

    public function edit(Hospital $hospital, User $user): View
    {
        $this->ensureAdmin();
        abort_unless((int) $user->hospital_id === (int) $hospital->id, 404);

        $user->load(['doctor.specialization', 'medicalProfile']);

        return view('dashboard.admin.hospitals.users.update', [
            'hospital' => $hospital,
            'user' => $user,
            'specializations' => Specialization::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Hospital $hospital, User $user): RedirectResponse
    {
        $this->ensureAdmin();
        abort_unless((int) $user->hospital_id === (int) $hospital->id, 404);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', "unique:users,email,{$user->id}"],
            'status' => ['required', 'in:pending,active,inactive'],
            'role' => ['required', 'in:hospital_admin,doctor,patient'],
            'specialization_id' => ['nullable', 'integer', 'exists:specializations,id'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'certificate_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'bio' => ['nullable', 'string'],
            'blood_type' => ['nullable', 'string', 'max:10'],
            'allergies' => ['nullable', 'string'],
            'chronic_conditions' => ['nullable', 'string'],
            'medications' => ['nullable', 'string'],
            'emergency_contact' => ['nullable', 'string', 'max:255'],
        ]);

        if ($request->hasFile('certificate_file')) {
            $validated['certificate_file'] = $request->file('certificate_file')->store('doctor-certificates', 'public');
        }

        $oldValues = [
            'user' => $user->only(['first_name', 'last_name', 'email', 'status', 'role']),
            'doctor' => $user->doctor?->only(['specialization_id', 'license_number', 'certificate_file', 'bio']),
            'medical_profile' => $user->medicalProfile?->only(['blood_type', 'allergies', 'chronic_conditions', 'medications', 'emergency_contact']),
        ];

        $newRole = RoleType::from($validated['role']);
        $user->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'status' => AccountStatus::from($validated['status']),
            'role' => $newRole,
        ]);

        if ($newRole === RoleType::Doctor) {
            Doctor::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'specialization_id' => $validated['specialization_id'] ?? null,
                    'license_number' => $validated['license_number'] ?? null,
                    'certificate_file' => $validated['certificate_file'] ?? $user->doctor?->certificate_file,
                    'bio' => $validated['bio'] ?? null,
                ]
            );
        } else {
            $user->doctor()?->delete();
        }

        if ($newRole === RoleType::Patient) {
            MedicalProfile::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'blood_type' => $validated['blood_type'] ?? null,
                    'allergies' => $validated['allergies'] ?? null,
                    'chronic_conditions' => $validated['chronic_conditions'] ?? null,
                    'medications' => $validated['medications'] ?? null,
                    'emergency_contact' => $validated['emergency_contact'] ?? null,
                ]
            );
        } else {
            $user->medicalProfile()?->delete();
        }

        $user->refresh()->load(['doctor', 'medicalProfile']);

        $this->auditLogService->log(
            action: 'admin.hospital_user_updated.web',
            entity: $user,
            oldValues: $oldValues,
            newValues: [
                'user' => $user->only(['first_name', 'last_name', 'email', 'status', 'role']),
                'doctor' => $user->doctor?->only(['specialization_id', 'license_number', 'certificate_file', 'bio']),
                'medical_profile' => $user->medicalProfile?->only(['blood_type', 'allergies', 'chronic_conditions', 'medications', 'emergency_contact']),
            ],
            request: $request,
            userId: auth()->id()
        );

        return redirect()
            ->route('admin.hospitals.users.info', [$hospital, $user])
            ->with('success', 'Hospital user updated successfully.');
    }
}
