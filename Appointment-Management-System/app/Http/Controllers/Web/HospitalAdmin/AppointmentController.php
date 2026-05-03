<?php

namespace App\Http\Controllers\Web\HospitalAdmin;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentType;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Services\AuditLogService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AppointmentController extends BaseHospitalAdminController
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function index(Request $request): View
    {
        $hospitalId = (int) $this->ensureHospitalAdmin()->hospital_id;

        $query = Appointment::query()
            ->whereHas('doctor.user', fn ($builder) => $builder->where('hospital_id', $hospitalId))
            ->with(['doctor.user:id,first_name,last_name', 'patient:id,first_name,last_name,email'])
            ->latest('scheduled_at');

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->whereHas('patient', function ($patientBuilder) use ($search): void {
                    $patientBuilder->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('doctor.user', function ($doctorBuilder) use ($search): void {
                    $doctorBuilder->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                });
            });
        }

        $status = trim((string) $request->query('status', ''));
        if ($status !== '') {
            $query->where('status', $status);
        }

        $type = trim((string) $request->query('appointment_type', ''));
        if ($type !== '') {
            $query->where('appointment_type', $type);
        }

        $appointments = $query->paginate(20);
        $appointments->appends($request->query());

        return view('dashboard.hospital_admin.appointments.index', [
            'appointments' => $appointments,
        ]);
    }

    public function show(Appointment $appointment): View
    {
        $appointment = $this->resolveAppointment($appointment);

        return view('dashboard.hospital_admin.appointments.info', [
            'appointment' => $appointment,
        ]);
    }

    public function edit(Appointment $appointment): View
    {
        $appointment = $this->resolveAppointment($appointment);
        $hospitalId = (int) $this->ensureHospitalAdmin()->hospital_id;

        $doctors = Doctor::query()
            ->whereHas('user', fn ($builder) => $builder->where('hospital_id', $hospitalId))
            ->with('user:id,first_name,last_name')
            ->orderBy('id')
            ->get();

        return view('dashboard.hospital_admin.appointments.update', [
            'appointment' => $appointment,
            'doctors' => $doctors,
            'statuses' => AppointmentStatus::cases(),
            'types' => AppointmentType::cases(),
        ]);
    }

    public function update(Request $request, Appointment $appointment): RedirectResponse
    {
        $appointment = $this->resolveAppointment($appointment);
        $hospitalId = (int) $this->ensureHospitalAdmin()->hospital_id;

        $validated = $request->validate([
            'doctor_id' => ['required', 'integer'],
            'appointment_type' => ['required', 'in:online,onsite'],
            'scheduled_at' => ['required', 'date'],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:480'],
            'status' => ['required', 'in:scheduled,confirmed,in_progress,completed,cancelled,no_show'],
            'notes' => ['nullable', 'string'],
        ]);

        $doctorAllowed = Doctor::query()
            ->where('id', (int) $validated['doctor_id'])
            ->whereHas('user', fn ($builder) => $builder->where('hospital_id', $hospitalId))
            ->exists();
        abort_unless($doctorAllowed, 422, 'Selected doctor is not in your hospital.');

        $oldValues = $appointment->only(['doctor_id', 'appointment_type', 'scheduled_at', 'duration_minutes', 'status', 'notes']);
        $appointment->update([
            'doctor_id' => (int) $validated['doctor_id'],
            'appointment_type' => AppointmentType::from($validated['appointment_type']),
            'scheduled_at' => $validated['scheduled_at'],
            'duration_minutes' => $validated['duration_minutes'] ?? null,
            'status' => AppointmentStatus::from($validated['status']),
            'notes' => $validated['notes'] ?? null,
        ]);

        $this->auditLogService->log(
            action: 'hospital_admin.appointment_updated.web',
            entity: $appointment,
            oldValues: $oldValues,
            newValues: $appointment->fresh()->only(['doctor_id', 'appointment_type', 'scheduled_at', 'duration_minutes', 'status', 'notes']),
            request: $request,
            userId: auth()->id()
        );

        return redirect()
            ->route('hospital-admin.appointments.info', $appointment)
            ->with('success', 'Appointment updated successfully.');
    }

    private function resolveAppointment(Appointment $appointment): Appointment
    {
        $hospitalId = (int) $this->ensureHospitalAdmin()->hospital_id;

        $appointment->load(['doctor.user:id,first_name,last_name,hospital_id', 'patient:id,first_name,last_name,email']);
        abort_unless((int) ($appointment->doctor?->user?->hospital_id ?? 0) === $hospitalId, 404);

        return $appointment;
    }
}
