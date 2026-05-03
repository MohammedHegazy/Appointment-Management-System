<?php

namespace App\Http\Controllers\Web\Admin;

use App\Enums\AccountStatus;
use App\Enums\RoleType;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Hospital;
use App\Models\Specialization;
use App\Services\AuditLogService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HospitalDoctorController extends BaseAdminController
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function index(Request $request, Hospital $hospital): View
    {
        $this->ensureAdmin();

        $query = Doctor::query()
            ->whereHas('user', function ($builder) use ($hospital): void {
                $builder->where('hospital_id', $hospital->id)
                    ->where('role', RoleType::Doctor);
            })
            ->with(['user', 'specialization'])
            ->withCount('appointments')
            ->latest('id');

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('license_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userBuilder) use ($search): void {
                        $userBuilder->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $status = trim((string) $request->query('status', ''));
        if ($status !== '') {
            $query->whereHas('user', fn ($builder) => $builder->where('status', $status));
        }

        $doctors = $query->paginate(20);
        $doctors->appends($request->query());

        return view('dashboard.admin.hospitals.doctors.index', [
            'hospital' => $hospital,
            'doctors' => $doctors,
        ]);
    }

    public function show(Hospital $hospital, Doctor $doctor): View
    {
        $this->ensureAdmin();
        $doctor->load(['user', 'specialization']);
        abort_unless((int) $doctor->user?->hospital_id === (int) $hospital->id, 404);

        $recentAppointments = Appointment::query()
            ->where('doctor_id', $doctor->id)
            ->with('patient:id,first_name,last_name')
            ->latest('scheduled_at')
            ->limit(8)
            ->get();

        return view('dashboard.admin.hospitals.doctors.info', [
            'hospital' => $hospital,
            'doctor' => $doctor,
            'recentAppointments' => $recentAppointments,
        ]);
    }

    public function edit(Hospital $hospital, Doctor $doctor): View
    {
        $this->ensureAdmin();
        $doctor->load('user');
        abort_unless((int) $doctor->user?->hospital_id === (int) $hospital->id, 404);

        return view('dashboard.admin.hospitals.doctors.update', [
            'hospital' => $hospital,
            'doctor' => $doctor,
            'specializations' => Specialization::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Hospital $hospital, Doctor $doctor): RedirectResponse
    {
        $this->ensureAdmin();
        $doctor->load('user');
        abort_unless((int) $doctor->user?->hospital_id === (int) $hospital->id, 404);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', "unique:users,email,{$doctor->user_id}"],
            'status' => ['required', 'in:pending,active,inactive'],
            'specialization_id' => ['nullable', 'integer', 'exists:specializations,id'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'certificate_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'bio' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('certificate_file')) {
            $validated['certificate_file'] = $request->file('certificate_file')->store('doctor-certificates', 'public');
        }

        $oldValues = [
            'user' => $doctor->user->only(['first_name', 'last_name', 'email', 'status']),
            'doctor' => $doctor->only(['specialization_id', 'license_number', 'certificate_file', 'bio']),
        ];

        $doctor->user->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'status' => AccountStatus::from($validated['status']),
        ]);

        $doctor->update([
            'specialization_id' => $validated['specialization_id'] ?? null,
            'license_number' => $validated['license_number'] ?? null,
            'certificate_file' => $validated['certificate_file'] ?? $doctor->certificate_file,
            'bio' => $validated['bio'] ?? null,
        ]);

        $this->auditLogService->log(
            action: 'admin.hospital_doctor_updated.web',
            entity: $doctor,
            oldValues: $oldValues,
            newValues: [
                'user' => $doctor->fresh()->user->only(['first_name', 'last_name', 'email', 'status']),
                'doctor' => $doctor->fresh()->only(['specialization_id', 'license_number', 'certificate_file', 'bio']),
            ],
            request: $request,
            userId: auth()->id()
        );

        return redirect()
            ->route('admin.hospitals.doctors.info', [$hospital, $doctor])
            ->with('success', 'Hospital doctor updated successfully.');
    }
}
