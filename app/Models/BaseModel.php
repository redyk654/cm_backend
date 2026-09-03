<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle de base des ressources métier :
 * - clé primaire UUID ordonnée ;
 * - suppression logique (soft delete) ;
 * - traçabilité created_by / updated_by renseignée automatiquement.
 *
 * Pas de scope d'isolation : l'application est mono-établissement.
 *
 * Contrat : toute table pilotée par un modèle héritant de BaseModel doit porter
 * les colonnes `created_by` et `updated_by` (uuid, nullable) et `deleted_at`.
 * Un modèle sans auteur (table de rattachement, référentiel purement technique)
 * peut désactiver la traçabilité via `protected bool $recordsBlame = false;`.
 */
abstract class BaseModel extends Model
{
    use HasUuidPrimaryKey;
    use SoftDeletes;

    /**
     * Renseigner automatiquement created_by / updated_by.
     */
    protected bool $recordsBlame = true;

    protected static function booted(): void
    {
        static::creating(function (Model $model): void {
            if (! $model->recordsBlame || ! auth()->check()) {
                return;
            }

            $model->created_by ??= auth()->id();
            $model->updated_by ??= auth()->id();
        });

        static::updating(function (Model $model): void {
            if ($model->recordsBlame && auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });
    }
}
