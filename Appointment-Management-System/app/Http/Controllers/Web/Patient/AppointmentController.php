<?php

namespace App\Http\Controllers\Web\Patient;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Services\AuditLogService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AppointmentController extends BasePatientController
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function index(Request $request): View
    {
        $patient = $this->ensurePatient();

        $query = Appointment::query()
            ->where('patient_id', $patient->id)
            ->with(['doctor.user:id,first_name,last_name', 'doctor.specialization:id,name', 'payment'])
            ->latest('scheduled_at');

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->whereHas('doctor.user', function ($builder) use ($search): void {
                $builder->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
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

        return view('dashboard.patient.appointments.index', [
            'appointments' => $appointments,
        ]);
    }

    public function show(Appointment $appointment): View
    {
        $appointment = $this->resolveAppointment($appointment);

        return view('dashboard.patient.appointments.info', [
            'appointment' => $appointment,
        ]);
    }

    public function cancel(Request $request, Appointment $appointment): RedirectResponse
    {
        $appointment = $this->resolveAppointment($appointment);

        abort_unless(
            in_array($appointment->status, [AppointmentStatus::Scheduled, AppointmentStatus::Confirmed, AppointmentStatus::InProgress], true),
            422,
            'Only active appointments can be cancelled.'
        );

        $oldValues = $appointment->only(['status', 'notes']);
        $note = trim((string) $request->input('cancel_note', ''));
        $mergedNote = trim(($appointment->notes ? $appointment->notes . PHP_EOL : '') . ($note !== '' ? '[Patient cancellation] ' . $note : '[Patient cancellation]'));

        $appointment->update([
            'status' => AppointmentStatus::Cancelled,
            'notes' => $mergedNote,
        ]);

        $appointment->statusHistory()->create([
            'old_status' => $oldValues['status'],
            'new_status' => AppointmentStatus::Cancelled,
            'notes' => $note !== '' ? $note : 'Cancelled by patient',
        ]);

        $this->auditLogService->log(
            action: 'patient.appointment_cancelled.web',
            entity: $appointment,
            oldValues: $oldValues,
            newValues: $appointment->fresh()->only(['status', 'notes']),
            request: $request,
            userId: auth()->id()
        );

        return redirect()->route('patient.appointments.info', $appointment)->with('success', 'Appointment cancelled successfully.');
    }

    private function resolveAppointment(Appointment $appointment): Appointment
    {
        $patient = $this->ensurePatient();

        $appointment->load([
            'doctor.user:id,first_name,last_name,email',
            'doctor.specialization:id,name',
            'payment',
            'medicalRecords' => fn ($builder) => $builder->latest('id'),
        ]);

        abort_unless((int) $appointment->patient_id === (int) $patient->id, 404);

        return $appointment;
    }
}

