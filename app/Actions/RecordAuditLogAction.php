<?php

namespace App\Actions;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class RecordAuditLogAction
{
    /**
     * @param  array<string, mixed>  $oldValues
     * @param  array<string, mixed>  $newValues
     */
    public function handle(string $event, ?Model $auditable, string $description, array $oldValues = [], array $newValues = []): AuditLog
    {
        return AuditLog::create([
            'actor_user_id' => auth()->id(),
            'event' => $event,
            'auditable_type' => $auditable?->getMorphClass(),
            'auditable_id' => $auditable?->getKey(),
            'description' => $description,
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
