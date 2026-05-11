<?php

namespace App\Http\Controllers\Web\Patient;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class DashboardController extends BasePatientController
{
    public function index(): View
    {
        $patient = $this->ensurePatient();

        $stats = [
            'appointments' => Appointment::query()->where('patient_id', $patient->id)->count(),
            'appointments_today' => Appointment::query()->where('patient_id', $patient->id)->whereDate('scheduled_at', Carbon::today())->count(),
            'upcoming' => Appointment::query()->where('patient_id', $patient->id)->whereIn('status', [
                AppointmentStatus::Scheduled->value,
                AppointmentStatus::Confirmed->value,
                AppointmentStatus::InProgress->value,
            ])->count(),
            'completed' => Appointment::query()->where('patient_id', $patient->id)->where('status', AppointmentStatus::Completed)->count(),
            'cancelled' => Appointment::query()->where('patient_id', $patient->id)->where('status', AppointmentStatus::Cancelled)->count(),
        ];

        $statusCounts = Appointment::query()
            ->where('patient_id', $patient->id)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $monthStart = Carbon::now()->startOfMonth()->subMonths(5);
        $monthlyRaw = Appointment::query()
            ->where('patient_id', $patient->id)
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

        return view('dashboard.patient.index', [
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

