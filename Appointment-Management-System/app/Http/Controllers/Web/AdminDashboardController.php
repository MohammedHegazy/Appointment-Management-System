<?php

namespace App\Http\Controllers\Web;

use App\Enums\AppointmentStatus;
use App\Enums\AccountStatus;
use App\Enums\RoleType;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Doctor;
use App\Models\Hospital;
use App\Models\MedicalProfile;
use App\Models\Specialization;
use App\Models\User;
use App\Services\AuditLogService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminDashboardController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function index(): View
    {
        $this->ensureAdmin();

        $stats = [
            'users' => User::query()->count(),
            'hospitals' => Hospital::query()->count(),
            'doctors' => Doctor::query()->count(),
            'appointments' => Appointment::query()->count(),
            'appointments_today' => Appointment::query()
                ->whereDate('scheduled_at', Carbon::today())
                ->count(),
            'audit_logs' => AuditLog::query()->count(),
        ];

        $roleCounts = User::query()
            ->select('role', DB::raw('COUNT(*) as total'))
            ->groupBy('role')
            ->pluck('total', 'role');

        $statusCounts = Appointment::query()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $monthStart = Carbon::now()->startOfMonth()->subMonths(5);
        $monthlyRaw = Appointment::query()
            ->selectRaw("DATE_FORMAT(scheduled_at, '%Y-%m') as ym, COUNT(*) as total")
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '>=', $monthStart)
            ->groupBy('ym')
            ->orderBy('ym')
            ->pluck('total', 'ym');

        $months = [];
        $monthlyTotals = [];
        for ($i = 0; $i < 6; $i++) {
            $current = $monthStart->copy()->addMonths($i);
            $key = $current->format('Y-m');
            $months[] = $current->format('M Y');
            $monthlyTotals[] = (int) ($monthlyRaw[$key] ?? 0);
        }

        return view('dashboard.admin.index', [
            'stats' => $stats,
            'charts' => [
                'appointmentsMonthly' => [
                    'labels' => $months,
                    'values' => $monthlyTotals,
                ],
                'appointmentStatus' => [
                    'labels' => collect(AppointmentStatus::cases())->map(fn (AppointmentStatus $case) => ucfirst(str_replace('_', ' ', $case->value)))->all(),
                    'values' => collect(AppointmentStatus::cases())->map(fn (AppointmentStatus $case) => (int) ($statusCounts[$case->value] ?? 0))->all(),
                ],
                'userRoles' => [
                    'labels' => collect(RoleType::cases())->map(fn (RoleType $case) => ucfirst(str_replace('_', ' ', $case->value)))->all(),
                    'values' => collect(RoleType::cases())->map(fn (RoleType $case) => (int) ($roleCounts[$case->value] ?? 0))->all(),
                ],
            ],
        ]);
    }

    public function auditLogs(Request $request): View
    {
        $this->ensureAdmin();

        $query = AuditLog::query()
            ->with('user:id,first_name,last_name,email')
            ->latest('id');

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('action', 'like', "%{$search}%")
                    ->orWhere('entity_type', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userBuilder) use ($search): void {
                        $userBuilder->where('email', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        $action = trim((string) $request->query('action', ''));
        if ($action !== '') {
            $query->where('action', $action);
        }

        $entityType = trim((string) $request->query('entity_type', ''));
        if ($entityType !== '') {
            $query->where('entity_type', $entityType);
        }

        $logs = $query->paginate(20);
        $logs->appends($request->query());

        return view('dashboard.admin.audit_logs.index', [
            'logs' => $logs,
            'actions' => AuditLog::query()->select('action')->distinct()->orderBy('action')->pluck('action'),
            'entityTypes' => AuditLog::query()->select('entity_type')->distinct()->orderBy('entity_type')->pluck('entity_type'),
        ]);
    }

    public function patientsIndex(Request $request): View
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

    public function patientInfo(User $patient): View
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

    public function updatePatient(User $patient): View
    {
        $this->ensureAdmin();
        abort_unless($patient->role === RoleType::Patient, 404);

        $patient->load('medicalProfile');

        return view('dashboard.admin.patients.update', [
            'patient' => $patient,
        ]);
    }

    public function updatePatientSubmit(Request $request, User $patient): RedirectResponse
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

    public function doctorsIndex(Request $request): View
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

    public function createDoctor(): View
    {
        $this->ensureAdmin();

        return view('dashboard.admin.doctors.create', [
            'specializations' => Specialization::query()->orderBy('name')->get(),
        ]);
    }

    public function createDoctorSubmit(Request $request): RedirectResponse
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

    public function doctorInfo(Doctor $doctor): View
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

    public function updateDoctor(Doctor $doctor): View
    {
        $this->ensureAdmin();
        $doctor->load(['user', 'specialization']);
        abort_unless($doctor->user && $doctor->user->role === RoleType::Doctor && $doctor->user->hospital_id === null, 404);

        return view('dashboard.admin.doctors.update', [
            'doctor' => $doctor,
            'specializations' => Specialization::query()->orderBy('name')->get(),
        ]);
    }

    public function updateDoctorSubmit(Request $request, Doctor $doctor): RedirectResponse
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

    public function hospitalsIndex(Request $request): View
    {
        $this->ensureAdmin();

        $query = Hospital::query()
            ->withCount([
                'users as hospital_admins_count' => fn ($builder) => $builder->where('role', RoleType::HospitalAdmin),
                'users as doctors_count' => fn ($builder) => $builder->where('role', RoleType::Doctor),
                'users as members_count',
            ])
            ->latest('id');

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $hospitals = $query->paginate(20);
        $hospitals->appends($request->query());

        return view('dashboard.admin.hospitals.index', [
            'hospitals' => $hospitals,
        ]);
    }

    public function createHospital(): View
    {
        $this->ensureAdmin();

        return view('dashboard.admin.hospitals.create');
    }

    public function createHospitalSubmit(Request $request): RedirectResponse
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:50'],
        ]);

        $hospital = Hospital::query()->create($validated);

        $this->auditLogService->log(
            action: 'admin.hospital_created.web',
            entity: $hospital,
            newValues: $hospital->only(['name', 'address', 'phone']),
            request: $request,
            userId: auth()->id()
        );

        return redirect()
            ->route('admin.hospitals.info', $hospital)
            ->with('success', 'Hospital created successfully.');
    }

    public function hospitalInfo(Hospital $hospital): View
    {
        $this->ensureAdmin();

        $admins = User::query()
            ->where('hospital_id', $hospital->id)
            ->where('role', RoleType::HospitalAdmin)
            ->select('id', 'first_name', 'last_name', 'email', 'status')
            ->latest('id')
            ->limit(8)
            ->get();

        $doctors = Doctor::query()
            ->whereHas('user', function ($builder) use ($hospital): void {
                $builder->where('hospital_id', $hospital->id)
                    ->where('role', RoleType::Doctor);
            })
            ->with(['user:id,first_name,last_name,email,status,hospital_id', 'specialization'])
            ->latest('id')
            ->limit(8)
            ->get();

        $stats = [
            'admins' => User::query()->where('hospital_id', $hospital->id)->where('role', RoleType::HospitalAdmin)->count(),
            'doctors' => User::query()->where('hospital_id', $hospital->id)->where('role', RoleType::Doctor)->count(),
            'patients' => User::query()->where('hospital_id', $hospital->id)->where('role', RoleType::Patient)->count(),
            'all_members' => User::query()->where('hospital_id', $hospital->id)->count(),
        ];

        return view('dashboard.admin.hospitals.info', [
            'hospital' => $hospital,
            'admins' => $admins,
            'doctors' => $doctors,
            'stats' => $stats,
        ]);
    }

    public function updateHospital(Hospital $hospital): View
    {
        $this->ensureAdmin();

        return view('dashboard.admin.hospitals.update', [
            'hospital' => $hospital,
        ]);
    }

    public function updateHospitalSubmit(Request $request, Hospital $hospital): RedirectResponse
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:50'],
        ]);

        $oldValues = $hospital->only(['name', 'address', 'phone']);
        $hospital->update($validated);

        $this->auditLogService->log(
            action: 'admin.hospital_updated.web',
            entity: $hospital,
            oldValues: $oldValues,
            newValues: $hospital->fresh()->only(['name', 'address', 'phone']),
            request: $request,
            userId: auth()->id()
        );

        return redirect()
            ->route('admin.hospitals.info', $hospital)
            ->with('success', 'Hospital updated successfully.');
    }

    public function hospitalUsersIndex(Request $request, Hospital $hospital): View
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

    public function hospitalUserInfo(Hospital $hospital, User $user): View
    {
        $this->ensureAdmin();
        abort_unless((int) $user->hospital_id === (int) $hospital->id, 404);

        $user->load(['doctor.specialization', 'medicalProfile']);

        return view('dashboard.admin.hospitals.users.info', [
            'hospital' => $hospital,
            'user' => $user,
        ]);
    }

    public function hospitalUserUpdate(Hospital $hospital, User $user): View
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

    public function hospitalUserUpdateSubmit(Request $request, Hospital $hospital, User $user): RedirectResponse
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

    public function hospitalDoctorsIndex(Request $request, Hospital $hospital): View
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

    public function hospitalDoctorInfo(Hospital $hospital, Doctor $doctor): View
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

    public function hospitalDoctorUpdate(Hospital $hospital, Doctor $doctor): View
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

    public function hospitalDoctorUpdateSubmit(Request $request, Hospital $hospital, Doctor $doctor): RedirectResponse
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

    public function settingsProfile(): View
    {
        $this->ensureAdmin();

        return view('dashboard.admin.settings.profile', [
            'user' => auth()->user(),
        ]);
    }

    public function settingsProfileUpdate(Request $request): RedirectResponse
    {
        $this->ensureAdmin();
        /** @var User $user */
        $user = auth()->user();

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
            action: 'admin.profile_updated.web',
            entity: $user,
            oldValues: $oldValues,
            newValues: $user->fresh()->only(['first_name', 'last_name', 'email', 'avatar']),
            request: $request,
            userId: $user->id
        );

        return back()->with('success', 'Profile updated successfully.');
    }

    public function settingsPasswordUpdate(Request $request): RedirectResponse
    {
        $this->ensureAdmin();
        /** @var User $user */
        $user = auth()->user();

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
            action: 'admin.password_updated.web',
            entity: $user,
            request: $request,
            userId: $user->id
        );

        return back()->with('success', 'Password updated successfully.');
    }

    private function ensureAdmin(): void
    {
        abort_unless(auth()->user()?->role === RoleType::Admin, 403, 'Admin access only.');
    }
}
