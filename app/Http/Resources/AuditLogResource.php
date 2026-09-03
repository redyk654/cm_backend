<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AuditLog
 */
class AuditLogResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'action' => $this->action,
            'action_label' => $this->action_label,
            'actor_type' => $this->actor_type,
            'status' => $this->status,
            'failure_reason' => $this->failure_reason,
            'auditable_type' => $this->auditable_type ? class_basename($this->auditable_type) : null,
            'auditable_id' => $this->auditable_id,
            'description' => $this->description,
            'old_values' => $this->old_values,
            'new_values' => $this->new_values,
            'ip_address' => $this->ip_address,
            'user' => $this->whenLoaded('user', fn () => $this->user ? [
                'id' => $this->user->id,
                'full_name' => $this->user->full_name,
            ] : null),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
