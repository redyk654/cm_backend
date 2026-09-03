<?php

declare(strict_types=1);

namespace App\Concerns;

use App\Models\AuditLog;
use App\Services\AuditLogger;
use Illuminate\Database\Eloquent\Model;

/**
 * Journalise automatiquement les créations / modifications / suppressions du
 * modèle dans `audit_logs`.
 *
 * Opt-in : `use Auditable;` sur le modèle concerné.
 * Personnalisation : `protected array $auditExclude = ['champ_technique'];`
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function (Model $model): void {
            app(AuditLogger::class)->logModelChange(
                AuditLog::ACTION_CREATE,
                $model,
                null,
                $model->auditableAttributes($model->getAttributes()),
            );
        });

        static::updated(function (Model $model): void {
            $changed = $model->auditableAttributes($model->getChanges());

            if ($changed === []) {
                return;
            }

            $before = array_intersect_key($model->getOriginal(), $changed);

            app(AuditLogger::class)->logModelChange(
                AuditLog::ACTION_UPDATE,
                $model,
                $before,
                $changed,
            );
        });

        static::deleted(function (Model $model): void {
            app(AuditLogger::class)->logModelChange(
                AuditLog::ACTION_DELETE,
                $model,
                $model->auditableAttributes($model->getOriginal()),
                null,
            );
        });
    }

    /**
     * Attributs à exclure du journal (fusionnés avec la liste par défaut).
     *
     * @return array<int, string>
     */
    protected function auditExcludedAttributes(): array
    {
        return array_merge(
            ['created_at', 'updated_at', 'deleted_at', 'password', 'remember_token'],
            $this->auditExclude ?? [],
        );
    }

    /**
     * Filtre un tableau d'attributs selon la liste d'exclusion.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public function auditableAttributes(array $attributes): array
    {
        return array_diff_key($attributes, array_flip($this->auditExcludedAttributes()));
    }
}
