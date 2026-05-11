<?php

namespace App\Http\Controllers\Web\Doctor;

use App\Enums\AppointmentType;
use App\Models\DoctorAvailability;
use App\Services\AuditLogService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AvailabilityController extends BaseDoctorController
{
    private const DAY_LABELS = [
        0 => 'Sunday',
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
    ];

    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function index(): View
    {
        $doctor = $this->resolveDoctor();

        $availabilities = $doctor->availabilities()
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        $entriesByDay = [];
        foreach ($availabilities as $slot) {
            $entriesByDay[(int) $slot->day_of_week][] = [
                'title' => ucfirst($slot->appointment_type->value),
                'time' => substr((string) $slot->start_time, 0, 5) . ' - ' . substr((string) $slot->end_time, 0, 5),
                'status' => $slot->is_available ? 'Available' : 'Unavailable',
            ];
        }

        $calendarDays = collect(self::DAY_LABELS)
            ->map(fn (string $label, int $day) => ['key' => $day, 'label' => $label])
            ->values()
            ->all();

        return view('dashboard.doctor.availability.index', [
            'doctor' => $doctor,
            'availabilities' => $availabilities,
            'entriesByDay' => $entriesByDay,
            'calendarDays' => $calendarDays,
            'dayLabels' => self::DAY_LABELS,
        ]);
    }

    public function create(): View
    {
        return view('dashboard.doctor.availability.create', [
            'dayLabels' => self::DAY_LABELS,
            'appointmentTypes' => AppointmentType::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $doctor = $this->resolveDoctor();

        $validated = $request->validate([
            'day_of_week' => ['required', 'integer', 'between:0,6'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'appointment_type' => ['required', 'in:online,onsite'],
            'is_available' => ['required', 'boolean'],
        ]);

        $availability = $doctor->availabilities()->create([
            'day_of_week' => (int) $validated['day_of_week'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'appointment_type' => AppointmentType::from($validated['appointment_type']),
            'is_available' => (bool) $validated['is_available'],
        ]);

        $this->auditLogService->log(
            action: 'doctor.availability_created.web',
            entity: $availability,
            newValues: $availability->only(['doctor_id', 'day_of_week', 'start_time', 'end_time', 'appointment_type', 'is_available']),
            request: $request,
            userId: auth()->id()
        );

        return redirect()->route('doctor.availability.index')->with('success', 'Availability slot created successfully.');
    }

    public function edit(DoctorAvailability $availability): View
    {
        $availability = $this->resolveAvailability($availability);

        return view('dashboard.doctor.availability.update', [
            'availability' => $availability,
            'dayLabels' => self::DAY_LABELS,
            'appointmentTypes' => AppointmentType::cases(),
        ]);
    }

    public function update(Request $request, DoctorAvailability $availability): RedirectResponse
    {
        $availability = $this->resolveAvailability($availability);

        $validated = $request->validate([
            'day_of_week' => ['required', 'integer', 'between:0,6'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'appointment_type' => ['required', 'in:online,onsite'],
            'is_available' => ['required', 'boolean'],
        ]);

        $oldValues = $availability->only(['day_of_week', 'start_time', 'end_time', 'appointment_type', 'is_available']);
        $availability->update([
            'day_of_week' => (int) $validated['day_of_week'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'appointment_type' => AppointmentType::from($validated['appointment_type']),
            'is_available' => (bool) $validated['is_available'],
        ]);

        $this->auditLogService->log(
            action: 'doctor.availability_updated.web',
            entity: $availability,
            oldValues: $oldValues,
            newValues: $availability->fresh()->only(['day_of_week', 'start_time', 'end_time', 'appointment_type', 'is_available']),
            request: $request,
            userId: auth()->id()
        );

        return redirect()->route('doctor.availability.index')->with('success', 'Availability slot updated successfully.');
    }

    public function destroy(Request $request, DoctorAvailability $availability): RedirectResponse
    {
        $availability = $this->resolveAvailability($availability);
        $oldValues = $availability->only(['day_of_week', 'start_time', 'end_time', 'appointment_type', 'is_available']);
        $availability->delete();

        $this->auditLogService->log(
            action: 'doctor.availability_deleted.web',
            entity: 'DoctorAvailability',
            entityId: $availability->id,
            oldValues: $oldValues,
            request: $request,
            userId: auth()->id()
        );

        return redirect()->route('doctor.availability.index')->with('success', 'Availability slot deleted successfully.');
    }

    private function resolveDoctor(): \App\Models\Doctor
    {
        $doctor = $this->ensureDoctor()->doctor;
        abort_unless($doctor, 404, 'Doctor profile not found.');

        return $doctor;
    }

    private function resolveAvailability(DoctorAvailability $availability): DoctorAvailability
    {
        $doctorId = (int) ($this->ensureDoctor()->doctor?->id ?? 0);
        abort_unless((int) $availability->doctor_id === $doctorId, 404);

        return $availability;
    }
}

