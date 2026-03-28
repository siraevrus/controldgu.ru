<?php

namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

final class Audit
{
    public static function record(
        string $action,
        ?string $auditableType = null,
        ?int $auditableId = null,
        array $properties = [],
    ): void {
        /** @var Authenticatable|null $user */
        $user = Auth::user();

        AuditLog::query()->create([
            'user_id' => $user?->getAuthIdentifier(),
            'action' => $action,
            'auditable_type' => $auditableType,
            'auditable_id' => $auditableId,
            'properties' => $properties ?: null,
            'ip_address' => request()?->ip(),
            'created_at' => now(),
        ]);
    }
}
