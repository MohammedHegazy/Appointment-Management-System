<?php

namespace App\Http\Controllers\Web\Admin;

use App\Enums\AppointmentStatus;
use App\Enums\RoleType;
use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Doctor;
use App\Models\Hospital;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class DashboardController extends BaseAdminController
{
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
}
