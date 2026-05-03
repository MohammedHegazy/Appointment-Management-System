<?php

namespace App\Http\Controllers\Web\Admin;

use App\Enums\AccountStatus;
use App\Enums\RoleType;
use App\Models\Appointment;
use App\Models\MedicalProfile;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PatientController extends BaseAdminController
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function index(Request $request): View
    {
        $this->ensureAdmin();

        $query = User::query()
            ->where('role', RoleType::Patient)
            ->with('medicalProfile')
            ->withCount('appointments')
            ->latest('id');

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $status = trim((string) $request->query('status', ''));
        if ($status !== '') {
            $query->where('status', $status);
        }

        $patients = $query->paginate(20);
        $patients->appends($request->query());

        return view('dashboard.admin.patients.index', [
            'patients' => $patients,
        ]);
    }

    public function show(User $patient): View
    {
        $this->ensureAdmin();
        abort_unless($patient->role === RoleType::Patient, 404);

        $patient->load('medicalProfile');

        $recentAppointments = Appointment::query()
            ->where('patient_id', $patient->id)
            ->with('doctor.user:id,first_name,last_name')
            ->latest('scheduled_at')
            ->limit(8)
            ->get();

        return view('dashboard.admin.patients.info', [
            'patient' => $patient,
            'recentAppointments' => $recentAppointments,
        ]);
    }

    public function edit(User $patient): View
    {
        $this->ensureAdmin();
        abort_unless($patient->role === RoleType::Patient, 404);

        $patient->load('medicalProfile');

        return view('dashboard.admin.patients.update', [
            'patient' => $patient,
        ]);
    }

    public function update(Request $request, User $patient): RedirectResponse
    {
        $this->ensureAdmin();
        abort_unless($patient->role === RoleType::Patient, 404);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', "unique:users,email,{$patient->id}"],
            'status' => ['required', 'in:active,inactive'],
            'blood_type' => ['nullable', 'string', 'max:10'],
            'allergies' => ['nullable', 'string'],
            'chronic_conditions' => ['nullable', 'string'],
            'medications' => ['nullable', 'string'],
            'emergency_contact' => ['nullable', 'string', 'max:255'],
        ]);

        $oldUserValues = $patient->only(['first_name', 'last_name', 'email', 'status']);
        $oldProfileValues = optional($patient->medicalProfile)->only([
            'blood_type',
            'allergies',
            'chronic_conditions',
            'medications',
            'emergency_contact',
        ]);

        $patient->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'status' => AccountStatus::from($validated['status']),
        ]);

        MedicalProfile::query()->updateOrCreate(
            ['user_id' => $patient->id],
            [
                'blood_type' => $validated['blood_type'] ?? null,
                'allergies' => $validated['allergies'] ?? null,
                'chronic_conditions' => $validated['chronic_conditions'] ?? null,
                'medications' => $validated['medications'] ?? null,
                'emergency_contact' => $validated['emergency_contact'] ?? null,
            ]
        );

        $this->auditLogService->log(
            action: 'admin.patient_updated.web',
            entity: $patient,
            oldValues: [
                'user' => $oldUserValues,
                'medical_profile' => $oldProfileValues,
            ],
            newValues: [
                'user' => $patient->fresh()->only(['first_name', 'last_name', 'email', 'status']),
                'medical_profile' => $patient->fresh()->medicalProfile?->only([
                    'blood_type',
                    'allergies',
                    'chronic_conditions',
                    'medications',
                    'emergency_contact',
                ]),
            ],
            request: $request,
            userId: auth()->id()
        );

        return redirect()
            ->route('admin.patients.info', $patient)
            ->with('success', 'Patient profile updated successfully.');
    }
}
