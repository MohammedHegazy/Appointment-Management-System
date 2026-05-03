<?php

namespace App\Http\Controllers\Web\HospitalAdmin;

use App\Enums\AppointmentStatus;
use App\Enums\RoleType;
use App\Models\Appointment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class DashboardController extends BaseHospitalAdminController
{
    public function index(): View
    {
        $user = $this->ensureHospitalAdmin();
        $hospitalId = (int) $user->hospital_id;

        $stats = [
            'hospital_admins' => User::query()->where('hospital_id', $hospitalId)->where('role', RoleType::HospitalAdmin)->count(),
            'doctors' => User::query()->where('hospital_id', $hospitalId)->where('role', RoleType::Doctor)->count(),
            'patients' => Appointment::query()
                ->where('status', AppointmentStatus::Completed)
                ->whereHas('doctor.user', fn ($builder) => $builder->where('hospital_id', $hospitalId))
                ->distinct('patient_id')
                ->count('patient_id'),
            'appointments' => Appointment::query()
                ->whereHas('doctor.user', fn ($builder) => $builder->where('hospital_id', $hospitalId))
                ->count(),
            'appointments_today' => Appointment::query()
                ->whereDate('scheduled_at', Carbon::today())
                ->whereHas('doctor.user', fn ($builder) => $builder->where('hospital_id', $hospitalId))
                ->count(),
        ];

        $roleCounts = User::query()
            ->where('hospital_id', $hospitalId)
            ->select('role', DB::raw('COUNT(*) as total'))
            ->groupBy('role')
            ->pluck('total', 'role');

        $statusCounts = Appointment::query()
            ->whereHas('doctor.user', fn ($builder) => $builder->where('hospital_id', $hospitalId))
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $monthStart = Carbon::now()->startOfMonth()->subMonths(5);
        $monthlyRaw = Appointment::query()
            ->whereHas('doctor.user', fn ($builder) => $builder->where('hospital_id', $hospitalId))
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

        return view('dashboard.hospital_admin.index', [
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
                    'labels' => ['Hospital Admins', 'Doctors', 'Patients'],
                    'values' => [
                        (int) ($roleCounts[RoleType::HospitalAdmin->value] ?? 0),
                        (int) ($roleCounts[RoleType::Doctor->value] ?? 0),
                        (int) ($roleCounts[RoleType::Patient->value] ?? 0),
                    ],
                ],
            ],
        ]);
    }
}
