<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class AuditService
{
    private static ?bool $hasAuditableColumns = null;

    public function log(string $action, string $entityType, ?int $entityId=null, array $meta=[], ?int $actorId=null): void
    {
        $r = request();
        $attributes = [
            'actor_id' => $actorId ?? Auth::id(),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'meta_json' => $meta,
            'ip' => $r?->ip(),
            'user_agent' => substr((string)$r?->userAgent(), 0, 500),
        ];

        if ($this->supportsAuditableColumns()) {
            $attributes['auditable_type'] = $entityType;
            $attributes['auditable_id'] = $entityId;
        }

        AuditLog::query()->create($attributes);
    }

    private function supportsAuditableColumns(): bool
    {
        if (self::$hasAuditableColumns !== null) {
            return self::$hasAuditableColumns;
        }

        return self::$hasAuditableColumns = Schema::hasColumns('audit_logs', [
            'auditable_type',
            'auditable_id',
        ]);
    }
}
