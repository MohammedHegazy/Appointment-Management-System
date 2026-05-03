<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditLogService
{
    public function log(
        string $action,
        Model|string $entity,
        int|string|null $entityId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?Request $request = null,
        ?int $userId = null
    ): AuditLog {
        [$entityType, $resolvedEntityId] = $this->resolveEntity($entity, $entityId);

        return AuditLog::create([
            'user_id' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => (int) $resolvedEntityId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }

    private function resolveEntity(Model|string $entity, int|string|null $entityId): array
    {
        if ($entity instanceof Model) {
            return [class_basename($entity), (int) $entity->getKey()];
        }

        return [(string) $entity, (int) ($entityId ?? 0)];
    }
}
