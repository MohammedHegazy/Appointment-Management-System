<?php

namespace App\Http\Controllers\Web\Admin;

use App\Enums\RoleType;
use App\Models\Doctor;
use App\Models\Hospital;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HospitalController extends BaseAdminController
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function index(Request $request): View
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

    public function create(): View
    {
        $this->ensureAdmin();

        return view('dashboard.admin.hospitals.create');
    }

    public function store(Request $request): RedirectResponse
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

    public function show(Hospital $hospital): View
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

    public function edit(Hospital $hospital): View
    {
        $this->ensureAdmin();

        return view('dashboard.admin.hospitals.update', [
            'hospital' => $hospital,
        ]);
    }

    public function update(Request $request, Hospital $hospital): RedirectResponse
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
}
