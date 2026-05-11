<?php

namespace App\Http\Controllers\Web\Doctor;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentType;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Services\AuditLogService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppointmentController extends BaseDoctorController
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function index(Request $request): View
    {
        $doctorId = (int) ($this->ensureDoctor()->doctor?->id ?? 0);
        abort_unless($doctorId > 0, 404, 'Doctor profile not found.');

        $query = Appointment::query()
            ->where('doctor_id', $doctorId)
            ->with(['patient:id,first_name,last_name,email'])
            ->latest('scheduled_at');

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->whereHas('patient', function ($builder) use ($search): void {
                $builder->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
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

        return view('dashboard.doctor.appointments.index', [
            'appointments' => $appointments,
        ]);
    }

    public function show(Appointment $appointment): View
    {
        $appointment = $this->resolveAppointment($appointment);

        $patientHistory = MedicalRecord::query()
            ->whereHas('appointment', fn ($builder) => $builder->where('patient_id', $appointment->patient_id))
            ->with(['appointment' => fn ($builder) => $builder->select('id', 'scheduled_at', 'doctor_id', 'patient_id'), 'appointment.doctor.user:id,first_name,last_name'])
            ->latest('id')
            ->limit(10)
            ->get();

        return view('dashboard.doctor.appointments.info', [
            'appointment' => $appointment,
            'patientHistory' => $patientHistory,
            'statuses' => AppointmentStatus::cases(),
        ]);
    }

    public function update(Request $request, Appointment $appointment): RedirectResponse
    {
        $appointment = $this->resolveAppointment($appointment);

        $validated = $request->validate([
            'status' => ['required', 'in:scheduled,confirmed,in_progress,completed,cancelled,no_show'],
            'notes' => ['nullable', 'string'],
            'diagnosis' => ['nullable', 'string'],
            'treatment' => ['nullable', 'string'],
            'prescription' => ['nullable', 'string'],
            'medical_notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($appointment, $validated, $request): void {
            $oldAppointmentValues = $appointment->only(['status', 'notes']);
            $oldStatus = (string) $appointment->status->value;

            $appointment->update([
                'status' => AppointmentStatus::from($validated['status']),
                'notes' => $validated['notes'] ?? null,
            ]);

            if ($oldStatus !== $validated['status']) {
                $appointment->statusHistory()->create([
                    'old_status' => AppointmentStatus::from($oldStatus),
                    'new_status' => AppointmentStatus::from($validated['status']),
                    'notes' => $validated['notes'] ?? null,
                ]);
            }

            $recordValues = [
                'diagnosis' => $validated['diagnosis'] ?? null,
                'treatment' => $validated['treatment'] ?? null,
                'prescription' => $validated['prescription'] ?? null,
                'notes' => $validated['medical_notes'] ?? null,
            ];

            $hasMedicalPayload = collect($recordValues)->filter(fn ($value) => filled($value))->isNotEmpty();
            if ($hasMedicalPayload) {
                $record = $appointment->medicalRecords()->latest('id')->first() ?? new MedicalRecord(['appointment_id' => $appointment->id]);
                $oldRecordValues = $record->exists ? $record->only(['diagnosis', 'treatment', 'prescription', 'notes']) : null;
                $record->fill($recordValues);
                $record->save();

                $this->auditLogService->log(
                    action: 'doctor.medical_record_updated.web',
                    entity: $record,
                    oldValues: $oldRecordValues,
                    newValues: $record->fresh()->only(['appointment_id', 'diagnosis', 'treatment', 'prescription', 'notes']),
                    request: $request,
                    userId: auth()->id()
                );
            }

            $this->auditLogService->log(
                action: 'doctor.appointment_updated.web',
                entity: $appointment,
                oldValues: $oldAppointmentValues,
                newValues: $appointment->fresh()->only(['status', 'notes']),
                request: $request,
                userId: auth()->id()
            );
        });

        return redirect()
            ->route('doctor.appointments.info', $appointment)
            ->with('success', 'Appointment and notes updated successfully.');
    }

    private function resolveAppointment(Appointment $appointment): Appointment
    {
        $doctorId = (int) ($this->ensureDoctor()->doctor?->id ?? 0);
        abort_unless($doctorId > 0, 404, 'Doctor profile not found.');

        $appointment->load([
            'patient:id,first_name,last_name,email',
            'patient.medicalProfile',
            'medicalRecords' => fn ($builder) => $builder->latest('id'),
        ]);

        abort_unless((int) $appointment->doctor_id === $doctorId, 404);

        return $appointment;
    }
}

