<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Point d'entrée unique pour écrire dans le journal d'audit.
 *
 * Garantit la cohérence (acteur, IP, user-agent, horodatage) quelle que soit
 * l'origine de l'événement : trait Auditable, service métier, contrôleur d'auth.
 */
class AuditLogger
{
    /**
     * Journalise un événement lié (ou non) à une entité.
     *
     * @param  array<string, mixed>  $context
     */
    public function log(
        string $action,
        ?Model $auditable = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null,
        array $context = [],
        string $status = AuditLog::STATUS_SUCCESS,
        ?string $failureReason = null,
        ?string $actorType = null,
    ): AuditLog {
        return AuditLog::create([
            'user_id' => auth()->id(),
            'actor_type' => $actorType ?? (auth()->check() ? AuditLog::ACTOR_USER : AuditLog::ACTOR_SYSTEM),
            'action' => $action,
            'status' => $status,
            'failure_reason' => $failureReason,
            'auditable_type' => $auditable?->getMorphClass(),
            'auditable_id' => $auditable?->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'description' => $description,
            'context' => $context ?: null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }

    /**
     * Journalise un changement de modèle (création / modification / suppression).
     *
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     */
    public function logModelChange(string $action, Model $model, ?array $oldValues, ?array $newValues): AuditLog
    {
        return $this->log($action, $model, $oldValues, $newValues);
    }

    /**
     * Journalise un événement d'authentification.
     */
    public function logAuthEvent(string $action, ?User $user, bool $success = true, ?string $failureReason = null): AuditLog
    {
        return AuditLog::create([
            'user_id' => $user?->getKey(),
            'actor_type' => AuditLog::ACTOR_USER,
            'action' => $action,
            'status' => $success ? AuditLog::STATUS_SUCCESS : AuditLog::STATUS_FAILURE,
            'failure_reason' => $failureReason,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }
}
