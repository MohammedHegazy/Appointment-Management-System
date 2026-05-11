<?php

namespace App\Http\Controllers\Web\HospitalDoctor;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class DashboardController extends BaseHospitalDoctorController
{
    public function index(): View
    {
        $user = $this->ensureHospitalDoctor();
        $doctorId = (int) ($user->doctor?->id ?? 0);
        abort_unless($doctorId > 0, 404, 'Doctor profile not found.');

        $stats = [
            'appointments' => Appointment::query()->where('doctor_id', $doctorId)->count(),
            'appointments_today' => Appointment::query()->where('doctor_id', $doctorId)->whereDate('scheduled_at', Carbon::today())->count(),
            'patients' => Appointment::query()->where('doctor_id', $doctorId)->distinct('patient_id')->count('patient_id'),
            'completed' => Appointment::query()->where('doctor_id', $doctorId)->where('status', AppointmentStatus::Completed)->count(),
            'upcoming' => Appointment::query()->where('doctor_id', $doctorId)->whereIn('status', [
                AppointmentStatus::Scheduled->value,
                AppointmentStatus::Confirmed->value,
                AppointmentStatus::InProgress->value,
            ])->count(),
        ];

        $statusCounts = Appointment::query()
            ->where('doctor_id', $doctorId)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $monthStart = Carbon::now()->startOfMonth()->subMonths(5);
        $monthlyRaw = Appointment::query()
            ->where('doctor_id', $doctorId)
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

        return view('dashboard.hospital_doctor.index', [
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
            ],
        ]);
    }
}

