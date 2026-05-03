<?php

namespace App\Http\Controllers\Web\Admin;

use App\Enums\AccountStatus;
use App\Enums\RoleType;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Specialization;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DoctorController extends BaseAdminController
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function index(Request $request): View
    {
        $this->ensureAdmin();

        $query = Doctor::query()
            ->whereHas('user', function ($builder): void {
                $builder->where('role', RoleType::Doctor)
                    ->whereNull('hospital_id');
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
            $query->whereHas('user', function ($userBuilder) use ($status): void {
                $userBuilder->where('status', $status);
            });
        }

        $doctors = $query->paginate(20);
        $doctors->appends($request->query());

        return view('dashboard.admin.doctors.index', [
            'doctors' => $doctors,
        ]);
    }

    public function create(): View
    {
        $this->ensureAdmin();

        return view('dashboard.admin.doctors.create', [
            'specializations' => Specialization::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'status' => ['required', 'in:pending,active,inactive'],
            'specialization_id' => ['nullable', 'integer', 'exists:specializations,id'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'certificate_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'bio' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('certificate_file')) {
            $validated['certificate_file'] = $request->file('certificate_file')->store('doctor-certificates', 'public');
        }

        $doctor = DB::transaction(function () use ($validated): Doctor {
            $user = User::query()->create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => RoleType::Doctor,
                'status' => AccountStatus::from($validated['status']),
                'hospital_id' => null,
                'email_verified_at' => now(),
            ]);

            return Doctor::query()->create([
                'user_id' => $user->id,
                'specialization_id' => $validated['specialization_id'] ?? null,
                'license_number' => $validated['license_number'] ?? null,
                'certificate_file' => $validated['certificate_file'] ?? null,
                'bio' => $validated['bio'] ?? null,
            ]);
        });

        $this->auditLogService->log(
            action: 'admin.doctor_created.web',
            entity: $doctor,
            newValues: [
                'doctor_id' => $doctor->id,
                'user_id' => $doctor->user_id,
                'specialization_id' => $doctor->specialization_id,
                'certificate_uploaded' => ! empty($validated['certificate_file']),
            ],
            request: $request,
            userId: auth()->id()
        );

        return redirect()
            ->route('admin.doctors.info', $doctor)
            ->with('success', 'Doctor created successfully.');
    }

    public function show(Doctor $doctor): View
    {
        $this->ensureAdmin();
        $doctor->load(['user', 'specialization']);
        abort_unless($doctor->user && $doctor->user->role === RoleType::Doctor && $doctor->user->hospital_id === null, 404);

        $recentAppointments = Appointment::query()
            ->where('doctor_id', $doctor->id)
            ->with('patient:id,first_name,last_name')
            ->latest('scheduled_at')
            ->limit(8)
            ->get();

        return view('dashboard.admin.doctors.info', [
            'doctor' => $doctor,
            'recentAppointments' => $recentAppointments,
        ]);
    }

    public function edit(Doctor $doctor): View
    {
        $this->ensureAdmin();
        $doctor->load(['user', 'specialization']);
        abort_unless($doctor->user && $doctor->user->role === RoleType::Doctor && $doctor->user->hospital_id === null, 404);

        return view('dashboard.admin.doctors.update', [
            'doctor' => $doctor,
            'specializations' => Specialization::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Doctor $doctor): RedirectResponse
    {
        $this->ensureAdmin();
        $doctor->load('user');
        abort_unless($doctor->user && $doctor->user->role === RoleType::Doctor && $doctor->user->hospital_id === null, 404);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', "unique:users,email,{$doctor->user_id}"],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
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
            'password' => ! empty($validated['password']) ? Hash::make($validated['password']) : $doctor->user->password,
        ]);

        $doctor->update([
            'specialization_id' => $validated['specialization_id'] ?? null,
            'license_number' => $validated['license_number'] ?? null,
            'certificate_file' => $validated['certificate_file'] ?? $doctor->certificate_file,
            'bio' => $validated['bio'] ?? null,
        ]);

        $this->auditLogService->log(
            action: 'admin.doctor_updated.web',
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
            ->route('admin.doctors.info', $doctor)
            ->with('success', 'Doctor profile updated successfully.');
    }
}
