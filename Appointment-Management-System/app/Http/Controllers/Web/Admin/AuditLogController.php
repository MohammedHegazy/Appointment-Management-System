<?php

namespace App\Http\Controllers\Web\Admin;

use App\Models\AuditLog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AuditLogController extends BaseAdminController
{
    public function index(Request $request): View
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
}
